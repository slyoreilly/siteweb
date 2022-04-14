<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);

$jDom =  isset($_POST['jDom'])? $_POST['jDom']:null;
$jVis =  isset($_POST['jVis'])? $_POST['jVis']:null;
$gDom = $_POST['gDom'];
$gVis = $_POST['gVis'];
$eqDom = $_POST['eqDom'];
$eqVis = $_POST['eqVis'];
$dateDeb = $_POST['dateDeb'];
if(isset($_POST['dateFin'])&&$_POST['dateFin']!="" ){$dateFin = $_POST['dateFin'];}else{$dateFin = '2050-01-01 00:00:00';}
$ligueId = $_POST['ligueId'];
$match_id =  isset($_POST['matchId'])? $_POST['matchId']:null;
$arenaId = is_numeric($_POST['arenaId']) ? $_POST['arenaId']: 0;
$arbitreId = $_POST['arbitreId'];
$appareils_json = isset($_POST['appareils'])?  $_POST['appareils']:null;
$appareils = json_decode($appareils_json, true);

$matchIdRef = substr($dateDeb, 0, 4) . "/" . substr($dateDeb, 5, 2) . "/" . substr($dateDeb, 8, 2) . "_" . $eqDom . "_" . $eqVis;

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

$result = mysqli_query($conn,"select timediff(now(),convert_tz(now(),@@global.time_zone,'+00:00'))");
$defTimeZone =mysqli_data_seek($result, 0);
mysqli_query($conn,"SET time_zone='+0:00'");



function getAlignement($connGA,$eqId,$defTimeZone)
{



	mysqli_query($connGA,"SET time_zone='{$defTimeZone}'");
	$resultJoueur = mysqli_query($connGA, "SELECT joueur_id
													FROM TableJoueur
													JOIN abonJoueurEquipe
														ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId)
														WHERE equipeId='{$eqId}'
														AND debutAbon<=DATE(NOW())
														AND finAbon>DATE(NOW())") or die(mysqli_error($connGA));
	mysqli_query($connGA,"SET time_zone='+0:00'");
	$alignement=array();
	//$alignement = "[";
	while ($rangeeJoueur = mysqli_fetch_array($resultJoueur)) {
array_push($alignement,$rangeeJoueur['joueur_id']);
 
}
	return json_encode($alignement);

}

$strEqDom = "";
$strEqVis = "";
$strGDom = "";
$strGVis = "";
$strJDom = "";
$strJVis = "";
$strArb = 0;

$milliseconds = round(microtime(true) * 1000);

//	echo "gDom: ".$gDom."   ";

if ($eqDom != 'undefined') {$strEqDom = "eqDom='{$eqDom}', ";
} else {$strEqDom = "";
	$eqDom = 0;
}
if ($eqVis != 'undefined') {$strEqVis = "eqVis='{$eqVis}', ";
} else {$strEqVis = "";
	$eqVis = 0;
}
if ($gDom != 'undefined' && $gDom != "") {$strGDom = "gardienDom='{$gDom}', ";
} else {$strGDom = "";
	$gDom = 0;
}
if ($gVis != 'undefined' && $gVis != "") {$strGVis = "gardienVis='{$gVis}', ";
} else {$strGVis = "";
	$gVis = 0;
}
if ($jDom == 'undefined'|| $jDom=="" || $jDom==null){
	$jDom=getAlignement($conn,$eqDom, $defTimeZone);
} else{
	$jDom=json_encode($jDom);
}$strJDom = "alignementDom='{$jDom}', ";
if ($jVis == 'undefined' || $jVis=="" || $jVis==null ){
	$jVis=getAlignement($conn,$eqVis, $defTimeZone);
}else{	
	$jVis=json_encode($jVis);
} $strJVis = "alignementVis='{$jVis}', ";

if ($arbitreId != 'undefined' && $arbitreId != "") {$strArb = "arbitreId='{$arbitreId}', ";
} else {
	$strArb = "";
	$arbitreId = 0;
}
if ($dateDeb == 'AAAA/MM/JJ 23:59') {$dateDeb = "2000/01/01 00:00";
}

