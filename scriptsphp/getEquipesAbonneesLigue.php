<?php
header('Content-Type: application/json; charset=utf-8');

require '../scriptsphp/defenvvar.php';

$ligueId = isset($_GET['ligueId']) ? $_GET['ligueId'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';

if (!preg_match('/^\d+$/', $ligueId)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Paramètre ligueId invalide.'));
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Paramètre date invalide.'));
    exit;
}

if($workEnv=="production"){
    $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
} else {
    $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database, $db_port);
}

if (!$conn) {
    http_response_code(500);
    echo json_encode(array('error' => 'Connexion BD impossible.'));
    exit;
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, 'utf8');

$sql = "SELECT te.equipe_id AS id, te.nom_equipe AS nom
        FROM TableEquipe te
        INNER JOIN abonEquipeLigue ael
            ON ael.equipeId = te.equipe_id
        WHERE ael.ligueId = ?
          AND ael.permission < 31
          AND ael.debutAbon <= ?
          AND ael.finAbon >= ?
        ORDER BY te.nom_equipe ASC";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(array('error' => 'Préparation SQL impossible.'));
    exit;
}

mysqli_stmt_bind_param($stmt, 'sss', $ligueId, $date, $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$equipes = array();
while ($row = mysqli_fetch_assoc($result)) {
    $equipes[] = $row;
}

echo json_encode($equipes);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
