<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_910';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';


$matchId = $_POST['matchId'];


////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}
	mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

		$rPun = mysql_query("SELECT *
								FROM commandeBoard 
								WHERE matchId='$matchId'
								")or die(mysql_error()); 

	
	$mesPuns=Array();

	while($rangPuns=mysql_fetch_assoc($rPun))
	{
		array_push($mesPuns,$rangPuns);
		
	}
	//////////////////////////////////////////////////
	//
	// 	�crit JSON

	


	
echo json_encode($mesPuns);
	

?>
