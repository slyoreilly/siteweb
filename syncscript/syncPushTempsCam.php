<?php
require '../scriptsphp/defenvvar.php';

$username = $_POST['username'];
$recentSync = $_POST['recentSync'];
//$dernierMatch = $_POST['nomMatch'];
$arena = $_POST['arenaId'];
$heure = $_POST['heure'];
$maxSec = $_POST['timeout'];
$avanceServeur = time() * 1000 - $heure;
$rSServ = $recentSync + $avanceServeur;
//rrs2: Plus recent sync du telephone corrigé àa l'heure serveur et boosté de 1 s.



unset($chronoRetour);
unset($resultChrono);
unset($rangeeChrono);

$rrs2 = $rSServ + 1000;
$cpt=0;


//echo $rrs2."  ";
//echo $maxSec."  ";
//echo $heure."  ";

////////////////////  Sortir tous les matchs récents de l'utilisateur.
//
///					Pour les évènements, voir plus bas.

do {

//echo " -- ". $cpt." -- ";

$chronoRetour = array();
$matchRetour = array();



$qMatch = "SELECT * FROM (
    SELECT chrono,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,TableMatch.arenaId,TableMatch.date FROM TableEvenement0 
			INNER JOIN TableMatch
				ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)

			WHERE ((TableEvenement0.chrono>$rrs2 
				AND (TableEvenement0.code=0 OR TableEvenement0.code=10))
				)
				AND TableMatch.arenaId='{$arena}'
UNION ALL
SELECT chrono,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,TableMatch.arenaId,TableMatch.date FROM Clips 
			INNER JOIN TableMatch
				ON (Clips.matchId=TableMatch.matchIdRef)
			WHERE Clips.chrono>$rrs2 
				AND TableMatch.arenaId='{$arena}') t 

GROUP BY matchIdRef
ORDER BY chrono";
  
 
 
 /*"SELECT chrono,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,TableMatch.arenaId,TableMatch.date FROM TableEvenement0 
			INNER JOIN TableMatch
				ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)
			
			WHERE ((TableEvenement0.chrono>$rrs2 
				AND (TableEvenement0.code=0 OR TableEvenement0.code=10))
				)
				AND TableMatch.arenaId='{$arena}'
					GROUP BY matchIdRef
					";*/
					//	mysql_query("SET SQL_BIG_SELECTS=1");
				//	$resultMatch = mysql_query($qMatch) or die(mysql_error() . $qMatch);
					
/*$qClips = "SELECT chrono,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,TableMatch.arenaId,TableMatch.date FROM Clips 
			INNER JOIN TableMatch
				ON (Clips.matchId=TableMatch.matchIdRef)
			WHERE Clips.chrono>$rrs2 
				AND TableMatch.arenaId='{$arena}'
					GROUP BY Clips.matchId";
					
					$qTot = $qMatch." ORDER BY chrono DESC";*/
					
						mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
					$resultMatchs = mysqli_query($conn,$qMatch) or die(mysqli_error($conn) . $qMatch);					
	
	//$qTotClips = $qClips." ORDER BY chrono DESC";
					
	//					mysql_query("SET SQL_BIG_SELECTS=1");
				//	$resultMatchC = mysql_query($qTotClips) or die(mysql_error() . $qTotClips);	
	
	//$resultMatch= array_merge($resultMatch0,$resultMatchC);
	/*$consoMatchs = array();
	while ($rangeeMatchA = mysql_fetch_array($resultMatch0)){// && !$trouve) {
	array_push($consoMatchs,$rangeeMatchA );}
	while ($rangeeMatchC = mysql_fetch_array($resultMatchC)){// && !$trouve) {
	array_push($consoMatchs,$rangeeMatchC );}*/
	
	
					
$qLigues = "SELECT * FROM Ligue
			INNER JOIN AbonnementLigue
				ON (Ligue.ID_Ligue=AbonnementLigue.ligueid)
			INNER JOIN TableUser
				ON (AbonnementLigue.userid=TableUser.noCompte)
			WHERE  TableUser.username='{$username}'";
					
				
					
						mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
					$resultLigues = mysqli_query($conn, $qLigues) or die(mysqli_error($conn) . $qLigues);							



					$vecLigues = array();
					$IL2=0;
while($r = mysqli_fetch_array($resultLigues)) {

    $vecLigues[]=$r;
    $vecLigues[$IL2]['ligueId']=$r['ID_Ligue'];
    $vecLigues[$IL2]['nomLigue']=$r['Nom_Ligue'];
    $IL2++;
    }
					
										
