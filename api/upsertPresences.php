<?php

declare(strict_types=1);

include_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'syncstatsconfig.php');
require '../scriptsphp/defenvvar.php';

header('Content-Type: application/json; charset=utf-8');

function respond(array $payload): void
{
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function toNullableInt($value): ?int
{
    if ($value === null || $value === '' || strtolower((string)$value) === 'null') {
        return null;
    }

    return is_numeric($value) ? (int)$value : null;
}

function toSqlDateTime($updatedAtMs, ?int $clientTimeMs, int $serverTimeMs): string
{
    $ts = is_numeric($updatedAtMs) ? (int)round((float)$updatedAtMs) : 0;
    if ($ts <= 0) {
        return date('Y-m-d H:i:s');
    }

    if ($clientTimeMs !== null && $clientTimeMs > 0) {
        $ts += ($serverTimeMs - $clientTimeMs);
    }

    return date('Y-m-d H:i:s', (int)floor($ts / 1000));
}

if (!($conn instanceof mysqli)) {
    respond([
        [
            'ok' => false,
            'error' => 'DB connection unavailable'
        ]
    ]);
}

$rawPresences = $_POST['presences'] ?? null;
$presences = is_string($rawPresences) ? json_decode($rawPresences, true) : null;

if (!is_array($presences)) {
    respond([
        [
            'ok' => false,
            'error' => 'invalid presences payload'
        ]
    ]);
}

$heure = isset($_POST['heure']) && is_numeric($_POST['heure']) ? (int)$_POST['heure'] : null;
$heureServeur = (int)round(microtime(true) * 1000);

$hasNumeroColumn = false;
$retNumero = mysqli_query($conn, "SHOW COLUMNS FROM Presences LIKE 'numero'");
if ($retNumero && mysqli_num_rows($retNumero) > 0) {
    $hasNumeroColumn = true;
}

$stmtMatch = mysqli_prepare($conn, 'SELECT match_id FROM TableMatch WHERE match_id = ? LIMIT 1');
$stmtExisting = mysqli_prepare($conn, 'SELECT presenceId FROM Presences WHERE matchId = ? AND joueurId = ? LIMIT 1');

if ($hasNumeroColumn) {
    $stmtInsert = mysqli_prepare(
        $conn,
        'INSERT INTO Presences (matchId, joueurId, positionId, numero, domVis, statut, updatedBy, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmtUpdate = mysqli_prepare(
        $conn,
        'UPDATE Presences SET positionId = ?, numero = ?, domVis = ?, statut = ?, updatedBy = ?, updatedAt = ? WHERE matchId = ? AND joueurId = ?'
    );
} else {
    $stmtInsert = mysqli_prepare(
        $conn,
        'INSERT INTO Presences (matchId, joueurId, positionId, domVis, statut, updatedBy, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmtUpdate = mysqli_prepare(
        $conn,
        'UPDATE Presences SET positionId = ?, domVis = ?, statut = ?, updatedBy = ?, updatedAt = ? WHERE matchId = ? AND joueurId = ?'
    );
}

if (!$stmtMatch || !$stmtExisting || !$stmtInsert || !$stmtUpdate) {
    respond([
        [
            'ok' => false,
            'error' => 'prepare failed: ' . mysqli_error($conn)
        ]
    ]);
}

$response = [];

foreach ($presences as $item) {
    $gameLocId = isset($item['GameLocId']) ? (int)$item['GameLocId'] : null;
    $gameComId = isset($item['GameComId']) && is_numeric($item['GameComId']) ? (int)$item['GameComId'] : null;
    $joueurId = isset($item['joueurId']) && is_numeric($item['joueurId']) ? (int)$item['joueurId'] : null;
    $positionId = toNullableInt($item['positionId'] ?? null);
    $numero = isset($item['numero']) ? (string)$item['numero'] : null;
    $domVis = isset($item['domVis']) && is_numeric($item['domVis']) ? (int)$item['domVis'] : null;
    $statut = isset($item['statut']) && is_numeric($item['statut']) ? (int)$item['statut'] : null;
    $updatedBy = isset($item['updatedBy']) && $item['updatedBy'] !== '' ? (string)$item['updatedBy'] : 'Default';
    $updatedAt = toSqlDateTime($item['updatedAt'] ?? null, $heure, $heureServeur);

    $resultItem = [
        'GameLocId' => $gameLocId,
        'GameComId' => $gameComId,
        'joueurId' => $joueurId,
        'ok' => false,
    ];

    if ($gameComId === null) {
        $resultItem['error'] = 'missing GameComId';
        $response[] = $resultItem;
        continue;
    }

    if ($joueurId === null) {
        $resultItem['error'] = 'missing joueurId';
        $response[] = $resultItem;
        continue;
    }

    if ($domVis === null) {
        $resultItem['error'] = 'missing domVis';
        $response[] = $resultItem;
        continue;
    }

    if ($statut === null) {
        $resultItem['error'] = 'missing statut';
        $response[] = $resultItem;
        continue;
    }

    mysqli_stmt_bind_param($stmtMatch, 'i', $gameComId);
    mysqli_stmt_execute($stmtMatch);
    mysqli_stmt_store_result($stmtMatch);

    if (mysqli_stmt_num_rows($stmtMatch) === 0) {
        $resultItem['error'] = 'unknown GameComId';
        $response[] = $resultItem;
        mysqli_stmt_free_result($stmtMatch);
        continue;
    }
    mysqli_stmt_free_result($stmtMatch);

    mysqli_stmt_bind_param($stmtExisting, 'ii', $gameComId, $joueurId);
    mysqli_stmt_execute($stmtExisting);
    mysqli_stmt_store_result($stmtExisting);
    $exists = mysqli_stmt_num_rows($stmtExisting) > 0;
    mysqli_stmt_free_result($stmtExisting);

    if ($exists) {
        if ($hasNumeroColumn) {
            mysqli_stmt_bind_param($stmtUpdate, 'isiissii', $positionId, $numero, $domVis, $statut, $updatedBy, $updatedAt, $gameComId, $joueurId);
        } else {
            mysqli_stmt_bind_param($stmtUpdate, 'iiissii', $positionId, $domVis, $statut, $updatedBy, $updatedAt, $gameComId, $joueurId);
        }

        if (!mysqli_stmt_execute($stmtUpdate)) {
            $resultItem['error'] = mysqli_stmt_error($stmtUpdate);
            $response[] = $resultItem;
            continue;
        }
    } else {
        if ($hasNumeroColumn) {
            mysqli_stmt_bind_param($stmtInsert, 'iiisiiss', $gameComId, $joueurId, $positionId, $numero, $domVis, $statut, $updatedBy, $updatedAt);
        } else {
            mysqli_stmt_bind_param($stmtInsert, 'iiiiiss', $gameComId, $joueurId, $positionId, $domVis, $statut, $updatedBy, $updatedAt);
        }

        if (!mysqli_stmt_execute($stmtInsert)) {
            $resultItem['error'] = mysqli_stmt_error($stmtInsert);
            $response[] = $resultItem;
            continue;
        }
    }

    $resultItem['ok'] = true;
    $response[] = $resultItem;
}

mysqli_stmt_close($stmtMatch);
mysqli_stmt_close($stmtExisting);
mysqli_stmt_close($stmtInsert);
mysqli_stmt_close($stmtUpdate);

respond($response);

?>
