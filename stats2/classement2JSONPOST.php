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


//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////

	
$equipe=array();	
	
	
if($saisonId=="null"||$saisonId=="undefined"||$saisonId=="")// Sp�cifie par la saison
	{
		$rfSaison = mysqli_query($conn,"SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ligueId}' ORDER BY premierMatch DESC LIMIT 0,1")
or die(mysqli_error($conn)." Select saisonId"); 

while($rangeeSaison=mysqli_fetch_array($rfSaison))
{
	$saisonId= $rangeeSaison['saisonId'];
	
}
		
		}
		
	$retPost= array();
	$retPost['ligueId']=$ligueId;
	$retPost['saisons']=array();
	$JSONstring = "{\"ligueId\": \"".$ligueId."\",";
	
	$Is=0;
	
	$resultLigue = mysqli_query($conn,"SELECT * FROM Ligue WHERE ID_Ligue = '{$ligueId}'")
or die(mysqli_error($conn));  
$rangeeLigue=mysqli_fetch_assoc($resultLigue);
$jsonLigue = json_decode($rangeeLigue['cleValeur'],true);
$nbPunMax = 1000;
if(isset($jsonLigue['reglements']['nbPunMax'])){
$nbPunMax = $jsonLigue['reglements']['nbPunMax'];
}
	
