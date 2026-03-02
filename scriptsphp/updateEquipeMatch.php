<?php
header('Content-Type: application/json; charset=utf-8');

require '../scriptsphp/defenvvar.php';

$postdata = file_get_contents('php://input');
$request = json_decode($postdata);

$ligueId = isset($request->ligueId) ? strval($request->ligueId) : '';
$userId = isset($request->userId) ? strval($request->userId) : '';
$matchId = isset($request->matchId) ? strval($request->matchId) : '';
$ancienneEquipeId = isset($request->ancienneEquipeId) ? strval($request->ancienneEquipeId) : '';
$nouvelleEquipeId = isset($request->nouvelleEquipeId) ? strval($request->nouvelleEquipeId) : '';

if (!preg_match('/^\d+$/', $ligueId) || $userId === '' || $matchId === '' || !preg_match('/^\d+$/', $ancienneEquipeId) || !preg_match('/^\d+$/', $nouvelleEquipeId)) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'error' => 'Paramètres invalides.'));
    exit;
}

if ($ancienneEquipeId === $nouvelleEquipeId) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'error' => 'L\'ancienne équipe et la nouvelle équipe sont identiques.'));
    exit;
}

if($workEnv=="production"){
    $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
} else {
    $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database, $db_port);
}

if (!$conn) {
    http_response_code(500);
    echo json_encode(array('ok' => false, 'error' => 'Connexion BD impossible.'));
    exit;
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, 'utf8');

$sqlPerm = "SELECT al.type AS permission
            FROM AbonnementLigue al
            INNER JOIN TableUser tu ON tu.noCompte = al.userid
            WHERE al.ligueid = ?
              AND tu.username = ?
            LIMIT 1";
$stmtPerm = mysqli_prepare($conn, $sqlPerm);
if (!$stmtPerm) {
    http_response_code(500);
    echo json_encode(array('ok' => false, 'error' => 'Préparation permission impossible.'));
    exit;
}
mysqli_stmt_bind_param($stmtPerm, 'ss', $ligueId, $userId);
mysqli_stmt_execute($stmtPerm);
$resPerm = mysqli_stmt_get_result($stmtPerm);
$rangPerm = mysqli_fetch_assoc($resPerm);
mysqli_stmt_close($stmtPerm);

if (!$rangPerm || intval($rangPerm['permission']) !== 1) {
    http_response_code(403);
    echo json_encode(array('ok' => false, 'error' => 'Permission insuffisante. Niveau requis: 1.'));
    exit;
}

$sqlMatch = "SELECT eq_dom, eq_vis FROM TableMatch WHERE matchIdRef = ? AND ligueRef = ? LIMIT 1";
$stmtMatch = mysqli_prepare($conn, $sqlMatch);
mysqli_stmt_bind_param($stmtMatch, 'ss', $matchId, $ligueId);
mysqli_stmt_execute($stmtMatch);
$resMatch = mysqli_stmt_get_result($stmtMatch);
$match = mysqli_fetch_assoc($resMatch);
mysqli_stmt_close($stmtMatch);

if (!$match) {
    http_response_code(404);
    echo json_encode(array('ok' => false, 'error' => 'Match introuvable dans la ligue.'));
    exit;
}

if ($match['eq_dom'] != $ancienneEquipeId && $match['eq_vis'] != $ancienneEquipeId) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'error' => 'L\'équipe à remplacer n\'est pas dans ce match.'));
    exit;
}

mysqli_begin_transaction($conn);
$ok = true;
    $sqlMajDom = "UPDATE TableMatch SET eq_dom = ? WHERE matchIdRef = ? AND eq_dom = ?";
    $stmtMajDom = mysqli_prepare($conn, $sqlMajDom);
    mysqli_stmt_bind_param($stmtMajDom, 'sss', $nouvelleEquipeId, $matchId, $ancienneEquipeId);
    if (!mysqli_stmt_execute($stmtMajDom)) { $ok = false; }
    mysqli_stmt_close($stmtMajDom);

    $sqlMajVis = "UPDATE TableMatch SET eq_vis = ? WHERE matchIdRef = ? AND eq_vis = ?";
    $stmtMajVis = mysqli_prepare($conn, $sqlMajVis);
    mysqli_stmt_bind_param($stmtMajVis, 'sss', $nouvelleEquipeId, $matchId, $ancienneEquipeId);
    if (!mysqli_stmt_execute($stmtMajVis)) { $ok = false; }
    mysqli_stmt_close($stmtMajVis);

    $sqlMajEvt = "UPDATE TableEvenement0 SET equipe_event_id = ? WHERE match_event_id = ? AND equipe_event_id = ?";
    $stmtMajEvt = mysqli_prepare($conn, $sqlMajEvt);
    mysqli_stmt_bind_param($stmtMajEvt, 'sss', $nouvelleEquipeId, $matchId, $ancienneEquipeId);
    if (!mysqli_stmt_execute($stmtMajEvt)) { $ok = false; }
    $nbEventsMaj = mysqli_stmt_affected_rows($stmtMajEvt);
    mysqli_stmt_close($stmtMajEvt);

if ($ok) {
    mysqli_commit($conn);
    echo json_encode(array(
        'ok' => true,
        'message' => 'Match et événements mis à jour.',
        'eventsUpdated' => $nbEventsMaj
    ));
} else {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(array('ok' => false, 'error' => 'Erreur lors de la mise à jour.'));
}

mysqli_close($conn);
?>
