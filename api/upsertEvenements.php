<?php

declare(strict_types=1);

include_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'syncstatsconfig.php');
require '../scriptsphp/calculeMatch2.php';
require '../scriptsphp/defenvvar.php';
require_once __DIR__ . '/lib/upsert_evenements_rules.php';

header('Content-Type: application/json; charset=utf-8');

function upsertEvenementsTrouverMatchId(mysqli $conn, string $gameStringID): int
{
    $stmtMatch = mysqli_prepare($conn, 'SELECT match_id FROM TableMatch WHERE matchIdRef = ? LIMIT 1');
    if (!$stmtMatch) {
        return 0;
    }

    mysqli_stmt_bind_param($stmtMatch, 's', $gameStringID);
    mysqli_stmt_execute($stmtMatch);
    mysqli_stmt_bind_result($stmtMatch, $matchId);
    mysqli_stmt_fetch($stmtMatch);
    mysqli_stmt_close($stmtMatch);

    return (int)($matchId ?? 0);
}

$heure = $_POST['heure'] ?? null;
$heureServeur = time() * 1000;

$evenements = null;
if (isset($_POST['evenements'])) {
    $evenements = json_decode((string)$_POST['evenements'], true);
}

$syncOK = array();
$noMatchId = 0;

if (is_array($evenements)) {
    foreach ($evenements as $evenement) {
        if (!is_array($evenement)) {
            continue;
        }

        $id = (int)($evenement['id'] ?? 0);
        $etatSync = (int)($evenement['etatSync'] ?? 0);

        if (isset($heure) && isset($evenement['chrono']) && is_numeric($evenement['chrono'])) {
            $evenement['chrono'] = (int)$evenement['chrono'] + $heureServeur - (int)$heure;
        }

        $validationChrono = upsertEvenementsValidationChrono($evenement);
        if ($validationChrono['ok'] !== true) {
            $syncOK[] = array(
                'id' => $id,
                'EventComId' => null,
                'etatSync' => $etatSync,
                'ok' => false,
                'error' => 'validation_echec',
                'reason' => $validationChrono['raison']
            );
            continue;
        }

        if ($etatSync === 10) {
            $eventComIdDel = (int)($evenement['EventComId'] ?? 0);
            if ($eventComIdDel <= 0) {
                $syncOK[] = array(
                    'id' => $id,
                    'EventComId' => null,
                    'etatSync' => 10,
                    'ok' => false,
                    'error' => 'delete_event_com_id_invalide'
                );
                continue;
            }

            $stmtDel = mysqli_prepare($conn, 'DELETE FROM TableEvenement0 WHERE event_id = ?');
            if (!$stmtDel) {
                $syncOK[] = array(
                    'id' => $id,
                    'EventComId' => $eventComIdDel,
                    'etatSync' => 10,
                    'ok' => false,
                    'error' => 'delete_prepare_failed'
                );
                continue;
            }

            mysqli_stmt_bind_param($stmtDel, 'i', $eventComIdDel);
            $deleteOK = mysqli_stmt_execute($stmtDel);
            $deleteAffected = mysqli_stmt_affected_rows($stmtDel);
            mysqli_stmt_close($stmtDel);

            $syncOK[] = array(
                'id' => $id,
                'EventComId' => $eventComIdDel,
                'etatSync' => 10,
                'ok' => $deleteOK ? true : false,
                'deleted' => $deleteAffected > 0
            );
            continue;
        }

        $gameStringID = (string)($evenement['GameStringID'] ?? '');
        $teamID = (int)($evenement['TeamID'] ?? 0);
        $playerID = (int)($evenement['PlayerComID'] ?? 0);
        $chrono = (int)($evenement['chrono'] ?? 0);
        $eventTypeID = (int)($evenement['EventTypeID'] ?? 0);
        $eventTypeDetailID = (int)($evenement['EventTypeDetailID'] ?? $evenement['eventTypeDetailId'] ?? $evenement['eventTypeDetailID'] ?? 0);
        $noSequence = (int)($evenement['noSequence'] ?? 0);

        $code = null;
        $subcode = null;

        if ($eventTypeDetailID > 0) {
            $stmt = mysqli_prepare($conn, 'SELECT Code, Subcode FROM EventTypeDetail WHERE EventTypeDetailId = ? LIMIT 1');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $eventTypeDetailID);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $detailCode, $detailSubcode);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                if ($detailCode !== null) {
                    $code = (int)$detailCode;
                    $subcode = ($detailSubcode === null) ? 0 : (int)$detailSubcode;
                }
            }
        }

        if ($code === null && isset($evenement['code']) && $evenement['code'] !== '') {
            $code = (int)$evenement['code'];
        }

        if ($subcode === null) {
            foreach (array('souscode', 'subcode', 'subCode', 'Subcode', 'sc') as $k) {
                if (isset($evenement[$k]) && $evenement[$k] !== '' && $evenement[$k] !== null) {
                    $subcode = (int)$evenement[$k];
                    break;
                }
            }
        }

        if ($code === null || $subcode === null) {
            $stmt = mysqli_prepare($conn, 'SELECT Code, Subcode FROM EventType WHERE EventTypeId = ? LIMIT 1');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $eventTypeID);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $typeCode, $typeSubcode);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                if ($code === null) {
                    $code = ($typeCode === null) ? 0 : (int)$typeCode;
                }
                if ($subcode === null) {
                    $subcode = ($typeSubcode === null) ? 0 : (int)$typeSubcode;
                }
            }
        }

        $eventComIdValue = $evenement['EventComId'] ?? null;
        $decisionCreationLocale = upsertEvenementsDecisionCreationLocale($evenement);

        if ($decisionCreationLocale['eventComIdVide'] === true) {
            $stmtExiste = mysqli_prepare(
                $conn,
                'SELECT event_id
                 FROM TableEvenement0
                 WHERE match_event_id = ?
                   AND equipe_event_id = ?
                   AND joueur_event_ref = ?
                   AND chrono = ?
                   AND code = ?
                   AND souscode = ?
                   AND noSequence = ?
                 ORDER BY event_id DESC
                 LIMIT 1'
            );

            if ($stmtExiste) {
                mysqli_stmt_bind_param($stmtExiste, 'siiiiii', $gameStringID, $teamID, $playerID, $chrono, $code, $subcode, $noSequence);
                mysqli_stmt_execute($stmtExiste);
                mysqli_stmt_bind_result($stmtExiste, $eventComIdExistant);
                $aTrouve = mysqli_stmt_fetch($stmtExiste);
                mysqli_stmt_close($stmtExiste);

                if ($aTrouve && !empty($eventComIdExistant)) {
                    $eventComId = (int)$eventComIdExistant;
                    $syncOK[] = array('id' => $id, 'EventComId' => $eventComId, 'etatSync' => 12, 'ok' => true, 'action' => 'reused');
                    continue;
                }
            }

            $stmtInsert = mysqli_prepare(
                $conn,
                'INSERT INTO TableEvenement0
                 (match_event_id, equipe_event_id, joueur_event_ref, chrono, code, souscode, noSequence)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            if (!$stmtInsert) {
                $syncOK[] = array('id' => $id, 'EventComId' => null, 'etatSync' => 3, 'ok' => false, 'error' => 'insert_prepare_failed');
                continue;
            }

            mysqli_stmt_bind_param($stmtInsert, 'siiiiii', $gameStringID, $teamID, $playerID, $chrono, $code, $subcode, $noSequence);
            $insertOK = mysqli_stmt_execute($stmtInsert);

            if (!$insertOK) {
                $insertErr = mysqli_stmt_error($stmtInsert);
                mysqli_stmt_close($stmtInsert);
                $syncOK[] = array('id' => $id, 'EventComId' => null, 'etatSync' => 3, 'ok' => false, 'error' => 'insert_failed', 'detail' => $insertErr);
                continue;
            }

            $eventComId = (int)mysqli_insert_id($conn);
            mysqli_stmt_close($stmtInsert);

            if ($eventComId <= 0) {
                $syncOK[] = array('id' => $id, 'EventComId' => null, 'etatSync' => 3, 'ok' => false, 'error' => 'insert_no_event_com_id');
                continue;
            }

            $matchId = upsertEvenementsTrouverMatchId($conn, $gameStringID);
            if ($matchId > 0) {
                $noMatchId = $matchId;
            }

            $syncOK[] = array('id' => $id, 'EventComId' => $eventComId, 'etatSync' => 12, 'ok' => true, 'action' => 'created');
            continue;
        }

        $eventComId = (int)$eventComIdValue;
        if ($eventComId <= 0) {
            $syncOK[] = array('id' => $id, 'EventComId' => null, 'etatSync' => 3, 'ok' => false, 'error' => 'event_com_id_invalide');
            continue;
        }

        $stmtUpsert = mysqli_prepare(
            $conn,
            'INSERT INTO TableEvenement0
            (event_id, match_event_id, equipe_event_id, joueur_event_ref, chrono, code, souscode, noSequence)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                match_event_id = VALUES(match_event_id),
                equipe_event_id = VALUES(equipe_event_id),
                joueur_event_ref = VALUES(joueur_event_ref),
                chrono = VALUES(chrono),
                code = VALUES(code),
                souscode = VALUES(souscode),
                noSequence = VALUES(noSequence)'
        );

        if (!$stmtUpsert) {
            $syncOK[] = array('id' => $id, 'EventComId' => $eventComId, 'etatSync' => 3, 'ok' => false, 'error' => 'upsert_prepare_failed');
            continue;
        }

        mysqli_stmt_bind_param($stmtUpsert, 'isiiiiii', $eventComId, $gameStringID, $teamID, $playerID, $chrono, $code, $subcode, $noSequence);
        $upsertOK = mysqli_stmt_execute($stmtUpsert);
        if (!$upsertOK) {
            $upsertErr = mysqli_stmt_error($stmtUpsert);
            mysqli_stmt_close($stmtUpsert);
            $syncOK[] = array('id' => $id, 'EventComId' => $eventComId, 'etatSync' => 3, 'ok' => false, 'error' => 'upsert_failed', 'detail' => $upsertErr);
            continue;
        }
        mysqli_stmt_close($stmtUpsert);

        $matchId = upsertEvenementsTrouverMatchId($conn, $gameStringID);
        if ($matchId > 0) {
            $noMatchId = $matchId;
        }

        $syncOK[] = array('id' => $id, 'EventComId' => $eventComId, 'etatSync' => 12, 'ok' => true, 'action' => 'upserted');
    }
}

if ($noMatchId !== 0) {
    if ($workEnv === 'production') {
        $url = 'https://syncstats.com/scriptsphp/calculeUnMatch.php';
    } else {
        $url = 'http://vieuxsite.sm.syncstats.ca/scriptsphp/calculeUnMatch.php';
    }

    $postData = http_build_query(array('noMatchId' => (int)$noMatchId));
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($postData)
        ),
    ));

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($result === false || $httpCode !== 200) {
        error_log('calculeUnMatch CURL ERROR | match=' . $noMatchId . ' | http=' . $httpCode . ' | err=' . $curlErr, 0);
    } elseif (trim((string)$result) === '') {
        error_log('calculeUnMatch reponse vide | match=' . $noMatchId, 0);
    }
}

echo json_encode($syncOK);

?>
