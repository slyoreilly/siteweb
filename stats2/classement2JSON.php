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
$tableMatch = 'TableMatch';


$ligueId = $_GET["ligueId"];
$saisonId = $_GET["saisonId"];

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");



/////////////////////////////////////////////////////////////
// 
//

function trouveSaisonActiveDeLigueId($ID,$conn){ 
$rfSaison = mysqli_query($conn,"SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ID}' ORDER BY premierMatch DESC")
or die(mysqli_error($conn)." Select saisonId");  
//echo mysql_result($rfSaison, 0)."\n";  
//$tmp= (mysql_fetch_array($rfSaison));
//echo $tmp['saisonId']."\n";  
return (mysqli_data_seek($rfSaison, 0))
} 

//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////

	
if($saisonId=="null")// Sp�cifie par la saison
	{$saisonId = trouveSaisonActiveDeLigueId($ligueId,$conn);}
		
	
	
$resultSaison = mysqli_query($conn,"SELECT * FROM TableSaison WHERE saisonId = '{$saisonId}'")
or die(mysqli_error($conn));  
while($rangeeSaison=mysqli_fetch_array($resultSaison))
{
	$premierMatch = $rangeeSaison['premierMatch'];
	$dernierMatch = $rangeeSaison['dernierMatch'];
	$typeSaison = $rangeeSaison['typeSaison'];
}
$requeteSelect= "SELECT TableEquipe.*,abonEquipeLigue.* FROM TableEquipe 
JOIN abonEquipeLigue
	ON (equipeId=equipe_id) 
	WHERE ligueId = '{$ligueId}'
	AND (abonEquipeLigue.finAbon>='{$premierMatch}'
			AND abonEquipeLigue.debutAbon<='{$dernierMatch}')";
$resultEquipe = mysqli_query($conn,$requeteSelect)
								or die($requeteSelect." --  ".mysqli_error($conn));  
$equipe=array();
$Ie=0;
while($rangeeEquipe=mysqli_fetch_array($resultEquipe))
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
		
$resultMatch = mysqli_query($conn,"SELECT * FROM $tableMatch WHERE eq_dom = '{$equipe[$Ie]['id']}'")
or die(mysqli_error($conn));  

while($rangeeMatch=mysqli_fetch_array($resultMatch))
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

$resultMatch = mysqli_query($conn,"SELECT * FROM $tableMatch WHERE eq_vis = '{$equipe[$Ie]['id']}'")
or die(mysqli_error($conn));  

while($rangeeMatch=mysqli_fetch_array($resultMatch))
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