$IM = 0;
$IC = 0;
$IL =0;// compteur ligue
$IE =0;// conpteur équipe
$IM2 =0;// compteur match
$trouve = false;


$matchOK = array();
$Ligues = array();
$Equipes = array();
$matchs = array();

$matchPeriode = array();
			$video=array();
//////////////////////////////     Pour les matchs qu'on vient de trouver
//////////////////////////////		On classe quelques infos: équipes, ligues, ....
//////////////////////////////		On trouve les buts marqués

while ($rangeeMatch=mysqli_fetch_array($resultMatchs)){// && !$trouve) {
//while ($IM<count($consoMatchs)){// && !$trouve) {
//		$rangeeMatch=$consoMatchs[$IM];
	$matchOK[$IM] = $rangeeMatch[1];
	//if(!in_array($rangeeMatch['ligueRef'], $Ligues))
	//{
	//	$qSelLigue="SELECT * From Ligue
	//					WHERE ID_Ligue='{$rangeeMatch['ligueRef']}'";
	//	$resLigue = mysql_query($qSelLigue) or die(mysql_error() . $qSelLigue);
	//	$nomLigue = mysql_result($resLigue, 0,'Nom_Ligue');				
	//	$Ligues[$IL]['ligueId']=$rangeeMatch['ligueRef'];
	//	$Ligues[$IL]['nomLigue']=$nomLigue;
	//	$IL++;
				
	//}
if(!in_array($rangeeMatch['match_id'], $matchs))
	{
		$qSelArena="SELECT * From TableArena
						WHERE arenaId='{$rangeeMatch['arenaId']}'";
		$resArena = mysqli_query($conn,$qSelArena) or die(mysqli_error($conn) . $qSelEqVis);
		while ($rangeeArena = mysqli_fetch_array($resArena)){
			$nomArena =$rangeeArena['nomArena'];					
			$nomGlace = $rangeeArena['nomGlace'];	
		}
		$abons = Array();
		$Ia=0;
		$qSelAbons="SELECT telId, abonAppareilMatch.role  From abonAppareilMatch
						WHERE matchId='{$rangeeMatch['match_id']}'";
		$resAbons = mysqli_query($conn,$qSelAbons) or die(mysqli_error($conn) . $qSelAbons);
		while ($rangeeAbons = mysqli_fetch_array($resAbons)){
			$abons[$Ia]['role'] =$rangeeAbons['role'];					
			$abons[$Ia]['telId'] =$telId = $rangeeAbons['telId'];	
			$Ia++;
		}



		$matchs[$IM2]['arena']=$nomArena." / ".$nomGlace;	
		$matchs[$IM2]['abons']=$abons;
		$matchs[$IM2]['arenaId']=$rangeeMatch['arenaId'];	
		$matchs[$IM2]['ligueId']=$rangeeMatch['ligueRef'];
		$matchs[$IM2]['eqDom']=$rangeeMatch['eq_dom'];
		$matchs[$IM2]['eqVis']=$rangeeMatch['eq_vis'];
		$matchs[$IM2]['matchId']=$rangeeMatch['match_id'];
		$matchs[$IM2]['nom']=$rangeeMatch['matchIdRef'];
		$matchs[$IM2]['date']=$rangeeMatch['date'];
				$IM2++;
	}
					$qChron = "SELECT event_id,chrono,match_event_id,equipe_event_id, code, souscode, TableMatch.match_id,TableMatch.ligueRef,TableMatch.eq_dom,TableMatch.eq_vis FROM TableEvenement0 
							INNER JOIN TableMatch
								ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)
			
							WHERE TableEvenement0.chrono>$rrs2 
								AND (TableEvenement0.code=0 OR TableEvenement0.code=11)
								AND match_event_id='{$matchOK[$IM]}' ORDER BY TableEvenement0.chrono ASC LIMIT 0,50";
		mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
								
		$resultChrono = mysqli_query($conn,$qChron) or die(mysqli_error($conn) . $qChron);
		while ($rangeeChrono = mysqli_fetch_array($resultChrono)) {
			switch($rangeeChrono['code']){
			case 0:
			//			array_push($chronoRetour, $rangeeChrono['chrono']);
				$chronoRetour[$IC] = $rangeeChrono['chrono'];
				$matchRetour[$IC] = $rangeeChrono['match_event_id'];
				$video[$IC]['match_id'] = $rangeeChrono['match_id'];
				$video[$IC]['reference'] = $rangeeChrono['event_id'];
				$video[$IC]['type'] = 0;
				$video[$IC]['chrono'] = $rangeeChrono['chrono'];
				$video[$IC]['ligueId'] = $rangeeChrono['ligueRef'];
				$video[$IC]['equipe'] = $rangeeChrono['equipe_event_id'];			
				$IC++;
			break;
			case 11:
				$unePeriode = Array();
				$unePeriode['noPer'] = $rangeeChrono['souscode'];
				$unePeriode['chronoDeb'] = $rangeeChrono['chrono'];
				
				$trouveMatch=false;
				foreach($matchPeriode as $unMatch){
					if($unMatch['match_id']==$rangeeChrono['match_id']){
						array_push($unMatch['periodes'],$unePeriode);
						$trouveMatch=true;
					}
				}
				if(!$trouveMatch){
					$nouveauMatch=Array();
					$nouveauMatch['match_id']=$rangeeChrono['match_id'];
					$nouveauMatch['periodes']= Array();
					array_push($nouveauMatch['periodes'],$unePeriode);
					array_push($matchPeriode,$nouveauMatch);
				}
			break;
			}

		}
			$qClips = "SELECT clipId,chrono,Clips.matchId,Clips.scoringEnd,TableMatch.match_id,TableMatch.ligueRef,TableMatch.eq_dom,TableMatch.eq_vis FROM Clips 
							INNER JOIN TableMatch
								ON (Clips.matchId=TableMatch.matchIdRef)
			
							WHERE Clips.chrono>$rrs2 
								AND Clips.matchId='{$matchOK[$IM]}' ORDER BY Clips.chrono ASC LIMIT 0,50";
		mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
								
		$resultClips = mysqli_query($conn,$qClips) or die(mysqli_error($conn) . $qClips);
		while ($rangeeClips = mysqli_fetch_array($resultClips)) {
			//			array_push($chronoRetour, $rangeeChrono['chrono']);
			$chronoRetour[$IC] = $rangeeClips[1];
			$matchRetour[$IC] = $rangeeClips[2];
			$video[$IC]['match_id'] = $rangeeClips['match_id'];
			$video[$IC]['reference'] = $rangeeClips['clipId'];
			$video[$IC]['type'] = 5;
			$video[$IC]['chrono'] = $rangeeClips['chrono'];
			$video[$IC]['ligueId'] = $rangeeClips['ligueRef'];
			$video[$IC]['equipe'] = $rangeeClips['scoringEnd'];
			$IC++;
		}
		
