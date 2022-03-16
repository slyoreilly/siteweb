<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$lesMatchs = $_POST['lesMatchs'];


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");


$IM=0;

foreach($lesMatchs as $unMatch){
	


$eqDom = $unMatch['eqDom'];
$eqVis = $unMatch['eqVis'];
$dateDeb = $unMatch['dateDeb'];
$dateFin = $unMatch['dateFin'];
$ligueId = $unMatch['ligueId'];
$arenaId = $unMatch['arenaId'];
$arbitreId = $unMatch['arbitreId'];
$appareils = $unMatch['appareils'];
//$appareils_json = $unMatch['appareils'];
//$appareils = json_decode($appareils_json, true);

//$matchId = substr($dateDeb, 0, 4) . "/" . substr($dateDeb, 5, 2) . "/" . substr($dateDeb, 8, 2) . "_" . $eqDom . "_" . $eqVis;


$strEqDom = "";
$strEqVis = "";
$strGDom = "";
$strGVis = "";
$strJDom = "";
$strJVis = "";
$strArb = 0;

//	echo "gDom: ".$gDom."   ";

if ($eqDom != 'undefined') {$strEqDom = "eqDom='{$eqDom}', ";
} else {$strEqDom = "";
	$eqDom = 0;
}
if ($eqVis != 'undefined') {$strEqVis = "eqVis='{$eqVis}', ";
} else {$strEqVis = "";
	$eqVis = 0;
}
$strGDom = "";
	$gDom = 0;

$strGVis = "";
	$gVis = 0;

$strJDom = "alignementDom=NULL, ";
$strJVis = "alignementVis=NULL, ";

if ($arbitreId != 'undefined' && $arbitreId != '' ) {$strArb = "arbitreId='{$arbitreId}', ";
} else {
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


	$resultJoueur = mysqli_query($conn, "SELECT joueur_id
													FROM TableJoueur
													JOIN abonJoueurEquipe
														ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId)
														WHERE equipeId='{$eqDom}'
														AND debutAbon<=DATE(NOW())
														AND finAbon>DATE(NOW())") or die(mysqli_error($conn));
	$jDom = "[";
	while ($rangeeJoueur = mysqli_fetch_array($resultJoueur)) {

		$jDom .= $rangeeJoueur['joueur_id'] . ",";
	}//Fin du scan des joueurs

	if (!strcmp(",", substr($jDom, -1)))// Pour �viter les vides;
	{
		$jDom = substr($jDom, 0, -1);
	}
	$jDom .= "]";
	//fin des joueurs d'une �quipe

	$rJVis = mysqli_query($conn, "SELECT joueur_id
													FROM TableJoueur
													JOIN abonJoueurEquipe
														ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId)
														WHERE equipeId='{$eqVis}'
														AND debutAbon<=DATE(NOW())
														AND finAbon>DATE(NOW())") or die(mysqli_error($conn));
	$jVis = "[";
	while ($rangeeJVis = mysqli_fetch_array($rJVis)) {

		$jVis .= $rangeeJVis['joueur_id'] . ",";
	}//Fin du scan des joueurs

	if (!strcmp(",", substr($jVis, -1)))// Pour �viter les vides;
	{
		$jVis = substr($jVis, 0, -1);
	}
	$jVis .= "]";
	//fin des joueurs d'une �quipe
/*
	$retour = mysqli_query($conn, "INSERT INTO MatchAVenir (matchId, alignementDom, alignementVis, gardienDom, gardienVis, eqDom, eqVis, date, dateFin, ligueId,dernierMAJ,arenaId,arbitreId) 
VALUES ('{$matchId}','{$jDom}', '{$jVis}','{$gDom}','{$gVis}','{$eqDom}','{$eqVis}','{$dateDeb}','{$dateFin}','{$ligueId}',NOW(),'{$arenaId}','{$arbitreId}')") or die(mysqli_error($conn) . " INSERT INTO MatchAVenir");

	$ret = mysqli_query($conn, "SELECT mavId 
						FROM MatchAVenir 
						WHERE 1 
						ORDER BY mavId DESC") or die(mysqli_error($conn));
	$tmp = mysqli_fetch_row($ret);
	$retour = $tmp[0];
	$mavId = $retour;
*/


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
$qQuery ="INSERT INTO TableMatch (matchId, matchIdRef, mavId, alignementDom, alignementVis, gardienDom, gardienVis, eq_dom, eq_vis, date, dateFin, ligueRef,dernierMAJ,arenaId,arbitreId) 
VALUES ('{$matchId}','{$matchId}', NULL ,'{$jDom}', '{$jVis}','{$gDom}','{$gVis}','{$eqDom}','{$eqVis}','{$dateDeb}','{$dateFin}','{$ligueId}',NOW(),'{$arenaId}','{$arbitreId}')";
//echo $qQuery;
$rTM = mysqli_query($conn, $qQuery) or die(mysqli_error($conn) . " INSERT INTO TableMatch");
	$match_id = mysqli_insert_id($conn);


if ($appareils != null) {
	//echo 1;
	if($appareils['cams']!=null){
	for ($a = 0; $a < count($appareils['cams']); $a++) {
	//echo 2;

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
		
	}
}
if($appareils['remotes']!=null){
	for ($a = 0; $a < count($appareils['remotes']); $a++) {
	//echo 3;
						echo 3;
			if ($appareils['remotes'][$a]['abon'] == true) {
				echo 4;
				mysqli_query($conn, "INSERT INTO abonAppareilMatch (matchId, surfaceId, gabaritId, posGabId, telId, role) 
					VALUES ('{$match_id}','{$appareils['remotes'][$a]['surfaceId']}','{$appareils['remotes'][$a]['gabaritId']}',
					'{$appareils['remotes'][$a]['posGabId']}', '{$appareils['remotes'][$a]['telId']}',
					'{$appareils['remotes'][$a]['role']}')") or die(mysqli_error($conn) . " INSERT INTO abonAppareilMatch");
				$retour = mysqli_error($conn);
				echo 5;
			}
			else{
					mysqli_query($conn, "INSERT INTO abonAppareilMatch (matchId, surfaceId, gabaritId, posGabId, telId, role) 
					VALUES ('{$match_id}','{$appareils['remotes'][$a]['surfaceId']}','{$appareils['remotes'][$a]['gabaritId']}',
					'{$appareils['remotes'][$a]['posGabId']}', '{$appareils['remotes'][$a]['telId']}',
					'0')") or die(mysqli_error($conn) . " INSERT INTO abonAppareilMatch");
				$retour = mysqli_error($conn);
				
			}
			}
		}
}
}
echo 1;
?>