//$resultSaison = mysql_query("SELECT * FROM TableSaison WHERE saisonId = '{$saisonId}'")
$resultSaison = mysqli_query($conn,"SELECT * FROM TableSaison WHERE ligueRef = '{$ligueId}'")
or die(mysqli_error($conn));  
while($rangeeSaison=mysqli_fetch_array($resultSaison))
{
	
	
	
	$premierMatch = $rangeeSaison['premierMatch'];
	$dernierMatch = $rangeeSaison['dernierMatch'];
	$typeSaison = $rangeeSaison['typeSaison'];
$retPost['saisons'][$Is]=array();
$retPost['saisons'][$Is]['pm']=$premierMatch;
$retPost['saisons'][$Is]['dm']=$dernierMatch;
$retPost['saisons'][$Is]['type']=$typeSaison;
$retPost['saisons'][$Is]['nom']= $rangeeSaison['nom'];
$retPost['saisons'][$Is]['saisonId']= $rangeeSaison['saisonId'];
$retPost['saisons'][$Is]['structureDivision']= json_decode($rangeeSaison['structureDivision']);

    ///  On ne popule de stats que la saison entière.
if($saisonId==$rangeeSaison['saisonId']){

$resultEquipe = mysqli_query($conn,"SELECT TableEquipe.*,abonEquipeLigue.* FROM TableEquipe 
								JOIN abonEquipeLigue
									ON (equipeId=equipe_id) 
									WHERE ligueId = '{$ligueId}'
									AND (abonEquipeLigue.finAbon>='{$premierMatch}'
											AND abonEquipeLigue.debutAbon<='{$dernierMatch}')
											AND abonEquipeLigue.permission<31")
								or die(mysqli_error($conn));  

$Ie=0;
while($rangeeEquipe=mysqli_fetch_array($resultEquipe))
{
	$equipe[$Ie]['id']=$rangeeEquipe['equipe_id'];
	$equipe[$Ie]['nom']=$rangeeEquipe['nom_equipe'];
	$equipe[$Ie]['ville']=$rangeeEquipe['ville'];
	$equipe[$Ie]['couleur1']=$rangeeEquipe['couleur1'];
	$equipe[$Ie]['vicDom']=0;
	$equipe[$Ie]['defDom']=0;
	$equipe[$Ie]['nulDom']=0;
	$equipe[$Ie]['vicVis']=0;
	$equipe[$Ie]['defVis']=0;
	$equipe[$Ie]['nulVis']=0;
	$equipe[$Ie]['defPDom']=0;
	$equipe[$Ie]['defPVis']=0;
	$equipe[$Ie]['ptsDisc']=0;
	$equipe[$Ie]['nbPun']=0;
	$equipe[$Ie]['bp']=0;
	$equipe[$Ie]['sequence']=0;
	$equipe[$Ie]['bc']=0;
	$equipe[$Ie]['dernier10']=array();
	$equipe[$Ie]['ficId']=$rangeeEquipe['ficId'];
	$equipe[$Ie]['vecRes']=array(    //   V-D-N / R-P / D-V
		array(
			array(
				0,0
				),
			array(
				0,0
				)
				),
		array(
			
			array(
				0,0
				),
			array(
				0,0
				)
				),
		array(
			
			array(
				0,0
				),
			array(
				0,0
				)
				)
		);
	$Ie++;
}


$Ie=0;
$statsEq = array();
while($Ie < count($equipe)) {

unset($resultMatch);
unset($rangeeMatch);
mysqli_query($conn,"SET SQL_BIG_SELECTS=1");

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
   
   $resultMatch = mysqli_query($conn,"
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
								
								
or die(mysqli_error($conn));  

while($rangeeMatch=mysqli_fetch_array($resultMatch))
{
	if($rangeeMatch['date']>=$premierMatch&&$rangeeMatch['date']<=$dernierMatch)
	{
		$equipe[$Ie]['nbPun']=$equipe[$Ie]['nbPun']+$rangeeMatch['punitions'];
		if($rangeeMatch['punitions']<=$nbPunMax){
			$equipe[$Ie]['ptsDisc']++;
		}	
		if($rangeeMatch['eq_dom']==$equipe[$Ie]['id'])
		{
		
			if($rangeeMatch['score_dom']>$rangeeMatch['score_vis']){
				if($rangeeMatch['sc11']>10){
				$equipe[$Ie]['vecRes'][0][1][0]++;
				}else{
				$equipe[$Ie]['vecRes'][0][0][0]++;
				}
			
				$equipe[$Ie]['vicDom']++;
				if($equipe[$Ie]['sequence']<0){
					$equipe[$Ie]['sequence']=1;
				}else{
					$equipe[$Ie]['sequence']=$equipe[$Ie]['sequence']+1;
				}
				array_push($equipe[$Ie]['dernier10'],'V');
				}
				
				
			if($rangeeMatch['score_dom']<$rangeeMatch['score_vis'])
			{
				if($rangeeMatch['sc11']>10){
				$equipe[$Ie]['defPDom']++;
				$equipe[$Ie]['vecRes'][1][1][0]++;
				}else{
				$equipe[$Ie]['defDom']++;
				$equipe[$Ie]['vecRes'][1][0][0]++;
				}
				if($equipe[$Ie]['sequence']>0){
					$equipe[$Ie]['sequence']=-1;
				}else{
					$equipe[$Ie]['sequence']=$equipe[$Ie]['sequence']-1;
				}
				
								array_push($equipe[$Ie]['dernier10'],'D');
				
			}
			if($rangeeMatch['score_dom']==$rangeeMatch['score_vis']){
				$equipe[$Ie]['nulDom']++;
				$equipe[$Ie]['vecRes'][2][0][0]++;
			$equipe[$Ie]['sequence']=0;
							array_push($equipe[$Ie]['dernier10'],'N');
			
			}
				
			$equipe[$Ie]['bp']+=$rangeeMatch['score_dom'];
			$equipe[$Ie]['bc']+=$rangeeMatch['score_vis'];
			
		}
		else{    /// L'équipe choisie est visiteur
			if($rangeeMatch['score_dom']<$rangeeMatch['score_vis']){
				if($rangeeMatch['sc11']>10){
				$equipe[$Ie]['vecRes'][0][1][1]++;
				}else{
				$equipe[$Ie]['vecRes'][0][0][1]++;
				}
			
				$equipe[$Ie]['vicVis']++;
				if($equipe[$Ie]['sequence']<0){
					$equipe[$Ie]['sequence']=1;
				}else{
					$equipe[$Ie]['sequence']=$equipe[$Ie]['sequence']+1;
				}
								array_push($equipe[$Ie]['dernier10'],'V');
			}
				
		
				
				
			if($rangeeMatch['score_dom']>$rangeeMatch['score_vis'])
			{
				if($rangeeMatch['sc11']>10){
					$equipe[$Ie]['defPVis']++;
					$equipe[$Ie]['vecRes'][1][1][1]++;
				}else{
					$equipe[$Ie]['defVis']++;
					$equipe[$Ie]['vecRes'][1][0][1]++;
				}
				if($equipe[$Ie]['sequence']>0){
					$equipe[$Ie]['sequence']=-1;
				}else{
					$equipe[$Ie]['sequence']=$equipe[$Ie]['sequence']-1;
				}
				array_push($equipe[$Ie]['dernier10'],'D');
			}
			if($rangeeMatch['score_dom']==$rangeeMatch['score_vis'])
				{$equipe[$Ie]['nulVis']++;
				$equipe[$Ie]['vecRes'][2][0][1]++;
							$equipe[$Ie]['sequence']=0;
							array_push($equipe[$Ie]['dernier10'],'N');
				}
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
	
//mysqli_close($conn);

?>
