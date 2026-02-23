<?php
require 'defenvvar.php';

$butId = isset($_POST['butId']) ? intval($_POST['butId']) : 0;
$chronoDemande = isset($_POST['chrono']) ? intval($_POST['chrono']) : 0;
$cameraId = isset($_POST['cameraId']) ? intval($_POST['cameraId']) : 0;
$eventTypeCode = isset($_POST['eventTypeCode']) ? intval($_POST['eventTypeCode']) : 0;
$matchIdRef = isset($_POST['matchIdRef']) ? intval($_POST['matchIdRef']) : 0;
$equipeId = isset($_POST['equipeId']) ? intval($_POST['equipeId']) : 0;
$noSequence = isset($_POST['noSequence']) ? intval($_POST['noSequence']) : -1;

if ($eventTypeCode <= 0) {
    $eventTypeCode = 5;
}

if ($chronoDemande <= 0 || $cameraId <= 0) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'message' => 'Paramètres invalides.'));
    exit;
}

if ($butId <= 0 && $matchIdRef > 0) {
    $conditions = array();
    $conditions[] = "match_event_id='{$matchIdRef}'";
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
    }
}

if ($butId <= 0) {
    http_response_code(404);
    echo json_encode(array('ok' => false, 'message' => "Impossible de trouver l'identifiant du but."));
    exit;
}

$qIns = "INSERT INTO DemandeAjoutVideo (
            eventId, typeEvenement, chronoDemande, cameraId, progression, dateCreation
        ) VALUES (
            '{$butId}', '{$eventTypeCode}', '{$chronoDemande}', '{$cameraId}', 1, NOW()
        )";

mysqli_query($conn, $qIns) or die(mysqli_error($conn) . $qIns);

echo json_encode(array(
    'ok' => true,
    'demandeId' => mysqli_insert_id($conn),
    'eventId' => $butId
));
?>
