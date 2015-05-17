<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
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
$tableMatch = 'TableMatch';


$ligueId = $_GET["ligueId"];
$saisonId = $_GET["saisonId"];

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

function trouveSaisonActiveDeLigueId($ID){ 
$rfSaison = mysql_query("SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ID}' ORDER BY premierMatch DESC")
or die(mysql_error()." Select saisonId");  
//echo mysql_result($rfSaison, 0)."\n";  
//$tmp= (mysql_fetch_array($rfSaison));
//echo $tmp['saisonId']."\n";  
 return (mysql_result($rfSaison, 0)); 
} 

//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////

	
if($saisonId=="null")// Sp�cifie par la saison
	{$saisonId = trouveSaisonActiveDeLigueId($ligueId);}
		
	
	
$resultSaison = mysql_query("SELECT * FROM TableSaison WHERE saisonId = '{$saisonId}'")
or die(mysql_error());  
while($rangeeSaison=mysql_fetch_array($resultSaison))
{
	$premierMatch = $rangeeSaison['premierMatch'];
	$dernierMatch = $rangeeSaison['dernierMatch'];
	$typeSaison = $rangeeSaison['typeSaison'];
}

$resultEquipe = mysql_query("SELECT TableEquipe.*,abonEquipeLigue.* FROM TableEquipe 
								JOIN abonEquipeLigue
									ON (equipeId=equipe_id) 
									WHERE ligueId = '{$ligueId}'
									AND (abonEquipeLigue.finAbon>='{$premierMatch}'
											AND abonEquipeLigue.debutAbon<='{$dernierMatch}')")
								or die(mysql_error());  
$equipe=array();
$Ie=0;
while($rangeeEquipe=mysql_fetch_array($resultEquipe))
{
	$equipe[$Ie]['id']=$rangeeEquipe['equipe_id'];
	$equipe[$Ie]['nom']=$rangeeEquipe['nom_equipe'];
	$equipe[$Ie]['vicDom']=0;
	$equipe[$Ie]['defDom']=0;
	$equipe[$Ie]['nulDom']=0;
	$equipe[$Ie]['vicVis']=0;
	$equipe[$Ie]['defVis']=0;
	$equipe[$Ie]['nulVis']=0;
	$equipe[$Ie]['bp']=0;
	$equipe[$Ie]['bc']=0;
	$Ie++;
}


$Ie=0;
$statsEq = array();
while($Ie < count($equipe)) {

unset($resultMatch);
unset($rangeeMatch);
		
$resultMatch = mysql_query("SELECT * FROM $tableMatch WHERE eq_dom = '{$equipe[$Ie]['id']}'")
or die(mysql_error());  

while($rangeeMatch=mysql_fetch_array($resultMatch))
{
	if($rangeeMatch['date']>=$premierMatch&&$rangeeMatch['date']<=$dernierMatch)
	{
	if($rangeeMatch['score_dom']>$rangeeMatch['score_vis'])
	$equipe[$Ie]['vicDom']++;
	if($rangeeMatch['score_dom']<$rangeeMatch['score_vis'])
	$equipe[$Ie]['defDom']++;
	if($rangeeMatch['score_dom']==$rangeeMatch['score_vis'])
	$equipe[$Ie]['nulDom']++;
	$equipe[$Ie]['bp']+=$rangeeMatch['score_dom'];
	$equipe[$Ie]['bc']+=$rangeeMatch['score_vis'];
	}
}

unset($resultMatch);
unset($rangeeMatch);

$resultMatch = mysql_query("SELECT * FROM $tableMatch WHERE eq_vis = '{$equipe[$Ie]['id']}'")
or die(mysql_error());  

while($rangeeMatch=mysql_fetch_array($resultMatch))
{
	if($rangeeMatch['date']>=$premierMatch&&$rangeeMatch['date']<=$dernierMatch)
	{
	
	if($rangeeMatch['score_dom']<$rangeeMatch['score_vis'])
	$equipe[$Ie]['vicVis']++;
	if($rangeeMatch['score_dom']>$rangeeMatch['score_vis'])
	$equipe[$Ie]['defVis']++;
	if($rangeeMatch['score_dom']==$rangeeMatch['score_vis'])
	$equipe[$Ie]['nulVis']++;
	$equipe[$Ie]['bc']+=$rangeeMatch['score_dom'];
	$equipe[$Ie]['bp']+=$rangeeMatch['score_vis'];
	}
	}
$Ie++;
	
}







$I0=0;
$JSONstring = "{\"ligueId\": \"".$ligueId."\",";
$JSONstring .="\"equipes\": [";

//foreach($equipe as $Ieq)
//{
while($I0<count($equipe))
	{
				$JSONstring .= json_encode($equipe[$I0]).",";
				$I0++;
				
	}
	
							if(!strcmp(",", substr($JSONstring,-1)))// Pour �viter les vides;
							{$JSONstring = substr($JSONstring, 0,-1);}
							$JSONstring .= "]}";
	
//echo json_encode($Sommaire);
echo $JSONstring;
	


?>
