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

if (!$conn) {
    http_response_code(500);
    echo json_encode(array('ok' => false, 'error' => 'Connexion BD impossible.'));
    exit;
}

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

$sqlMatch = "SELECT eq_dom, eq_vis, date FROM TableMatch WHERE matchIdRef = ? AND ligueRef = ? LIMIT 1";
$stmtMatch = mysqli_prepare($conn, $sqlMatch);
if (!$stmtMatch) {
    http_response_code(500);
    echo json_encode(array('ok' => false, 'error' => 'Préparation match impossible.'));
    exit;
}
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

$matchDate = substr($match['date'], 0, 10);

$sqlJoueur = "SELECT joueurId
              FROM abonJoueurEquipe
              WHERE equipeId = ?
                AND debutAbon <= ?
                AND finAbon >= ?
              ORDER BY joueurId ASC
              LIMIT 1";
$stmtJoueur = mysqli_prepare($conn, $sqlJoueur);
if (!$stmtJoueur) {
    http_response_code(500);
    echo json_encode(array('ok' => false, 'error' => 'Préparation joueur impossible.'));
    exit;
}
mysqli_stmt_bind_param($stmtJoueur, 'sss', $nouvelleEquipeId, $matchDate, $matchDate);
mysqli_stmt_execute($stmtJoueur);
$resJoueur = mysqli_stmt_get_result($stmtJoueur);
$joueur = mysqli_fetch_assoc($resJoueur);
mysqli_stmt_close($stmtJoueur);

if (!$joueur || !isset($joueur['joueurId'])) {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'error' => 'Aucun joueur actif trouvé dans la nouvelle équipe à la date du match.'));
    exit;
}

$nouveauMarqueurId = strval($joueur['joueurId']);

mysqli_begin_transaction($conn);
$ok = true;

$sqlMajDom = "UPDATE TableMatch SET eq_dom = ? WHERE matchIdRef = ? AND eq_dom = ?";
$stmtMajDom = mysqli_prepare($conn, $sqlMajDom);
if (!$stmtMajDom) {
    $ok = false;
} else {
    mysqli_stmt_bind_param($stmtMajDom, 'sss', $nouvelleEquipeId, $matchId, $ancienneEquipeId);
    if (!mysqli_stmt_execute($stmtMajDom)) { $ok = false; }
    mysqli_stmt_close($stmtMajDom);
}

$sqlMajVis = "UPDATE TableMatch SET eq_vis = ? WHERE matchIdRef = ? AND eq_vis = ?";
$stmtMajVis = mysqli_prepare($conn, $sqlMajVis);
if (!$stmtMajVis) {
    $ok = false;
} else {
    mysqli_stmt_bind_param($stmtMajVis, 'sss', $nouvelleEquipeId, $matchId, $ancienneEquipeId);
    if (!mysqli_stmt_execute($stmtMajVis)) { $ok = false; }
    mysqli_stmt_close($stmtMajVis);
}

$sqlRetirePasseurs = "UPDATE TableEvenement0
                      SET code = 15
                      WHERE match_event_id = ?
                        AND equipe_event_id = ?
                        AND code = 1";
$stmtRetirePasseurs = mysqli_prepare($conn, $sqlRetirePasseurs);
if (!$stmtRetirePasseurs) {
    $ok = false;
    $nbPasseursRetires = 0;
} else {
    mysqli_stmt_bind_param($stmtRetirePasseurs, 'ss', $matchId, $ancienneEquipeId);
    if (!mysqli_stmt_execute($stmtRetirePasseurs)) { $ok = false; }
    $nbPasseursRetires = mysqli_stmt_affected_rows($stmtRetirePasseurs);
    mysqli_stmt_close($stmtRetirePasseurs);
}

$sqlMajButs = "UPDATE TableEvenement0
               SET joueur_event_ref = ?
               WHERE match_event_id = ?
                 AND equipe_event_id = ?
                 AND code = 0";
$stmtMajButs = mysqli_prepare($conn, $sqlMajButs);
if (!$stmtMajButs) {
    $ok = false;
    $nbButsReattribues = 0;
} else {
    mysqli_stmt_bind_param($stmtMajButs, 'sss', $nouveauMarqueurId, $matchId, $ancienneEquipeId);
    if (!mysqli_stmt_execute($stmtMajButs)) { $ok = false; }
    $nbButsReattribues = mysqli_stmt_affected_rows($stmtMajButs);
    mysqli_stmt_close($stmtMajButs);
}

$sqlMajEvt = "UPDATE TableEvenement0 SET equipe_event_id = ? WHERE match_event_id = ? AND equipe_event_id = ?";
$stmtMajEvt = mysqli_prepare($conn, $sqlMajEvt);
if (!$stmtMajEvt) {
    $ok = false;
    $nbEventsMaj = 0;
} else {
    mysqli_stmt_bind_param($stmtMajEvt, 'sss', $nouvelleEquipeId, $matchId, $ancienneEquipeId);
    if (!mysqli_stmt_execute($stmtMajEvt)) { $ok = false; }
    $nbEventsMaj = mysqli_stmt_affected_rows($stmtMajEvt);
    mysqli_stmt_close($stmtMajEvt);
}

if ($ok) {
    mysqli_commit($conn);
    echo json_encode(array(
        'ok' => true,
        'message' => 'Match et événements mis à jour.',
        'eventsUpdated' => $nbEventsMaj,
        'goalsReassigned' => $nbButsReattribues,
        'assistsRemoved' => $nbPasseursRetires,
        'newScorerId' => $nouveauMarqueurId
    ));
} else {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(array('ok' => false, 'error' => 'Erreur lors de la mise à jour.'));
}

?>
