<?php


/////////////////////////////////////////////////////////////
//
//  Définitions des variables
// 
////////////////////////////////////////////////////////////

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';

$equipeId = $_GET["equipeId"];
$ligueId = $_GET["ligueId"];
$nomEquipe = $_GET["nomEquipe"];


////////////////////////////////////////////////////////////
//
// 	Connections à la base de données
//
////////////////////////////////////////////////////////////

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}
if(empty($nomEquipe))
{$equipeId = $_GET["equipeId"];
	$resultEquipe = mysql_query("SELECT * FROM TableEquipe WHERE equipe_id = '{$equipeId}'")
	or die(mysql_error());  
}
else {
	$resultEquipe = mysql_query("SELECT * FROM {$tableEquipe} where nom_equipe='$nomEquipe' and ligue_equipe_ref='$ligueId'")
or die(mysql_error());  	
}
	// Retrieve all the data from la table

$liste=array();
$Ieq =0;

$JSONstring = "{";
$JSONstring .="\"Equipes\": [";

while($rangeeEv=mysql_fetch_array($resultEquipe))
{
$JSONstring .= "{\"equipeId\": \"".$rangeeEv['equipe_id']."\",";
$JSONstring .="\"nomEquipe\": \"".$rangeeEv['nom_equipe']."\",";
$JSONstring .="\"ficId\": \"".$rangeeEv['ficId']."\",";
$JSONstring .="\"logo\": \"".$rangeeEv['logo']."\",";
$JSONstring .="\"ligueId\": \"".$rangeeEv['ligue_equipe_ref']."\"},";

}

	$JSONstring = substr($JSONstring, 0,-1);
	$JSONstring .= "]}";
	
//echo json_encode($Sommaire);
echo $JSONstring;
	


?>
