<?php
require '../scriptsphp/defenvvar.php';
header('Content-Type: application/json; charset=utf-8');

$videoId = isset($_POST['videoId']) ? intval($_POST['videoId']) : 0;
$type = isset($_POST['type']) ? intval($_POST['type']) : null;

if ($videoId <= 0 || $type === null) {
    echo json_encode(array('ok' => false, 'message' => 'Paramètres invalides (videoId/type).'));
    exit;
}

$sqlPreview = "UPDATE Video SET type=" . intval($type) . " WHERE videoId=" . intval($videoId) . " LIMIT 1;";

$stmt = mysqli_prepare($conn, "UPDATE Video SET type=? WHERE videoId=? LIMIT 1");
if (!$stmt) {
    echo json_encode(array('ok' => false, 'message' => 'Préparation SQL impossible.', 'sql' => $sqlPreview));
    exit;
}

mysqli_stmt_bind_param($stmt, 'ii', $type, $videoId);
$ok = mysqli_stmt_execute($stmt);
if (!$ok) {
    echo json_encode(array('ok' => false, 'message' => mysqli_error($conn), 'sql' => $sqlPreview));
    exit;
}

$affected = mysqli_stmt_affected_rows($stmt);
echo json_encode(array('ok' => true, 'affectedRows' => $affected, 'sql' => $sqlPreview));
