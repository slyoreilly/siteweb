<?php
require '../scriptsphp/defenvvar.php';
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


$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
	
	
//$json=json_decode("'".$matchjson."'");
//$leMatch = utf8_decode(json_decode($matchjson, true));
$leMatch = json_decode($matchjson, true);
	$intEquipe = $leMatch['nomEquipe'];
	$intLigue = $leMatch['ligue_id'];
	$intLogo = $leMatch['logo'];
	$vieuId = $leMatch['vieuId'];

	
$retour = mysqli_query($conn,"INSERT INTO TableEquipe (nom_equipe,logo,ficId, equipeActive,dernierMAJ) 
VALUES ('{$intEquipe}', '{$intLogo}', 16, 1, NOW())") or die(mysqli_error($conn));  	
//	mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono, match_event_id) 
//VALUES ( 'test	Match2', 'testMatch2', 'testMatch2', 'testMatch2','testMatch2')");	
	
	$resultNouveau = mysqli_query($conn,"SELECT equipe_id FROM TableEquipe WHERE nom_equipe='{$intEquipe}'  ORDER BY equipe_id DESC")
				or die(mysqli_error($conn));  
	
	$nId = mysqli_data_seek($resultNouveau,0);
		$JSONstring = 	"{\"vieuId\": \"".$vieuId."\",";
		$JSONstring .= 	"\"nouveauId\": \"".$nId[0]."\"}";
	
//$retour = mysql_query("INSERT INTO abonJoueurEquipe (joueurId, equipeId, permission, debutAbon, finAbon) 
//VALUES ('{$nId[0]}', '{$intEquipe}',30, NOW(),'2050-01-01')");	
$retour = mysqli_query($conn,"INSERT INTO abonEquipeLigue (equipeId, ligueId, permission, debutAbon, finAbon) 
VALUES ('{$nId[0]}', '{$intLigue}',30, NOW(),'2050-01-01')");	

	
		echo $JSONstring;

		mysqli_close($conn);
//		echo "".json_last_error();
			header("HTTP/1.1 200 OK");

?>
