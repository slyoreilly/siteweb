<?php
require 'defenvvar.php';
header('Content-Type: application/json; charset=utf-8');

function logAjoutVideo($message, $context = array()) {
    if (!is_array($context)) {
        $context = array('context' => $context);
    }

    error_log('[ajouteVideos.php] ' . $message . ' | ' . json_encode($context));
}

$butId = isset($_POST['butId']) ? intval($_POST['butId']) : 0;
$chronoDemande = isset($_POST['chrono']) ? intval($_POST['chrono']) : 0;
$cameraId = isset($_POST['cameraId']) ? intval($_POST['cameraId']) : 0;
$eventTypeCode = isset($_POST['eventTypeCode']) ? intval($_POST['eventTypeCode']) : 0;
$matchIdRef = isset($_POST['matchIdRef']) ? trim($_POST['matchIdRef']) : '';
$matchIdRefSql = mysqli_real_escape_string($conn, $matchIdRef);
$equipeId = isset($_POST['equipeId']) ? intval($_POST['equipeId']) : 0;
$noSequence = isset($_POST['noSequence']) ? intval($_POST['noSequence']) : -1;

logAjoutVideo('POST recu', array(
    'butId' => $butId,
    'chronoDemande' => $chronoDemande,
    'cameraId' => $cameraId,
    'eventTypeCode' => $eventTypeCode,
    'matchIdRef' => $matchIdRef,
    'equipeId' => $equipeId,
    'noSequence' => $noSequence
));

$eventTypeCode = 0;

if ($chronoDemande <= 0 || $cameraId <= 0) {
    $message = 'Parametres invalides.';
    logAjoutVideo($message, array('chronoDemande' => $chronoDemande, 'cameraId' => $cameraId));
    http_response_code(400);
    echo json_encode(array('ok' => false, 'message' => $message));
    exit;
}

if ($butId <= 0 && $matchIdRef !== '') {
    $conditions = array();
    $conditions[] = "match_event_id='{$matchIdRefSql}'";
    $conditions[] = "code=0";
    $conditions[] = "ABS(chrono-'{$chronoDemande}')<=120000";

    if ($equipeId > 0) {
        $conditions[] = "equipe_event_id='{$equipeId}'";
    }
    if ($noSequence >= 0) {
        $conditions[] = "noSequence='{$noSequence}'";
    }

    $qResolve = "SELECT event_id FROM TableEvenement0 WHERE " . implode(' AND ', $conditions)
        . " ORDER BY ABS(chrono-'{$chronoDemande}') ASC LIMIT 0,1";
    $resResolve = mysqli_query($conn, $qResolve);
    if ($resResolve && mysqli_num_rows($resResolve) > 0) {
        $rowResolve = mysqli_fetch_array($resResolve);
        $butId = intval($rowResolve['event_id']);
        logAjoutVideo('butId resolu automatiquement', array('butId' => $butId));
    } else {
        logAjoutVideo('Aucun butId resolu', array('qResolve' => $qResolve, 'mysql_error' => mysqli_error($conn)));
    }
}

if ($butId <= 0) {
    $message = "Impossible de trouver l'identifiant du but.";
    logAjoutVideo($message, array('matchIdRef' => $matchIdRef, 'equipeId' => $equipeId, 'chronoDemande' => $chronoDemande));
    http_response_code(404);
    echo json_encode(array('ok' => false, 'message' => $message));
    exit;
}

$qIns = "INSERT INTO DemandeAjoutVideo (
            eventId, typeEvenement, chronoDemande, cameraId, progression, dateCreation
        ) VALUES (
            '{$butId}', '{$eventTypeCode}', '{$chronoDemande}', '{$cameraId}', 1, NOW()
        )";

$resInsert = mysqli_query($conn, $qIns);
if (!$resInsert) {
    $mysqlError = mysqli_error($conn);
    logAjoutVideo('Erreur insertion', array('mysql_error' => $mysqlError, 'query' => $qIns));
    http_response_code(500);
    echo json_encode(array('ok' => false, 'message' => "Erreur SQL lors de la creation de la demande video."));
    exit;
}

$insertId = mysqli_insert_id($conn);
logAjoutVideo('Insertion OK', array('demandeId' => $insertId, 'eventId' => $butId));

echo json_encode(array(
    'ok' => true,
    'demandeId' => $insertId,
    'eventId' => $butId
));
?>
