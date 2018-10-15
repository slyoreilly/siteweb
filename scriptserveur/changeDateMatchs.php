<?php
$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection/
if (!$conn) {
	error_log("Connection failed: " . mysqli_connect_error());
   die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

	
	//////////////////////////////////////////////////////////////////////
//
//	
//
/////////////////////////////////////////////////////////////////////


$cree = $_GET['cree'];
$ligueId = $_GET['ligueId'];
$dateOrig="2018-06-27"." 00:00:00";
$dateDest="2018-05-27";
$limDateOrig="2018-06-27"." 23:59:59";
$mavIdDate="2018/05/27";

$retour1 = mysqli_query($conn,"SELECT * FROM TableMatch 
							WHERE ligueRef='{$ligueId}'
							AND date>='{$dateOrig}' 
							AND date<='{$limDateOrig}'");
								error_log("Nb Taches: " . mysqli_num_rows($retour1));
							
		if(mysqli_num_rows($retour1)>0)			
		{while($rangee = mysqli_fetch_assoc($retour1)){
			mysqli_query($conn,"INSERT INTO TableMatch 
(`eq_dom`, `score_dom`, `eq_vis`, `score_vis`, `statut`, `matchIdRef`, `ligueRef`, `date`, `cleValeur`, `matchId`, `dateFin`, `alignementDom`, `gardienDom`, `alignementVis`, `gardienVis`, `dernierMAJ`, `arenaId`, `arbitreId`, `mavId`) 
VALUES ('{$rangee['eq_dom']}',0,'{$rangee['eq_vis']}',0,'',CONCAT('{$rangee['matchIdRef']}','_X'),'{$ligueId}',DATE_ADD('{$rangee['date']}', INTERVAL -31 DAY),'{$rangee['cleValeur']}',CONCAT('{$rangee['matchIdRef']}','_X'),'{$rangee['dateFin']}','{$rangee['alignementDom']}','{$rangee['gardienDom']}','{$rangee['alignementVis']}','{$rangee['gardienVis']}',NOW(),'{$rangee['arenaId']}','{$rangee['arbitreId']}',NULL)") or die(mysqli_error($conn));
								error_log("Nb Taches: " . mysqli_num_rows($retour1));
			$mavId=$mavIdDate."_".$rangee['eq_dom']."_".$rangee['eq_vis'];
			$retour = mysqli_query($conn, "INSERT INTO MatchAVenir (matchId, alignementDom, alignementVis, gardienDom, gardienVis, eqDom, eqVis, date, dateFin, ligueId,dernierMAJ,arenaId,arbitreId) 
VALUES ('{$mavId}','{$rangee['alignementDom']}','{$rangee['alignementVis']}','{$rangee['gardienDom']}','{$rangee['gardienVis']}','{$rangee['eq_dom']}','{$rangee['eq_vis']}',DATE_ADD('{$rangee['date']}', INTERVAL -31 DAY),'{$rangee['dateFin']}','{$ligueId}',NOW(),'{$rangee['arenaId']}','{$rangee['arbitreId']}')") or die(mysqli_error() . " INSERT INTO MatchAVenir");

$rTM = mysqli_query($conn, "SELECT mavId 
						FROM MatchAVenir
						WHERE matchId='{$mavId}'") or die(mysqli_error());

if (mysqli_num_rows($rTM) > 0) {
	$mav_id_vec = mysqli_fetch_row($rTM);
	$mav_id = $mav_id_vec[0];
	$qUpMAV = "UPDATE TableMatch SET mavId='{$mav_id}'  WHERE matchIdRef=CONCAT('{$rangee['matchIdRef']}','_X')";
	$retour = mysqli_query($conn, $qUpMAV) or die(mysqli_error() . $qUpMAV);
	
	
}			
			
			
		}
		
		}


//include 'library/closedb.php';
	
?>
