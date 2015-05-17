<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$username = $_POST['username'];
$password = $_POST['password'];
$matchjson = stripslashes($_POST['matchjson']);

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	
	
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

	
	
//$json=json_decode("'".$matchjson."'");
//$leMatch = utf8_decode(json_decode($matchjson, true));
$leMatch = json_decode($matchjson, true);
	$intEquipe = $leMatch['nomEquipe'];
	$intLigue = $leMatch['ligue_id'];
	$intLogo = $leMatch['logo'];
	$vieuId = $leMatch['vieuId'];

	
$retour = mysql_query("INSERT INTO TableEquipe (nom_equipe,logo,ficId, equipeActive,dernierMAJ) 
VALUES ('{$intEquipe}', '{$intLogo}', 16, 1, NOW())");	
//	mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono, match_event_id) 
//VALUES ( 'test	Match2', 'testMatch2', 'testMatch2', 'testMatch2','testMatch2')");	
	
	$resultNouveau = mysql_query("SELECT equipe_id FROM TableEquipe WHERE nom_equipe='{$intEquipe}'  ORDER BY equipe_id DESC")
				or die(mysql_error());  
	
	$nId = mysql_fetch_row($resultNouveau);
		$JSONstring = 	"{\"vieuId\": \"".$vieuId."\",";
		$JSONstring .= 	"\"nouveauId\": \"".$nId[0]."\"}";
	
//$retour = mysql_query("INSERT INTO abonJoueurEquipe (joueurId, equipeId, permission, debutAbon, finAbon) 
//VALUES ('{$nId[0]}', '{$intEquipe}',30, NOW(),'2050-01-01')");	
$retour = mysql_query("INSERT INTO abonEquipeLigue (equipeId, ligueId, permission, debutAbon, finAbon) 
VALUES ('{$nId[0]}', '{$intLigue}',30, NOW(),'2050-01-01')");	

	
		echo $JSONstring;
//		echo "".json_last_error();
			header("HTTP/1.1 200 OK");

?>