//	}
	$IM++;

}
//$nouveauxTemps = array();
$nouveauxIndices = array();
$repSite = array();

	$repSite['match']=array();
	$repSite['chronoBut']=array();
	$repSite['video']=array();
	
	
//	Pour Merger les 2 tableaux (clips et buts) ainsi que les quelques vecteurs en ordre chronologique
while(count($nouveauxIndices)< count($chronoRetour)){
	$min = 15000000000000000;
	$ind = 0;
for($a=0 ;$a< count($chronoRetour);$a++ )
{

	if(floatval($chronoRetour[$a])<$min && !in_array($a, $nouveauxIndices)){
			$min = floatval($chronoRetour[$a]);
			$ind = $a;
		}
}

	// Équivalent d'un array_push
//	$nouveauxTemps[count($nouveauxTemps)]=$min;
	$repSite['match'][count($nouveauxIndices)] = $matchRetour[$ind];
	$repSite['chronoBut'][count($nouveauxIndices)] = $chronoRetour[$ind];
	$repSite['video'][count($nouveauxIndices)] = $video[$ind];
	$nouveauxIndices[count($nouveauxIndices)]=$ind;
}


$repSite['heure'] = time();
$repSite['chronoBut'] = $chronoRetour;
$repSite['match'] = $matchRetour;
$repSite['video'] = $video;
$repSite['matchPeriode'] = $matchPeriode;
//$repSite['ligues'] = $Ligues;
//$repSite['ligues'] =$vecLigues;
//$repSite['equipes'] = $Equipes;
$repSite['matchs'] = $matchs;
//$repSite['info']=$resultMatch;
if($IC==0){
	$mSleep = sleep(5);
	flush();
	$cpt = $cpt+5000;

	
}

//$repSite['IC'] =$IC;
//$repSite['cpt'] =$cpt;
//echo " -!- ". $maxSec." -!- ";

$comp =$maxSec-30000;
}
 while (($IC==0)&&($cpt<$comp));


//echo json_encode($Sommaire);
echo json_encode($repSite);
//mysqli_close($conn);
//	header("HTTP/1.1 200 OK");

?>	