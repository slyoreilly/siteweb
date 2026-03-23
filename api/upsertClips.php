<?php

declare(strict_types=1);

include_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'syncstatsconfig.php');
require '../scriptsphp/defenvvar.php';

header('Content-Type: application/json; charset=utf-8');

function lockNameFromMatchRefClip(string $matchIdRef): string
{
    return 'upsert_clip_match_' . substr(sha1($matchIdRef), 0, 32);
}

function acquireClipMatchLock(mysqli $conn, string $matchIdRef, int $timeoutSeconds = 5): bool
{
    $lockName = lockNameFromMatchRefClip($matchIdRef);
    $stmt = mysqli_prepare($conn, 'SELECT GET_LOCK(?, ?)');
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'si', $lockName, $timeoutSeconds);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $lockResult);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return ((int)$lockResult) === 1;
}

function releaseClipMatchLock(mysqli $conn, string $matchIdRef): void
{
    $lockName = lockNameFromMatchRefClip($matchIdRef);
    $stmt = mysqli_prepare($conn, 'SELECT RELEASE_LOCK(?)');
    if (!$stmt) {
        return;
    }

    mysqli_stmt_bind_param($stmt, 's', $lockName);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function matchIdByRef(mysqli $conn, string $matchIdRef): int
{
    $stmt = mysqli_prepare($conn, 'SELECT match_id FROM TableMatch WHERE matchIdRef = ? ORDER BY match_id DESC LIMIT 1');
    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param($stmt, 's', $matchIdRef);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $matchId);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return (int)($matchId ?? 0);
}

function creerMatchSiInexistant(mysqli $conn, string $matchClientId, ?int $plateauId, ?int $ligueId): ?int
{
    if ($matchClientId === '' || $ligueId === null) {
        return null;
    }

    if (!acquireClipMatchLock($conn, $matchClientId, 5)) {
        return null;
    }

    try {
        $existing = matchIdByRef($conn, $matchClientId);
        if ($existing > 0) {
            return $existing;
        }

        $qEquipes = 'SELECT equipeId FROM TableEquipe WHERE ligueId = ? LIMIT 2';
        $stmtEq = mysqli_prepare($conn, $qEquipes);
        if (!$stmtEq) {
            return null;
        }

        mysqli_stmt_bind_param($stmtEq, 'i', $ligueId);
        mysqli_stmt_execute($stmtEq);
        $resEq = mysqli_stmt_get_result($stmtEq);

        $equipes = array();
        while ($resEq && ($row = mysqli_fetch_assoc($resEq))) {
            $equipes[] = (int)$row['equipeId'];
        }
        mysqli_stmt_close($stmtEq);

        if (count($equipes) < 2) {
            error_log('[upsertClips] Pas assez d\'equipes dans la ligue ' . $ligueId, 0);
            return null;
        }

        $eqDom = $equipes[0];
        $eqVis = $equipes[1];
        $now = date('Y-m-d H:i:s');
        $tsdm = (string)round(microtime(true) * 1000);
        $arenaId = $plateauId;

        $stmtInsert = mysqli_prepare(
            $conn,
            'INSERT INTO TableMatch (eq_dom, score_dom, eq_vis, score_vis, statut, matchIdRef, ligueRef, date, TSDMAJ, arenaId)
             VALUES (?, 0, ?, 0, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmtInsert) {
            return null;
        }

        $statut = 'F';
        mysqli_stmt_bind_param($stmtInsert, 'iisisssi', $eqDom, $eqVis, $statut, $matchClientId, $ligueId, $now, $tsdm, $arenaId);
        $ok = mysqli_stmt_execute($stmtInsert);
        $newId = (int)mysqli_insert_id($conn);
        $err = mysqli_stmt_error($stmtInsert);
        mysqli_stmt_close($stmtInsert);

        if (!$ok || $newId <= 0) {
            error_log('[upsertClips] insert match failed | matchIdRef=' . $matchClientId . ' err=' . $err, 0);
            return null;
        }

        return $newId;
    } finally {
        releaseClipMatchLock($conn, $matchClientId);
    }
}

$clips = array();
if (isset($_POST['clips'])) {
    $decoded = json_decode((string)$_POST['clips'], true);
    if (is_array($decoded)) {
        $clips = $decoded;
    }
}

$heure = isset($_POST['heure']) && is_numeric($_POST['heure']) ? (int)$_POST['heure'] : null;
$heureServeur = (int)(time() * 1000);

$syncOK = array();

foreach ($clips as $unClip) {
    if (!is_array($unClip)) {
        continue;
    }

    $gameStringId = isset($unClip['GameStringID']) ? (string)$unClip['GameStringID'] : '';
    if ($gameStringId === '') {
        $syncOK[] = array('id' => (int)($unClip['id'] ?? 0), 'SyncKey' => null, 'error' => 'GameStringID manquant');
        continue;
    }

    $chrono = isset($unClip['chrono']) && is_numeric($unClip['chrono']) ? (int)$unClip['chrono'] : 0;
    if ($heure !== null) {
        $chrono = $chrono + $heureServeur - $heure;
    }

    $stmtClip = mysqli_prepare($conn, 'INSERT INTO Clips (matchId, chrono, scoringEnd, type) VALUES (?, ?, NULL, 5)');
    if (!$stmtClip) {
        $syncOK[] = array('id' => (int)($unClip['id'] ?? 0), 'SyncKey' => null, 'error' => 'insert clip prepare failed');
        continue;
    }

    mysqli_stmt_bind_param($stmtClip, 'si', $gameStringId, $chrono);
    $okClip = mysqli_stmt_execute($stmtClip);
    $errClip = mysqli_stmt_error($stmtClip);
    $webIdClip = (int)mysqli_insert_id($conn);
    mysqli_stmt_close($stmtClip);

    if (!$okClip || $webIdClip <= 0) {
        $syncOK[] = array('id' => (int)($unClip['id'] ?? 0), 'SyncKey' => null, 'error' => 'insert clip failed: ' . $errClip);
        continue;
    }

    $syncOK[] = array('id' => (int)($unClip['id'] ?? 0), 'SyncKey' => $webIdClip);

    $plateauId = isset($unClip['plateauId']) && is_numeric($unClip['plateauId']) ? (int)$unClip['plateauId'] : null;
    $ligueId = isset($unClip['ligueId']) && is_numeric($unClip['ligueId']) ? (int)$unClip['ligueId'] : null;

    if ($plateauId !== null && $ligueId !== null) {
        $createdMatchId = creerMatchSiInexistant($conn, $gameStringId, $plateauId, $ligueId);
        if ($createdMatchId !== null) {
            error_log('[upsertClips] match check/create done | matchIdRef=' . $gameStringId . ' match_id=' . $createdMatchId, 0);
        }
    }
}

echo json_encode($syncOK, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

?>
