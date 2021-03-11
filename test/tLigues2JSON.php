<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////
require '../scriptsphp/defenvvar.php';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';

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
//   Trouve ID de l'equipe � partir du nom.
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


//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////
//$strOut=" ";
//$strOut="Hey!!!! ";
//echo $strOut;
	
	$ligueId = $_GET["LigueID"];
$equipeId = $_GET["equipeId"];
	// Retrieve all the data from la table

if(is_numeric($ligueId))
{
$resultEvent = mysql_query("SELECT * FROM Ligue WHERE ID_Ligue = '{$ligueId}'")
or die(mysql_error());  
}
else 
{if (is_numeric($equipeId))
	{
	$resultEquipe = mysql_query("SELECT ligue_equipe_ref FROM TableEquipe WHERE equipe_id = '{$equipeId}'")
	or die(mysql_error());  
	$resultEvent = mysql_query("SELECT * FROM Ligue WHERE ID_Ligue = '".mysql_result($resultEquipe,0)."'")
	or die(mysql_error());  
	}
	else
	{
	$resultEvent = mysql_query("SELECT * FROM Ligue")
	or die(mysql_error());  	
	}}
$liste=array();
$Ieq =0;
$strOut.="Hey! ";

$JSONstring = "{";
$JSONstring .="\"Ligues\": [";

while($rangeeEv=mysql_fetch_array($resultEvent))
{
$JSONstring .= "{\"nomLigue\": \"".$rangeeEv['Nom_Ligue']."\",";
$JSONstring .="\"ligueId\": \"".$rangeeEv['ID_Ligue']."\",";
$JSONstring .="\"ficId\": \"".$rangeeEv['ficId']."\",";
$JSONstring .="\"lieu\": \"".$rangeeEv['Lieu']."\",";
$JSONstring .="\"horaire\": \"".$rangeeEv['Horaire']."\"},";

}

	$JSONstring = substr($JSONstring, 0,-1);
	$JSONstring .= "]}";
	
//echo json_encode($Sommaire);
echo $JSONstring;
//echo $strOut;
	

?>
