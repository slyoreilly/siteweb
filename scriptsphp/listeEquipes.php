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


$ligueId = $_POST["ligueId"];
$saisonId = $_POST["saisonId"];

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

	
if($saisonId=="null"||$saisonId=="undefined")// Sp�cifie par la saison
	{$saisonId = trouveSaisonActiveDeLigueId($ligueId);}
		
	$retPost= Array();
	$retPost['ligueId']=$ligueId;
	$retPost['saisons']=Array();
	$JSONstring = "{\"ligueId\": \"".$ligueId."\",";
	
	$Is=0;
	
//$resultSaison = mysql_query("SELECT * FROM TableSaison WHERE saisonId = '{$saisonId}'")
$resultSaison = mysql_query("SELECT * FROM TableSaison WHERE ligueRef = '{$ligueId}'")
or die(mysql_error());  
while($rangeeSaison=mysql_fetch_array($resultSaison))
{
	
	
	
	$premierMatch = $rangeeSaison['premierMatch'];
	$dernierMatch = $rangeeSaison['dernierMatch'];
	$typeSaison = $rangeeSaison['typeSaison'];
$retPost['saisons'][$Is]=Array();
$retPost['saisons'][$Is]['pm']=$premierMatch;
$retPost['saisons'][$Is]['dm']=$dernierMatch;
$retPost['saisons'][$Is]['type']=$typeSaison;
$retPost['saisons'][$Is]['nom']= $rangeeSaison['nom'];
$retPost['saisons'][$Is]['saisonId']= $rangeeSaison['saisonId'];
$retPost['saisons'][$Is]['structureDivision']= json_decode($rangeeSaison['structureDivision']);

    ///  On ne popule de stats que la saison entière.
if($saisonId==$rangeeSaison['saisonId']){

$resultEquipe = mysql_query("SELECT TableEquipe.*,abonEquipeLigue.* FROM TableEquipe 
								JOIN abonEquipeLigue
									ON (equipeId=equipe_id) 
									WHERE ligueId = '{$ligueId}'
									AND (abonEquipeLigue.finAbon>='{$premierMatch}'
											AND abonEquipeLigue.debutAbon<='{$dernierMatch}')
											AND abonEquipeLigue.permission<31")
								or die(mysql_error());  
$equipe=array();
$Ie=0;
while($rangeeEquipe=mysql_fetch_array($resultEquipe))
{
	$equipe[$Ie]['id']=$rangeeEquipe['equipe_id'];
	$equipe[$Ie]['nom']=$rangeeEquipe['nom_equipe'];
	$equipe[$Ie]['ville']=$rangeeEquipe['ville'];
	$equipe[$Ie]['ficId']=$rangeeEquipe['ficId'];
	$equipe[$Ie]['logo']=$rangeeEquipe['logo'];
	$equipe[$Ie]['couleur1']=$rangeeEquipe['couleur1'];
	$equipe[$Ie]['cleValeur']=$rangeeEquipe['cleValeur'];
	$Ie++;
}


}





$I0=0;





//$JSONstring .="\"equipes\": ".json_encode($equipe)."}";

//echo json_encode($Sommaire);




$retPost['saisons'][$Is]['equipe']=$equipe;

$Is++;
}
echo json_encode($retPost);
	


?>
