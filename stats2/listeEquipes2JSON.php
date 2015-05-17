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

/////////////////////////////////////////////////////////////
//
//

function trouveNomJoueurParID($ID){ 

$resultJoueur = mysql_query("SELECT * FROM TableJoueur WHERE joueur_id = '{$ID}'")
or die(mysql_error());  
if($rangeeJoueur=mysql_fetch_array($resultJoueur))
		  return ($rangeeJoueur['NomJoueur']); 
else { return ("Anonyme"); }
} 


/////////////////////////////////////////////////////////////
//
//

function parseMatchID($ID){
	 
$monMatch['date'] = substr($ID,0,stripos($ID,'_'));
$longueur = strlen($monMatch['date']);
$monMatch['dom'] = substr($ID,stripos($ID,'_')+1,stripos(substr($ID,$longueur+2),'_')+1);
$monMatch['vis'] = substr($ID,strripos($ID,'_')+1);
return $monMatch;
} 


/////////////////////////////////////////////////////
	//
//   Trouve ID de l'equipe à partir du nom.
//
////////////////////////////////////////////////////

function trouveIDParNomEquipe($nomEq)
{
$resultEquipe = mysql_query("SELECT * FROM {$tableEquipe}")
or die(mysql_error());  
while($rangeeEquipe=mysql_fetch_array($resultEquipe))
{
		if(!strcmp($rangeeEquipe['nom_equipe'],$nomEq))
	{$equipeID =$rangeeEquipe['equipe_id'];// Ce sont de INT
	}
}
return $equipeID;
}
/////////////////////////////////////////////////////
	//
//   Trouve Nom de l'equipe à partir du ID.
//
////////////////////////////////////////////////////

function trouveNomParIDEquipe($IEq)
{
//$resultEquipe2 = mysql_query("SELECT * FROM {$tableEquipe}")
//or die(mysql_error());  
//while($rangeeEquipe2=mysql_fetch_array($resultEquipe2))
//{
			
//		if($rangeeEquipe2['equipe_id']==$IEq)
//	{
	//$NomEquipe =$rangeeEquipe2['nom_equipe'];// Ce sont de INT
//	}
//}
$NomEquipe ="1";
return $NomEquipe;
}

//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////



$equipeId = $_GET["equipeId"];
$ligueId = $_GET["LigueID"];
	
	// Retrieve all the data from la table
if(is_numeric($equipeId))
{
$resultEvent = mysql_query("SELECT * FROM {$tableEquipe} WHERE ligue_equipe_ref = '{$ligueId}' AND equipe_id = '{$equipeId}' ")
or die(mysql_error());  
}
else {
$resultEvent = mysql_query("SELECT * FROM {$tableEquipe} WHERE ligue_equipe_ref = '{$ligueId}'")
or die(mysql_error());  	
}

$liste=array();
$Ieq =0;

$JSONstring = "{";
$JSONstring .="\"equipes\": [";

while($rangeeEv=mysql_fetch_array($resultEvent))
{
$JSONstring .= "{\"nomEquipe\": \"".$rangeeEv['nom_equipe']."\",";
$JSONstring .="\"equipeId\": \"".$rangeeEv['nom_equipe']."\",";
$JSONstring .="\"logo\": \"".$rangeeEv['logo']."\",";	
}

	$JSONstring = substr($JSONstring, 0,-1);
	$JSONstring .= "]}";
	
//echo json_encode($Sommaire);
echo $JSONstring;
	


?>
