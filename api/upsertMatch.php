<?php
////////////////////////////////////////////////////////////
//
//	upsertMatch.php
//	Est appelé dans MatchRepository.kt
//
//
////////////////////////////////////////////////////////////

include_once ($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR . "syncstatsconfig.php");

require '../scriptsphp/defenvvar.php';

$preMatch =null;
if(isset($_POST['match'])){
	$preMatch = $_POST["match"];
	$matchArray = json_decode($preMatch, true);
	}
	


$heure = $_POST['heure'];
$heureServeur = time()*1000;

$syncOK = array();

if($matchArray != null) {


	foreach ($matchArray as $match) {

		if (isset($heure)) {
			// retourner le but, sans correction de chrono.
			//$unClip['chrono'] = $unClip['chrono'] + $heureServeur - $heure;
		}

		
		if($match['GameComId']<1){
			$qInsM = "INSERT INTO TableMatch (eq_dom, score_dom, eq_vis, score_vis, statut, matchIdRef, ligueRef, date, cleValeur, arenaId, TSDMAJ) 
			VALUES ('{$match['eqDom']}','{$match['scoreDom']}','{$match['eqVis']}','{$match['scoreVis']}',0,'{$match['matchLongId']}','{$match['ligueId']}','{$match['date']}','{$match['cleValeur']}','{$match['arenaId']}','{$match['dernierMAJ']}')";

			mysqli_query($conn,$qInsM) or die(mysqli_error($conn) . $qInsM);
			$webMatchId=mysqli_insert_id($conn);

		}

		else{
			$retour = mysqli_query($conn,"UPDATE TableMatch 
			SET eq_dom='{$match['eqDom']}',
			score_dom={$match['scoreDom']},
			eq_vis='{$match['eqVis']}',
			score_vis={$match['scoreVis']},
			statut='{$match['etat']}',
			matchIdRef='{$match['matchLongId']}',
			ligueRef='{$match['ligueId']}',
			date='{$match['date']}',
			cleValeur='{$match['cleValeur']}',
			arenaId='{$match['eqDom']}',
			TSDMAJ=NOW() 
			WHERE match_id='{$match['GameComId']}'");	

			$webMatchId = $match['GameComId'];

		}

			

		

		

				
				$retObj = array("GameLocId"=>$match['GameLocId'], "GameComId"=>$webMatchId);
				array_push($syncOK, $retObj);
		
			}		
		}

echo json_encode($syncOK);


mysqli_close($conn);

?>