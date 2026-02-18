<?php

include_once ($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR . "syncstatsconfig.php");

require '../scriptsphp/defenvvar.php';

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


$preClips =null;
if(isset($_POST['clips'])){
	$preClips = $_POST["clips"];
	$clips = json_decode($preClips, true);
	}
	


$heure = $_POST['heure'];
$heureServeur = time()*1000;

$syncOK = array();


foreach ($clips as $unClip) {


		if (isset($heure)) {
			// retourner le but, sans correction de chrono.
			$unClip['chrono'] = $unClip['chrono'] + $heureServeur - $heure;
		}


			

				$qInsM = "INSERT INTO Clips (matchId, chrono, scoringEnd, type) VALUES ('{$unClip['GameStringID']}','{$unClip['chrono']}',Null,5)";

				mysqli_query($conn,$qInsM) or die(mysqli_error($conn) . $qInsM);
				$webIdClip=mysqli_insert_id($conn);
				

				
				$retObj = array("id"=>$unClip["id"],"SyncKey"=>$webIdClip);
				array_push($syncOK, $retObj);

				if(isset($unClip['plateauId'])&&isset($unClip['ligueId']))
				{
					creerMatchSiInexistant($conn, $unClip['GameStringID'],  $unClip['plateauId'], $unClip['ligueId']);
				}
	}

echo json_encode($syncOK);


//mysqli_close($conn);

?>