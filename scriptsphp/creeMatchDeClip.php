<?php

function creerMatchSiInexistant($conn, $matchClientId, $plateauId, $ligueId) {
    $now = date("Y-m-d H:i:s");
    $tsdm = round(microtime(true) * 1000);

    $qCheck = "SELECT * FROM TableMatch WHERE matchIdRef = '$matchClientId'";
    $result = mysqli_query($conn, $qCheck);
    if (mysqli_num_rows($result) > 0) {
        return null; // Match existe déjà
    }

    $qEquipes = "SELECT equipeId FROM TableEquipe WHERE ligueId = $ligueId LIMIT 2";
    $resEquipes = mysqli_query($conn, $qEquipes);

    if (mysqli_num_rows($resEquipes) < 2) {
        throw new Exception("Pas assez d'équipes dans la ligue $ligueId");
    }

    $equipes = [];
    while ($row = mysqli_fetch_assoc($resEquipes)) {
        $equipes[] = intval($row['equipeId']);
    }

    $eqDom = $equipes[0];
    $eqVis = $equipes[1];

    $qInsert = "INSERT INTO TableMatch (
        eq_dom, score_dom, eq_vis, score_vis, statut, matchIdRef,
        ligueRef, date, TSDMAJ, arenaId
    ) VALUES (
        $eqDom, 0, $eqVis, 0, 'F', '$matchClientId',
        $ligueId, '$now', $tsdm, $plateauId
    )";

    if (!mysqli_query($conn, $qInsert)) {
        throw new Exception("Erreur MySQL : " . mysqli_error($conn));
    }

    return mysqli_insert_id($conn);
}
