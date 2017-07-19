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
$nomEquipe = $_POST['nom'];
$logo = $_POST['logo'];
$ligueId = $_POST['ligueId'];
$code = $_POST['code'];
$ficId = $_POST['ficId'];
$couleur1 = $_POST['couleur'];


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
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

//////////////////////////////////
//
//	Les queries
//


	if(1==$code)  // Code 1:  Cr�er une nouvelle equipe.
{
	$query_equipe = "INSERT INTO TableEquipe (nom_equipe, logo, ligue_equipe_ref,ficId,equipeActive,dernierMAJ,couleur1) ".
"VALUES ('$nomEquipe', '$logo', '$ligueId','$ficId',1,NOW(),'$couleur1')";
		
		$retour = mysql_query($query_equipe) or die("Erreur: ".$query_equipe.mysql_error);


$requeteDernId = "SELECT * FROM TableEquipe WHERE nom_equipe='$nomEquipe' ORDER BY equipe_id DESC";
$rDernId = mysql_query($requeteDernId) or die("Erreur: ".$requeteDernId.mysql_error);


$rEID = mysql_fetch_array($rDernId);



$requeteAbon = "INSERT INTO abonEquipeLigue (equipeId, ligueId, permission, debutAbon, finAbon) ".
"VALUES ('$rEID[0]', '$ligueId', 30, NOW(), '2050-01-01')";

$retour2 = mysql_query($requeteAbon) or die("Erreur: ".$requeteAbon.mysql_error);
echo "$rEID[0]";

}
	
	if(10==$code)  // Code 10:  Modifie ligue existante.
	{
//		$resultFic = mysql_query("SELECT ficId FROM TableEquipe WHERE equipe_id= '$equipeId'");
		
		
		
	$query_update = "UPDATE TableEquipe SET nom_equipe='$nomEquipe', logo='$logo', ficId='$ficId', couleur1='$couleur1', dernierMAJ=NOW() WHERE equipe_id= '$equipeId'";	
	mysql_query($query_update)or die(mysql_error()." update");	
	
	
	}

	
?>
