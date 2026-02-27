<?php
require '../scriptsphp/defenvvar.php';
header('Content-Type: application/json; charset=utf-8');

$nomFichier = isset($_POST['nomFichier']) ? trim($_POST['nomFichier']) : '';
$nomMatch = isset($_POST['nomMatch']) ? trim($_POST['nomMatch']) : '';
$camId = isset($_POST['camId']) ? trim($_POST['camId']) : '';
$chrono = isset($_POST['chrono']) ? intval($_POST['chrono']) : 0;
$type = isset($_POST['type']) ? intval($_POST['type']) : 0;
$reference = isset($_POST['reference']) ? intval($_POST['reference']) : 0;
$emplacement = isset($_POST['emplacement']) ? trim($_POST['emplacement']) : 'syncstats.com';
$angleOk = isset($_POST['angleOk']) ? intval($_POST['angleOk']) : 0;

if ($nomFichier === '' || $camId === '' || $chrono <= 0) {
    echo json_encode(array('ok' => false, 'message' => 'Champs requis manquants (nomFichier/camId/chrono).'));
    exit;
}

$q = "INSERT INTO Video (nomFichier,nomMatch,chrono,camId,type,reference,emplacement,angleOk) VALUES (?,?,?,?,?,?,?,?)";
$stmt = mysqli_prepare($conn, $q);
if (!$stmt) {
    echo json_encode(array('ok' => false, 'message' => 'Préparation SQL impossible.', 'sql' => $q));
    exit;
}

mysqli_stmt_bind_param($stmt, 'ssisiisi', $nomFichier, $nomMatch, $chrono, $camId, $type, $reference, $emplacement, $angleOk);
$ok = mysqli_stmt_execute($stmt);
if (!$ok) {
    echo json_encode(array('ok' => false, 'message' => mysqli_error($conn), 'sql' => $q));
    exit;
}

$videoId = mysqli_insert_id($conn);

echo json_encode(array('ok' => true, 'videoId' => $videoId));
