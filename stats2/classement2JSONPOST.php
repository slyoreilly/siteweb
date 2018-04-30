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
	
	$resultLigue = mysql_query("SELECT * FROM Ligue WHERE ID_Ligue = '{$ligueId}'")
or die(mysql_error());  
$rangeeLigue=mysql_fetch_assoc($resultLigue);
$jsonLigue = json_decode($rangeeLigue['cleValeur'],true);
$nbPunMax = $jsonLigue['reglements']['nbPunMax'];

	
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
	$equipe[$Ie]['vicDom']=0;
	$equipe[$Ie]['defDom']=0;
	$equipe[$Ie]['nulDom']=0;
	$equipe[$Ie]['vicVis']=0;
	$equipe[$Ie]['defVis']=0;
	$equipe[$Ie]['nulVis']=0;
	$equipe[$Ie]['defPDom']=0;
	$equipe[$Ie]['defPVis']=0;
	$equipe[$Ie]['ptsDisc']=0;
	$equipe[$Ie]['bp']=0;
	$equipe[$Ie]['bc']=0;
	$equipe[$Ie]['ficId']=$rangeeEquipe['ficId'];
	$Ie++;
}


$Ie=0;
$statsEq = array();
while($Ie < count($equipe)) {

unset($resultMatch);
unset($rangeeMatch);
mysql_query("SET SQL_BIG_SELECTS=1");

/*$resultMatch = mysql_query("
SELECT * 
FROM TableMatch 
INNER JOIN(
  SELECT match_event_id,code,souscode
    FROM TableEvenement0 
    WHERE code=11 OR code =10
    ORDER BY match_event_id, code DESC, souscode DESC
) AS s1 
	ON (TableMatch.matchIdRef=s1.match_event_id)
							WHERE (eq_dom = '{$equipe[$Ie]['id']}' OR  eq_vis = '{$equipe[$Ie]['id']}')
								AND statut='F' AND ligueRef='{$ligueId}'
    GROUP BY s1.match_event_id ")*/
   
   $resultMatch = mysql_query("
    SELECT * 	
FROM TableMatch 	
LEFT JOIN(	
	  SELECT match_event_id, MAX(souscode) AS sc10
	    FROM TableEvenement0 
	    WHERE code =10
    	GROUP BY match_event_id
	) AS s1 
ON (TableMatch.matchIdRef=s1.match_event_id)	
	LEFT JOIN(
	  SELECT match_event_id,MAX(souscode) AS sc11
	    FROM TableEvenement0 
	    WHERE code =11
        GROUP BY match_event_id
	) AS s2
ON (TableMatch.matchIdRef=s2.match_event_id)	
	LEFT JOIN(
	  SELECT match_event_id, COUNT(*) AS punitions
	    FROM TableEvenement0 
	    WHERE code=4 AND equipe_event_id='{$equipe[$Ie]['id']}'
	    GROUP BY match_event_id
	) AS s3
ON (TableMatch.matchIdRef=s3.match_event_id)	
WHERE (eq_dom = '{$equipe[$Ie]['id']}' OR  eq_vis = '{$equipe[$Ie]['id']}')	
	AND statut='F' AND ligueRef='{$ligueId}'")
    
								
/*
 * "
SELECT * 
FROM TableMatch 
INNER JOIN(
  SELECT match_event_id,code,souscode
    FROM TableEvenement0 
    WHERE code=11 OR code =10
    ORDER BY match_event_id, code DESC, souscode DESC
) AS s1 
	ON (TableMatch.matchIdRef=s1.match_event_id)
LEFT JOIN(
  SELECT TableEvenement0.match_event_id,TableEvenement0.code,TableEvenement0.souscode
    FROM TableEvenement0 
    WHERE code=11 OR code =10
    ORDER BY match_event_id, code DESC, souscode DESC
) AS s2
	ON (TableMatch.match_id=s2.match_event_id)
							WHERE (eq_dom = '{$equipe[$Ie]['id']}' OR  eq_vis = '{$equipe[$Ie]['id']}')
								AND statut='F' AND ligueRef='{$ligueId}'
    GROUP BY s1.match_event_id "
 * 
 * */								
								
								
or die(mysql_error());  

while($rangeeMatch=mysql_fetch_array($resultMatch))
{
	if($rangeeMatch['date']>=$premierMatch&&$rangeeMatch['date']<=$dernierMatch)
	{
		if($rangeeMatch['punitions']<=$nbPunMax){
			$equipe[$Ie]['ptsDisc']++;
		}	
		if($rangeeMatch['eq_dom']==$equipe[$Ie]['id'])
		{
		
			if($rangeeMatch['score_dom']>$rangeeMatch['score_vis'])
				$equipe[$Ie]['vicDom']++;
			if($rangeeMatch['score_dom']<$rangeeMatch['score_vis'])
			{
				if($rangeeMatch['sc11']>10){
				$equipe[$Ie]['defPDom']++;
				}else{
				$equipe[$Ie]['defDom']++;
				}
			}
			if($rangeeMatch['score_dom']==$rangeeMatch['score_vis']){
				$equipe[$Ie]['nulDom']++;}
				
			$equipe[$Ie]['bp']+=$rangeeMatch['score_dom'];
			$equipe[$Ie]['bc']+=$rangeeMatch['score_vis'];
			
		}
		else{
			if($rangeeMatch['score_dom']<$rangeeMatch['score_vis'])
				$equipe[$Ie]['vicVis']++;
			if($rangeeMatch['score_dom']>$rangeeMatch['score_vis'])
			{
				if($rangeeMatch['sc11']>10){
					$equipe[$Ie]['defPVis']++;
				}else{
					$equipe[$Ie]['defVis']++;
				}
			}
			if($rangeeMatch['score_dom']==$rangeeMatch['score_vis'])
				$equipe[$Ie]['nulVis']++;
			$equipe[$Ie]['bc']+=$rangeeMatch['score_dom'];
			$equipe[$Ie]['bp']+=$rangeeMatch['score_vis'];
		}
		
}
}
unset($resultMatch);
unset($rangeeMatch);




$Ie++;
	
}

}





$I0=0;





//$JSONstring .="\"equipes\": ".json_encode($equipe)."}";

//echo json_encode($Sommaire);




$retPost['saisons'][$Is]['equipe']=$equipe;

$Is++;
}
if($ligueId==49 || $ligueId==50 || $ligueId==51 ){
include '../scriptsphp/customCalculPoints.php';
$retPost['customClassement']=$retCC;}

echo json_encode($retPost);
	


?>
