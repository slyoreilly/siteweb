<?php

include_once ($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR . "syncstatsconfig.php");

require '../scriptsphp/defenvvar.php';
require_once 'creeMatchDeClip.php';


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
					creerMatchSiInexistant($conn, $unClip['GameStringID'], $plateauId, $ligueId);
				}
	}

echo json_encode($syncOK);


mysqli_close($conn);

?>