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
$tableAbon = 'AbonnementLigue';

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

function trouveIDParUsername($uname)
{
$resultEquipe = mysql_query("SELECT * FROM TableUser")
or die(mysql_error());  
while($rangeeEquipe=mysql_fetch_array($resultEquipe))
{
		if(!strcmp($rangeeEquipe['username'],$uname))
	{$equipeID =$rangeeEquipe['ref_id'];// Ce sont de INT
	}
}
return $equipeID;
}


//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////
	
$uname = $_GET["userId"];
	// Retrieve all the data from la table
$userId = trouveIDParUsername($uname);

$resultEvent = mysql_query("SELECT * FROM AbonnementLigue WHERE userid='{$userId}'")
or die(mysql_error());  	

$liste=array();
$Ieq =0;

$JSONstring = "{";
$JSONstring .="\"abonnements\": [";

while($rangeeEv=mysql_fetch_array($resultEvent))
{
	$Ieq=1;
$JSONstring .= "{\"type\": \"".$rangeeEv['type']."\",";
$JSONstring .="\"ligueId\": \"".$rangeeEv['ligueid']."\"},";
}
if($Ieq>0)
	$JSONstring = substr($JSONstring, 0,-1);
	$JSONstring .= "]}";
	
//echo json_encode($Sommaire);
echo $JSONstring;
	


?>
