<?php
require 'defenvvar.php';

$butId = isset($_POST['butId']) ? intval($_POST['butId']) : 0;
$chronoDemande = isset($_POST['chrono']) ? intval($_POST['chrono']) : 0;
$cameraId = isset($_POST['cameraId']) ? intval($_POST['cameraId']) : 0;
$eventTypeCode = isset($_POST['eventTypeCode']) ? intval($_POST['eventTypeCode']) : 0;

if ($eventTypeCode <= 0) {
    $eventTypeCode = 5;
}

if ($butId <= 0 || $chronoDemande <= 0 || $cameraId <= 0) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'message' => 'Paramètres invalides.'));
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
    'demandeId' => mysqli_insert_id($conn)
));
?>
