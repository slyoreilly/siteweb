<?php
header('Content-Type: application/json; charset=utf-8');

require '../scriptsphp/defenvvar.php';

$date = isset($_GET['date']) ? $_GET['date'] : '';
$ligueId = isset($_GET['ligueId']) ? $_GET['ligueId'] : '';

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Paramètre date invalide. Format attendu: YYYY-MM-DD.'));
    exit;
}

if (!preg_match('/^\d+$/', $ligueId)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Paramètre ligueId invalide.'));
    exit;
}

if (!$conn) {
    http_response_code(500);
    echo json_encode(array('error' => 'Connexion BD impossible.'));
    exit;
}

$sql = "SELECT
            tm.matchIdRef AS matchId,
            tm.date,
            tm.eq_dom AS eqDomId,
            tm.eq_vis AS eqVisId,
            ld.nom_equipe AS equipeDomicile,
            lv.nom_equipe AS equipeVisiteur,
            l.Nom_Ligue AS ligue
        FROM TableMatch tm
        LEFT JOIN TableEquipe ld ON ld.equipe_id = tm.eq_dom
        LEFT JOIN TableEquipe lv ON lv.equipe_id = tm.eq_vis
        LEFT JOIN Ligue l ON l.ID_Ligue = tm.ligueRef
        WHERE DATE(tm.date) = ?
          AND tm.ligueRef = ?
        ORDER BY tm.date ASC";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(array('error' => 'Préparation SQL impossible.'));
    exit;
}

mysqli_stmt_bind_param($stmt, 'ss', $date, $ligueId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$matchs = array();
while ($row = mysqli_fetch_assoc($result)) {
    $matchs[] = $row;
}

echo json_encode($matchs);

mysqli_stmt_close($stmt);
?>
