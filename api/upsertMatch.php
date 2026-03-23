<?php

declare(strict_types=1);

include_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'syncstatsconfig.php');
require '../scriptsphp/defenvvar.php';
require_once __DIR__ . '/lib/upsert_match_rules.php';

header('Content-Type: application/json; charset=utf-8');

function lockNameFromMatchRef(string $matchIdRef): string
{
    return 'upsert_match_' . substr(sha1($matchIdRef), 0, 32);
}

function acquireMatchLock(mysqli $conn, string $matchIdRef, int $timeoutSeconds = 5): bool
{
    $lockName = lockNameFromMatchRef($matchIdRef);
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

function releaseMatchLock(mysqli $conn, string $matchIdRef): void
{
    $lockName = lockNameFromMatchRef($matchIdRef);
    $stmt = mysqli_prepare($conn, 'SELECT RELEASE_LOCK(?)');
    if (!$stmt) {
        return;
    }

    mysqli_stmt_bind_param($stmt, 's', $lockName);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function trouverMatchIdParRef(mysqli $conn, string $matchIdRef): int
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

function upsertMatchByRef(mysqli $conn, array $m): array
{
    $matchIdRef = $m['matchLongId'];
    if ($matchIdRef === '') {
        return array('ok' => false, 'error' => 'matchLongId manquant', 'matchId' => 0, 'action' => 'rejected');
    }

    $lockAcquired = acquireMatchLock($conn, $matchIdRef, 5);
    if (!$lockAcquired) {
        return array('ok' => false, 'error' => 'verrou timeout', 'matchId' => 0, 'action' => 'lock_timeout');
    }

    try {
        $existingId = trouverMatchIdParRef($conn, $matchIdRef);

        if ($existingId > 0) {
            $stmtUpdate = mysqli_prepare(
                $conn,
                'UPDATE TableMatch
                 SET eq_dom = ?, score_dom = ?, eq_vis = ?, score_vis = ?, statut = ?, ligueRef = ?, date = ?, cleValeur = ?, arenaId = ?, TSDMAJ = ?
                 WHERE match_id = ?'
            );

            if (!$stmtUpdate) {
                return array('ok' => false, 'error' => 'update_prepare_failed', 'matchId' => 0, 'action' => 'error');
            }

            $arenaIdForBind = $m['arenaId'];
            mysqli_stmt_bind_param(
                $stmtUpdate,
                'iiiiiissisi',
                $m['eqDom'],
                $m['scoreDom'],
                $m['eqVis'],
                $m['scoreVis'],
                $m['etat'],
                $m['ligueId'],
                $m['dateSql'],
                $m['cleValeur'],
                $arenaIdForBind,
                $m['TSDMAJ'],
                $existingId
            );

            $ok = mysqli_stmt_execute($stmtUpdate);
            $err = mysqli_stmt_error($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);

            if (!$ok) {
                return array('ok' => false, 'error' => 'update_failed:' . $err, 'matchId' => 0, 'action' => 'error');
            }

            return array('ok' => true, 'error' => null, 'matchId' => $existingId, 'action' => 'update');
        }

        $stmtInsert = mysqli_prepare(
            $conn,
            'INSERT INTO TableMatch (eq_dom, score_dom, eq_vis, score_vis, statut, matchIdRef, ligueRef, date, cleValeur, arenaId, TSDMAJ)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        if (!$stmtInsert) {
            return array('ok' => false, 'error' => 'insert_prepare_failed', 'matchId' => 0, 'action' => 'error');
        }

        $arenaIdForBind = $m['arenaId'];
        mysqli_stmt_bind_param(
            $stmtInsert,
            'iiiiisissis',
            $m['eqDom'],
            $m['scoreDom'],
            $m['eqVis'],
            $m['scoreVis'],
            $m['etat'],
            $m['matchLongId'],
            $m['ligueId'],
            $m['dateSql'],
            $m['cleValeur'],
            $arenaIdForBind,
            $m['TSDMAJ']
        );

        $ok = mysqli_stmt_execute($stmtInsert);
        $err = mysqli_stmt_error($stmtInsert);
        $newId = (int)mysqli_insert_id($conn);
        mysqli_stmt_close($stmtInsert);

        if (!$ok || $newId <= 0) {
            return array('ok' => false, 'error' => 'insert_failed:' . $err, 'matchId' => 0, 'action' => 'error');
        }

        return array('ok' => true, 'error' => null, 'matchId' => $newId, 'action' => 'insert');
    } finally {
        releaseMatchLock($conn, $matchIdRef);
    }
}

$matchArray = null;
if (isset($_POST['match'])) {
    $matchArray = json_decode((string)$_POST['match'], true);
}

$syncOK = array();

if (is_array($matchArray)) {
    foreach ($matchArray as $matchRaw) {
        if (!is_array($matchRaw)) {
            continue;
        }

        $m = upsertMatchNormaliser($matchRaw);
        $result = upsertMatchByRef($conn, $m);

        error_log(
            '[upsertMatch] matchLongId=' . $m['matchLongId']
            . ' inputGameComId=' . $m['GameComId']
            . ' action=' . $result['action']
            . ' returnedGameComId=' . $result['matchId']
            . ' ok=' . ($result['ok'] ? '1' : '0'),
            0
        );

        if ($result['ok']) {
            $syncOK[] = array(
                'GameLocId' => $m['GameLocId'],
                'GameComId' => $result['matchId']
            );
        } else {
            $syncOK[] = array(
                'GameLocId' => $m['GameLocId'],
                'GameComId' => null,
                'error' => $result['error']
            );
        }
    }
}

echo json_encode($syncOK, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

?>