if ($eqDom != 'undefined') {$strTMEqDom = "eq_dom='{$eqDom}', ";
} else {$strTMEqDom = "";
	$eqDom = 0;
}
if ($eqVis != 'undefined') {$strTMEqVis = "eq_vis='{$eqVis}', ";
} else {$strTMEqVis = "";
	$eqVis = 0;
}

////////////////////
//
//	Section TableMatch
//

$ret = mysqli_query($conn, "SELECT nom_equipe
						FROM TableEquipe 
						WHERE equipe_id='{$eqDom}'
						") or die(mysqli_error($conn));
$tmp = mysqli_fetch_row($ret);
$strNomEqDom = $tmp[0];

$ret = mysqli_query($conn, "SELECT nom_equipe
						FROM TableEquipe 
						WHERE equipe_id='{$eqVis}'
						") or die(mysqli_error($conn));
$tmp = mysqli_fetch_row($ret);
$strNomEqVis = $tmp[0];

$matchId = substr($dateDeb, 0, 4) . "/" . substr($dateDeb, 5, 2) . "/" . substr($dateDeb, 8, 2) . "_" . $strNomEqDom . "_" . $strNomEqVis . "_" . $ligueId;

if(is_numeric($match_id)){

	$qTMEUp = "UPDATE TableMatch SET matchId='{$matchId}', matchIdRef='{$matchIdRef}',arenaId={$arenaId},
	 " . $strTMEqDom . $strTMEqVis . $strGDom . $strGVis . $strJDom . $strJVis . $strArb . "
	date='{$dateDeb}',dateFin='{$dateFin}',ligueRef='{$ligueId}', dernierMAJ=NOW(), TSDMAJ='{$milliseconds}' WHERE match_id='{$match_id}'";
	$rTM = mysqli_query($conn, $qTMEUp) or die(mysqli_error($conn) . $qTMEUp);
	$retour = $match_id;
} else {
	$rTM = mysqli_query($conn, "INSERT INTO TableMatch (matchId, matchIdRef, mavId, alignementDom, alignementVis, gardienDom, gardienVis, eq_dom, eq_vis, date, dateFin, ligueRef,dernierMAJ, TSDMAJ, arenaId,arbitreId) 
VALUES ('{$matchId}','{$matchId}',null,'{$jDom}', '{$jVis}','{$gDom}','{$gVis}','{$eqDom}','{$eqVis}','{$dateDeb}','{$dateFin}','{$ligueId}',NOW(),'{$milliseconds}','{$arenaId}','{$arbitreId}')") or die(mysqli_error($conn) . " INSERT INTO TableMatch");
	$match_id = mysqli_insert_id($conn);
}



if ($appareils != null) {
	//echo 1;
	for ($a = 0; $a < count($appareils['cams']); $a++) {
	//echo 2;
		$qMatch = "SELECT matchId 
						FROM abonAppareilMatch 
						WHERE matchId='{$match_id}' and telId = '{$appareils['cams'][$a]['telId']}'";
		$rAAM = mysqli_query($conn, $qMatch) or die(mysqli_error($conn) . $qMatch);
		//$retour=$rAAM;
		//$retour =$appareils['cams'][$a]['abon'];

		if (mysqli_num_rows($rAAM) == 0) {
			if ($appareils['cams'][$a]['abon'] == 1) {
				mysqli_query($conn, "INSERT INTO abonAppareilMatch (matchId, surfaceId, gabaritId, posGabId, telId, role) 
					VALUES ('{$match_id}','{$appareils['cams'][$a]['surfaceId']}','{$appareils['cams'][$a]['gabaritId']}','{$appareils['cams'][$a]['posGabId']}', '{$appareils['cams'][$a]['telId']}','{$appareils['cams'][$a]['role']}')") or die(mysqli_error($conn) . " INSERT INTO abonAppareilMatch");
				$retour = mysqli_error($conn);
			}
			else{
				mysqli_query($conn, "INSERT INTO abonAppareilMatch (matchId, surfaceId, gabaritId, posGabId, telId, role) 
					VALUES ('{$match_id}','{$appareils['cams'][$a]['surfaceId']}','{$appareils['cams'][$a]['gabaritId']}','{$appareils['cams'][$a]['posGabId']}', '{$appareils['cams'][$a]['telId']}','0')") or die(mysqli_error($conn) . " INSERT INTO abonAppareilMatch");
				$retour = mysqli_error($conn);
			}
		} else {
			if ($appareils['cams'][$a]['abon'] == 1) {
				mysqli_query($conn, "UPDATE abonAppareilMatch SET surfaceId='{$appareils['cams'][$a]['surfaceId']}',gabaritId='{$appareils['cams'][$a]['gabaritId']}',posGabId='{$appareils['cams'][$a]['posGabId']}', role='{$appareils['cams'][$a]['role']}' WHERE matchId='{$match_id}' and telId = '{$appareils['cams'][$a]['telId']}'") or die(mysqli_error($conn) . " UPDATE INTO abonAppareilMatch");
			} else {
				mysqli_query($conn, "UPDATE abonAppareilMatch SET surfaceId='{$appareils['cams'][$a]['surfaceId']}',gabaritId='{$appareils['cams'][$a]['gabaritId']}',posGabId='{$appareils['cams'][$a]['posGabId']}', role='0' WHERE matchId='{$match_id}' and telId = '{$appareils['cams'][$a]['telId']}'") or die(mysqli_error($conn) . " UPDATE INTO abonAppareilMatch");
			//	mysqli_query($conn, "DELETE FROM abonAppareilMatch WHERE matchId='{$match_id}' and telId = '{$appareils['cams'][$a]['telId']}'") or die(mysqli_error() . " DELETE INTO abonAppareilMatch");
			}
		}
	}

	for ($a = 0; $a < count($appareils['remotes']); $a++) {
	//echo 3;
		$qMatch = "SELECT matchId 
						FROM abonAppareilMatch 
						WHERE matchId='{$match_id}' and telId = '{$appareils['remotes'][$a]['telId']}'";
		$rAAM = mysqli_query($conn, $qMatch) or die(mysqli_error($conn) . $qMatch);
		//$retour=$rAAM;
		//$retour =$appareils['remotes'][$a]['abon'];

		if (mysqli_num_rows($rAAM) == 0) {
				echo 3;
			if ($appareils['remotes'][$a]['abon'] == true) {
				echo 4;
				mysqli_query($conn, "INSERT INTO abonAppareilMatch (matchId, surfaceId, gabaritId, posGabId, telId, role) 
					VALUES ('{$match_id}','{$arenaId}','0',
					'0', '{$appareils['remotes'][$a]['telId']}',
					'{$appareils['remotes'][$a]['role']}')") or die(mysqli_error($conn) . " INSERT INTO abonAppareilMatch");
				$retour = mysqli_error($conn);
				echo 5;
			}
			else{
					mysqli_query($conn, "INSERT INTO abonAppareilMatch (matchId, surfaceId, gabaritId, posGabId, telId, role) 
					VALUES ('{$match_id}','{$arenaId}','0',
					'0', '{$appareils['remotes'][$a]['telId']}',
					'0')") or die(mysqli_error($conn) . " INSERT INTO abonAppareilMatch");
				$retour = mysqli_error($conn);
				
			}
		} else {
			//	echo 5;
			if ($appareils['remotes'][$a]['abon'] == true) {
				mysqli_query($conn, "UPDATE abonAppareilMatch SET surfaceId='{$arenaId}',gabaritId='0',posGabId='0', role='{$appareils['remotes'][$a]['role']}' WHERE matchId='{$match_id}' and telId = '{$appareils['remotes'][$a]['telId']}'") or die(mysqli_error($conn) . " UPDATE INTO abonAppareilMatch");
			} else {
				mysqli_query($conn, "UPDATE abonAppareilMatch SET surfaceId='{$arenaId}',gabaritId='0',posGabId='0', role='0' WHERE matchId='{$match_id}' and telId = '{$appareils['remotes'][$a]['telId']}'") or die(mysqli_error($conn) . " UPDATE INTO abonAppareilMatch");
			//	mysqli_query($conn, "DELETE FROM abonAppareilMatch WHERE matchId='{$match_id}' and telId = '{$appareils['remotes'][$a]['telId']}'") or die(mysqli_error() . " DELETE INTO abonAppareilMatch");
			}
		}
	}

}
mysqli_close($conn);
echo $retour;
?>
