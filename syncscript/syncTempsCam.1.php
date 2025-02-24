<?php
require '../scriptsphp/defenvvar.php';

$username = $_POST['username'];
$recentSync = $_POST['recentSync'];
$dernierMatch = $_POST['nomMatch'];
//$arena = $_POST['arenaId'];
$heure = $_POST['heure'];
//$maxSec = $_POST['timeout'];
$avanceServeur = time() * 1000 - $heure;
$rSServ = $recentSync + $avanceServeur;
//rrs2: Plus recent sync du telephone corrigé àa l'heure serveur et boosté de 1 s.


unset($chronoRetour);
unset($resultChrono);
unset($rangeeChrono);

$rrs2 = $rSServ;// + 1000;
//$cpt=0;

////////////////////  Sortir tous les matchs récents de l'utilisateur.
//
///					Pour les évènements, voir plus bas.




$chronoRetour = array();
$matchRetour = array();



$qMatch = "SELECT * FROM (
    SELECT TableEvenement0.event_id, chrono,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,
		TableMatch.arenaId,TableMatch.date, '0' as 'type', TableEvenement0.equipe_event_Id as scoringEnd , TableEvenement0.code,TableEvenement0.souscode
	FROM TableEvenement0 
	INNER JOIN TableMatch
				ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)
			INNER JOIN AbonnementLigue
				ON (TableMatch.ligueRef=AbonnementLigue.ligueid)
			INNER JOIN TableUser
				ON (AbonnementLigue.userid=TableUser.noCompte)
			WHERE TableEvenement0.chrono>$rrs2 
				AND (TableEvenement0.code=0 OR TableEvenement0.code=2)
				AND TableUser.username='{$username}'
UNION ALL
	SELECT Clips.clipId, chrono,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,
		TableMatch.arenaId,TableMatch.date, '5' as 'type', Clips.scoringEnd, '5' as 'code', '0' as 'souscode' 
	FROM Clips 
	INNER JOIN TableMatch
				ON (Clips.matchId=TableMatch.matchIdRef)
			INNER JOIN AbonnementLigue
				ON (TableMatch.ligueRef=AbonnementLigue.ligueid)
			INNER JOIN TableUser
				ON (AbonnementLigue.userid=TableUser.noCompte)
			WHERE Clips.chrono>$rrs2 
				AND TableUser.username='{$username}' ) t


ORDER BY  matchIdRef, chrono";
  

					
						mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
					$resultMatchs = mysqli_query($conn,$qMatch) or die(mysqli_error($conn) . $qMatch);					



					
										
$IM = 0;
$IC = 0;
$IE =0;// conpteur équipe
$IM2 =0;// compteur match
$trouve = false;
$matchs = array();

$matchPeriode = array();
			$video=array();

