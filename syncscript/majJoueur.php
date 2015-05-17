<?php
$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

header('Content-type: text/html; charset=utf-8');

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$username = $_POST['username'];
//$password = $_POST['password'];
//$matchjson = stripslashes($_POST['matchjson']);
$matchjson = stripslashes($_POST['matchjson']);

if (!mysql_connect($db_host, $db_user, $db_pwd))
	die("Can't connect to database");

if (!mysql_select_db($database)) {
	echo "<h1>Database: {$database}</h1>";
	echo "<h1>Table: {$table}</h1>";
	die("Can't select database");
}

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

$totalJSON = json_decode($matchjson, true);

for ($a = 0; $a < count($totalJSON); $a++) {
	$leMatch = $totalJSON[$a];

	$intEquipe = $leMatch['equipeId'];
	$intLigue = $leMatch['ligueId'];
	$intJoueur = mysql_real_escape_string($leMatch['nomJoueur']);
	$intNo = $leMatch['noJoueur'];

	$joueurId = $leMatch['joueurId'];
	$position = $leMatch['position'];

	$resultNouveau = mysql_query("UPDATE {$tableJoueur} SET NomJoueur='{$intJoueur}', NumeroJoueur='{$leMatch['noJoueur']}',position='{$leMatch['position']}',dernierMAJ=NOW() WHERE joueur_id='{$joueurId}' ") or die(mysql_error() . "update bug");

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	$retour = mysql_query("SELECT abonJouLig 
						FROM abonJoueurLigue 
						WHERE joueurId={$joueurId}
						AND ligueId = {$leMatch['ligueId']}
						AND finAbon>NOW()") or die(mysql_error() . "select ligue" . "SELECT abonJouLig FROM abonJoueurLigue WHERE joueurId={$joueurId} AND ligueId = {$leMatch['ligueId']} AND finAbon>NOW()");

	if (mysql_num_rows($retour) > 0) {
		/*			$mr = mysql_fetch_row($retour);
		 mysql_query("UPDATE abonJoueurLigue SET finAbon=NOW() WHERE abonJouLig='{$mr[0]}' ")
		 or die(mysql_error()."update bug");  */
	} else {$retour = mysql_query("INSERT INTO abonJoueurLigue (joueurId, ligueId, permission, debutAbon, finAbon) 
		VALUES ('{$joueurId}', '{$intLigue}',30, NOW(),'2030-01-01')") or die(mysql_error() . "insert Ligue");
	}
	///////////////////////////////////
	/// Section Equipe
	//////////////////////////////////

	$retour = mysql_query("SELECT abonJouEq 
						FROM abonJoueurEquipe 
						JOIN abonEquipeLigue
							ON (abonJoueurEquipe.equipeId=abonEquipeLigue.equipeId)
						WHERE joueurId={$joueurId}
						AND ligueId = {$leMatch['ligueId']}
						AND abonJoueurEquipe.finAbon>NOW()") or die(mysql_error() . "select bug EQ");

	if (mysql_num_rows($retour) > 0) {
		$mr = mysql_fetch_row($retour);
		mysql_query("UPDATE abonJoueurEquipe SET finAbon=NOW() WHERE abonJouEq='{$mr[0]}' ") or die(mysql_error() . "update bug EQ");
	}
	if ($intEquipe != 0) {
		$retour = mysql_query("INSERT INTO abonJoueurEquipe (joueurId, equipeId, permission, debutAbon, finAbon) 
		VALUES ('{$joueurId}', '{$intEquipe}',30, NOW(),'2030-01-01')") or die(mysql_error() . "insert bug EQ");
	}
}
echo "Fin du script.";
//		echo "".json_last_error()
header("HTTP/1.1 200 OK");
?>
