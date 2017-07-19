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

$equipeId = $_POST['equipeId'];
$taJoueurs = $_POST['taJoueurs'];
$lesJoueurs = explode(PHP_EOL, $taJoueurs);


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

//if (!mysql_connect($db_host, $db_user, $db_pwd))
 //   die("Can't connect to database");

//if (!mysql_select_db($database))
 //   {
//    	echo "<h1>Database: {$database}</h1>";
 //   	echo "<h1>Table: {$table}</h1>";
  //  	die("Can't select database");
//	}
	
		mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

//////////////////////////////////
//
//	Les queries
//



foreach($lesJoueurs as $nomJoueur)
{
	$requeteJou = "INSERT INTO TableJoueur (NomJoueur, NumeroJoueur, ficIdPortrait, dernierMAJ) ".
"VALUES ('$nomJoueur', '0', 95, NOW())";

	$retour3 = mysqli_query($conn,$requeteJou) or die("Erreur: ".$requeteJou.mysqli_error);
		$last_id = mysqli_insert_id($conn);
	
$requeteAbon = "INSERT INTO abonJoueurEquipe (equipeId, joueurId, permission, debutAbon, finAbon) ".
"VALUES ('$equipeId', '$last_id', 30, NOW(), '2030-01-01')";

$retour2 = mysqli_query($conn,$requeteAbon) or die("Erreur: ".$requeteAbon.mysqli_error);


}
	
?>