while ($rangeeMatch=mysqli_fetch_array($resultMatchs)){// && !$trouve) {

				$mGameIndex = 0;
					$trouveMatch=false;
				foreach($matchPeriode as $key=>$unMatch){
					if($unMatch['match_id']==$rangeeMatch['match_id']){
						$mGameIndex=$key;
						$trouveMatch=true;
					}
				}
				if(!$trouveMatch){
					$nouveauMatch=Array();
					$nouveauMatch['match_id']=$rangeeMatch['match_id'];
					$nouveauMatch['arenaId']=$rangeeMatch['arenaId'];
					$nouveauMatch['ligueId']=$rangeeMatch['ligueRef'];
					$nouveauMatch['eqDom']=$rangeeMatch['eq_dom'];
					$nouveauMatch['eqVis']=$rangeeMatch['eq_vis'];
					$nouveauMatch['nom']=$rangeeMatch['matchIdRef'];
					$nouveauMatch['date']=$rangeeMatch['date'];
					$nouveauMatch['periodes']= Array();
					$nouveauMatch['videos']= Array();
					$mGameIndex=array_push($matchPeriode,$nouveauMatch)-1;
				}




			switch($rangeeMatch['type']){

				case 0:
					switch($rangeeMatch['code']){
					case 0:
					$mVideo= array();
					$mVideo['match_id'] = $rangeeMatch['match_id'];
					$mVideo['reference'] = $rangeeMatch['event_id'];
					$mVideo['type'] = 0;
					$mVideo['chrono'] = $rangeeMatch['chrono'];
					$mVideo['ligueId'] = $rangeeMatch['ligueRef'];
					$mVideo['equipe'] = $rangeeMatch['scoringEnd'];
					array_push($matchPeriode[$mGameIndex]['videos'],$mVideo);
					break;

					case 11:
					$unePeriode = Array();
					$unePeriode['noPer'] = $rangeeMatch['souscode'];
					$unePeriode['chronoDeb'] = $rangeeMatch['chrono'];
					array_push($matchPeriode[$mGameIndex]['periodes'],$unePeriode);
					break;


					}
					case 2:
						$mVideo= array();
						$mVideo['match_id'] = $rangeeMatch['match_id'];
						$mVideo['reference'] = $rangeeMatch['event_id'];
						$mVideo['type'] = 2;
						$mVideo['chrono'] = $rangeeMatch['chrono'];
						$mVideo['ligueId'] = $rangeeMatch['ligueRef'];
						$mVideo['equipe'] = $rangeeMatch['scoringEnd'];
						array_push($matchPeriode[$mGameIndex]['videos'],$mVideo);
						break;

				break;






				case 5:			
				$mVideo= array();
				$mVideo['match_id'] = $rangeeMatch['match_id'];
				$mVideo['reference'] = $rangeeMatch['event_id'];
				$mVideo['type'] = 5;
				$mVideo['chrono'] = $rangeeMatch['chrono'];
				$mVideo['ligueId'] = $rangeeMatch['ligueRef'];
				$mVideo['equipe'] = $rangeeMatch['scoringEnd'];
				array_push($matchPeriode[$mGameIndex]['videos'],$mVideo);

				break;
			}


		
//	}
	$IM++;

}


foreach($matchPeriode as $key=>$unMatch){
	
	$qSelArena="SELECT * From TableArena
	WHERE arenaId='{$unMatch['arenaId']}'";
	$resArena = mysqli_query($conn,$qSelArena) or die(mysqli_error($conn) . $qSelEqVis);
	while ($rangeeArena = mysqli_fetch_array($resArena)){
	$nomArena =$rangeeArena['nomArena'];					
	$nomGlace = $rangeeArena['nomGlace'];	
	}
	$abons = Array();
	$Ia=0;
	$qSelAbons="SELECT telId, abonAppareilMatch.role  From abonAppareilMatch
		WHERE matchId='{$unMatch['match_id']}'";
	$resAbons = mysqli_query($conn,$qSelAbons) or die(mysqli_error($conn) . $qSelAbons);
	while ($rangeeAbons = mysqli_fetch_array($resAbons)){
	$abons[$Ia]['role'] =$rangeeAbons['role'];					
	$abons[$Ia]['telId'] =$telId = $rangeeAbons['telId'];	
	$Ia++;
	}



	$unMatch['arena']=$nomArena." / ".$nomGlace;	
	$unMatch['abons']=$abons;
	
}


				
					
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
			$video=array();
//////////////////////////////     Pour les matchs qu'on vient de trouver
//////////////////////////////		On classe quelques infos: équipes, ligues, ....
//////////////////////////////		On trouve les buts marqués


//$nouveauxTemps = array();
$nouveauxIndices = array();
$repSite = array();

	$repSite['match']=array();
	$repSite['chronoBut']=array();
	$repSite['video']=array();



$repSite['heure'] = time();

$repSite['matchPeriode'] =$matchPeriode;
$repSite['ligues'] =$vecLigues;


//echo json_encode($Sommaire);
echo json_encode($repSite);
mysqli_close($conn);
//	header("HTTP/1.1 200 OK");

?>	