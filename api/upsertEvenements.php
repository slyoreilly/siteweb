<?php

include_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "syncstatsconfig.php");
require("../scriptsphp/calculeMatch2.php");
require '../scriptsphp/defenvvar.php';

$heure = $_POST['heure'] ?? null;
$heureServeur = time() * 1000;

$preEvenements = null;
$evenements = null;
if (isset($_POST['evenements'])) {
    $preEvenements = $_POST["evenements"];
    $evenements = json_decode($preEvenements, true);
}

$syncOK = array();

$qRef = "SELECT event_id FROM TableEvenement0 WHERE 1 ORDER BY event_id DESC LIMIT 0,1";
$rRef = mysqli_query($conn, $qRef) or die(mysqli_error($conn) . $qRef);
$vRef = mysqli_fetch_row($rRef);

$extra['DM'] = 3;
$memNoMatchId = 0;
$noMatchId = 0;

if ($evenements != null) {

    foreach ($evenements as $evenement) {

        $evenement['TeamID'] = $evenement['TeamID'] ?? null;
        $evenement['PlayerComID'] = $evenement['PlayerComID'] ?? null;
        $evenement['etatSync'] = $evenement['etatSync'] ?? null;

        if (isset($heure) && isset($evenement['chrono'])) {
            $evenement['chrono'] = (int)$evenement['chrono'] + $heureServeur - (int)$heure;
        }

        if ((int)$evenement['etatSync'] == 10) {

            $eventComIdDel = mysqli_real_escape_string($conn, (string)($evenement['EventComId'] ?? ''));
            $qDel = "DELETE FROM TableEvenement0 WHERE event_id='{$eventComIdDel}'";
            mysqli_query($conn, $qDel) or die(mysqli_error($conn) . $qDel);

            $retObj = array(
                "id" => (int)($evenement['id'] ?? 0),
                "EventComId" => mysqli_insert_id($conn),
                "etatSync" => 10
            );
            array_push($syncOK, $retObj);

        } else {
            $gameStringID = (string)($evenement['GameStringID'] ?? '');
            $teamID = (int)($evenement['TeamID'] ?? 0);
            $playerID = (int)($evenement['PlayerComID'] ?? 0);
            $chrono = (int)($evenement['chrono'] ?? 0);
            $eventTypeID = (int)($evenement['EventTypeID'] ?? 0);
            $eventTypeDetailID = (int)($evenement['EventTypeDetailID'] ?? $evenement['eventTypeDetailId'] ?? $evenement['eventTypeDetailID'] ?? 0);
            $id = (int)($evenement['id'] ?? 0);

            $code = null;
            $subcode = null;

            // 1) Priorité au EventTypeDetailID
            if ($eventTypeDetailID > 0) {
                $stmt = mysqli_prepare($conn, "SELECT Code, Subcode FROM EventTypeDetail WHERE EventTypeDetailId = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt, "i", $eventTypeDetailID);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $detailCode, $detailSubcode);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                if ($detailCode !== null) {
                    $code = (int)$detailCode;
                    $subcode = ($detailSubcode === null) ? 0 : (int)$detailSubcode;
                }
            }

            // 2) Fallback sur les champs entrants si EventTypeDetail introuvable
            if ($code === null) {
                if (isset($evenement['code']) && $evenement['code'] !== '' && $evenement['code'] !== null) {
                    $code = (int)$evenement['code'];
                }
            }

            if ($subcode === null) {
                foreach (['souscode', 'subcode', 'subCode', 'Subcode', 'sc'] as $k) {
                    if (isset($evenement[$k]) && $evenement[$k] !== '' && $evenement[$k] !== null) {
                        $subcode = (int)$evenement[$k];
                        break;
                    }
                }
            }

            // 3) Fallback final sur EventType
            if ($code === null || $subcode === null) {
                $stmt = mysqli_prepare($conn, "SELECT Code, Subcode FROM EventType WHERE EventTypeId = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt, "i", $eventTypeID);
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

            $eventComIdValue = $evenement['EventComId'] ?? null;

            if ($eventComIdValue === null || $eventComIdValue === '') {

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO TableEvenement0 
                    (match_event_id, equipe_event_id, joueur_event_ref, chrono, code, souscode, noSequence) 
                    VALUES (?, ?, ?, ?, ?, ?, 0)"
                );

                mysqli_stmt_bind_param($stmt, "siiiii", $gameStringID, $teamID, $playerID, $chrono, $code, $subcode);
                $success = mysqli_stmt_execute($stmt);

                if (!$success) {
                    die("Erreur d'insertion : " . mysqli_error($conn));
                }

                $eventComId = mysqli_insert_id($conn);

                if ($eventComId <= 0) {
                    die("Erreur : Aucun ID inséré.");
                }

                $stmtMatch = mysqli_prepare($conn, "SELECT match_id FROM TableMatch WHERE matchIdRef = ? LIMIT 1");
                mysqli_stmt_bind_param($stmtMatch, "s", $gameStringID);
                mysqli_stmt_execute($stmtMatch);
                mysqli_stmt_bind_result($stmtMatch, $matchId);
                mysqli_stmt_fetch($stmtMatch);
                mysqli_stmt_close($stmtMatch);

                if (!empty($matchId)) {
                    $noMatchId = $matchId;
                }

                $retObj = array("id" => $id, "EventComId" => $eventComId, "etatSync" => 12);
                array_push($syncOK, $retObj);

                mysqli_stmt_close($stmt);

            } else {
                $eventComId = (int)$eventComIdValue;

                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE TableEvenement0 
                     SET match_event_id = ?, 
                         equipe_event_id = ?, 
                         joueur_event_ref = ?, 
                         chrono = ?, 
                         code = ?, 
                         souscode = ?, 
                         noSequence = 0 
                     WHERE event_id = ?"
                );

                mysqli_stmt_bind_param($stmt, "siiiiii", $gameStringID, $teamID, $playerID, $chrono, $code, $subcode, $eventComId);
                $success = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                if (!$success) {
                    die(
                        "Erreur: Mise à jour échouée pour event_id={$eventComId} " .
                        "Tentative UPDATE avec paramètres : gameStringID=$gameStringID, teamID=$teamID, playerID=$playerID, chrono=$chrono, code=$code, subcode=$subcode, eventComId=$eventComId"
                    );
                }

                $stmtMatch = mysqli_prepare($conn, "SELECT match_id FROM TableMatch WHERE matchIdRef = ? LIMIT 1");
                mysqli_stmt_bind_param($stmtMatch, "s", $gameStringID);
                mysqli_stmt_execute($stmtMatch);
                mysqli_stmt_bind_result($stmtMatch, $matchId);
                mysqli_stmt_fetch($stmtMatch);
                mysqli_stmt_close($stmtMatch);

                if (!empty($matchId)) {
                    $noMatchId = $matchId;
                }

                $retObj = array("id" => $id, "EventComId" => $eventComId, "etatSync" => 12);
                array_push($syncOK, $retObj);
            }
        }
    }
}

if ($noMatchId != 0) {

    if ($workEnv == "production") {
        $url = 'https://syncstats.com/scriptsphp/calculeUnMatch.php';
    } else {
        $url = 'http://vieuxsite.sm.syncstats.ca/scriptsphp/calculeUnMatch.php';
    }

    $postData = http_build_query([
        'noMatchId' => (int)$noMatchId
    ]);

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($postData)
        ],
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);

    curl_close($ch);

    if ($result === false || $httpCode !== 200) {
        error_log(
            "calculeUnMatch CURL ERROR | match={$noMatchId} | http={$httpCode} | err={$curlErr}",
            0
        );
    } elseif (trim($result) === '') {
        error_log(
            "calculeUnMatch réponse vide | match={$noMatchId}",
            0
        );
    }

    $memNoMatchId = $noMatchId;
}

$deSyncMatch = 1;
echo json_encode($syncOK);
//mysqli_close($conn);

?>
