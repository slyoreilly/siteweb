<?php
require '../scriptsphp/defenvvar.php';

$username = $_POST['username'];
$debutSync = $_POST['debutSync'];
$finSync = $_POST['finSync'];
$heure = $_POST['heure'];
$avanceServeur = time() * 1000 - $heure;
$rSServ = $debutSync + $avanceServeur;
$fSServ = $finSync + $avanceServeur;
//rrs2: Plus recent sync du telephone corrigé àa l'heure serveur et boosté de 1 s.
unset($chronoRetour);
unset($resultChrono);
unset($rangeeChrono);

$rrs2 = $rSServ + 1000;
$fss2 = $fSServ + 1000;

////////////////////  Sortir tous les matchs récents de l'utilisateur.
//
///					Pour les évènements, voir plus bas.


$chronoRetour = array();
$matchRetour = array();
$qMatch = "SELECT chrono,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,TableMatch.arenaId,TableMatch.date FROM TableEvenement0 
			INNER JOIN TableMatch
				ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)
			INNER JOIN AbonnementLigue
				ON (TableMatch.ligueRef=AbonnementLigue.ligueid)
			INNER JOIN TableUser
				ON (AbonnementLigue.userid=TableUser.noCompte)
			WHERE TableEvenement0.chrono>$rrs2
			AND  TableEvenement0.chrono<$fss2
				AND TableEvenement0.code=0 
				AND TableUser.username='{$username}'
					GROUP BY match_event_id
					";
					//	mysql_query("SET SQL_BIG_SELECTS=1");
				//	$resultMatch = mysql_query($qMatch) or die(mysql_error() . $qMatch);
					
$qClips = "SELECT chrono,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,TableMatch.date FROM Clips 
			INNER JOIN TableMatch
				ON (Clips.matchId=TableMatch.matchIdRef)
			INNER JOIN AbonnementLigue
				ON (TableMatch.ligueRef=AbonnementLigue.ligueid)
			INNER JOIN TableUser
				ON (AbonnementLigue.userid=TableUser.noCompte)
			WHERE Clips.chrono>$rrs2 
			AND Clips.chrono>$fss2
				AND TableUser.username='{$username}'
					GROUP BY Clips.matchId";
					
					$qTot = $qMatch." ORDER BY chrono DESC";
					
						mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
					$resultMatch = mysqli_query($conn,$qTot) or die(mysqli_error($conn) . $qTot);					
					
$qLigues = "SELECT * FROM Ligue
			INNER JOIN AbonnementLigue
				ON (Ligue.ID_Ligue=AbonnementLigue.ligueid)
			INNER JOIN TableUser
				ON (AbonnementLigue.userid=TableUser.noCompte)
			WHERE  TableUser.username='{$username}'";
					
				
					
						mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
					$resultLigues = mysqli_query($conn,$qLigues) or die(mysqli_error($conn) . $qLigues);							

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


while ($rangeeMatch = mysqli_fetch_array($resultMatch)){// && !$trouve) {
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
	if(!in_array($rangeeMatch['eq_dom'], $Equipes))
	{		$qSelEqDom="SELECT nom_equipe From TableEquipe
						WHERE equipe_id='{$rangeeMatch['eq_dom']}'";
		$resEqDom = mysqli_query($conn,$qSelEqDom) or die(mysqli_error($conn) . $qSelEqDom);
		$rang=mysqli_data_seek($resEqDom,0);
		$Equipes[$IE]['nomEquipe']=$rang[0];
		$Equipes[$IE]['equipeId']=$rangeeMatch['eq_dom'];
		$IE++;
	}
	if(!in_array($rangeeMatch['eq_vis'], $Equipes))
	{		$qSelEqVis="SELECT nom_equipe From TableEquipe
						WHERE equipe_id='{$rangeeMatch['eq_vis']}'";
		$resEqVis = mysqli_query($conn,$qSelEqVis) or die(mysqli_error($conn) . $qSelEqVis);
		$rang=mysqli_data_seek($resEqVis,0);
		$Equipes[$IE]['nomEquipe']=$rang[0];	
		$Equipes[$IE]['equipeId']=$rangeeMatch['eq_vis'];
		$IE++;
	}
	if(!in_array($rangeeMatch['match_id'], $matchs))
	{
		$qSelArena="SELECT nomArena,nomGlace From TableArena
						WHERE arenaId='{$rangeeMatch['arenaId']}'";
		$resArena = mysqli_query($conn,$qSelArena) or die(mysqli_error($conn) . $qSelArena);
		$rang=mysqli_data_seek($resArena,0);
		
		$matchs[$IM2]['arena']=$rang[0]." / ".$rang[1];	
		$matchs[$IM2]['arenaId']=$rangeeMatch['arenaId'];	
		$matchs[$IM2]['ligueId']=$rangeeMatch['ligueRef'];
		$matchs[$IM2]['eqDom']=$rangeeMatch['eq_dom'];
		$matchs[$IM2]['eqVis']=$rangeeMatch['eq_vis'];
		$matchs[$IM2]['matchId']=$rangeeMatch['match_id'];
		$matchs[$IM2]['nom']=$rangeeMatch['matchIdRef'];
		$matchs[$IM2]['date']=$rangeeMatch['date'];
				$IM2++;
	}
					$qChron = "SELECT event_id,chrono,match_event_id,TableMatch.match_id,TableMatch.ligueRef,TableMatch.eq_dom,TableMatch.eq_vis FROM TableEvenement0 
							INNER JOIN TableMatch
								ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)
			
							WHERE TableEvenement0.chrono>$rrs2 
							AND TableEvenement0.chrono<$fss2 
								AND TableEvenement0.code=0 
								AND match_event_id='{$matchOK[$IM]}' ORDER BY TableEvenement0.chrono ASC LIMIT 0,20";
		mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
								
		$resultChrono = mysqli_query($conn,$qChron) or die(mysqli_error($conn) . $qChron);
		while ($rangeeChrono = mysqli_fetch_array($resultChrono)) {
			//			array_push($chronoRetour, $rangeeChrono['chrono']);
			$chronoRetour[$IC] = $rangeeChrono[1];
			$matchRetour[$IC] = $rangeeChrono[2];
			$video[$IC]['match_id'] = $rangeeChrono['match_id'];
			$video[$IC]['reference'] = $rangeeChrono['event_id'];
			$video[$IC]['type'] = 0;
			$video[$IC]['chrono'] = $rangeeChrono['chrono'];
/*			$video[$IC]['ligueId'] = $rangeeChrono['ligueRef'];
			$video[$IC]['eqDom'] = $rangeeChrono['eq_dom'];
			$video[$IC]['eqVis'] = $rangeeChrono['eq_vis'];*/
			$IC++;
		}
			$qClips = "SELECT clipId,chrono,Clips.matchId,TableMatch.match_id,TableMatch.ligueRef,TableMatch.eq_dom,TableMatch.eq_vis FROM Clips 
							INNER JOIN TableMatch
								ON (Clips.matchId=TableMatch.matchIdRef)
			
							WHERE Clips.chrono>$rrs2 
							AND  Clips.chrono>$fss2 
								AND Clips.matchId='{$matchOK[$IM]}' ORDER BY Clips.chrono ASC LIMIT 0,20";
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
/*			$video[$IC]['ligueId'] = $rangeeChrono['ligueRef'];
			$video[$IC]['eqDom'] = $rangeeChrono['eq_dom'];
			$video[$IC]['eqVis'] = $rangeeChrono['eq_vis'];*/
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
while(count($nouveauxIndices)< count($chronoRetour)){
	$min = 15000000000000000;
	$ind = 0;
for($a=0 ;$a< count($chronoRetour);$a++ )
{

	if(intval($chronoRetour[$a])<$min && !in_array($a, $nouveauxIndices)){
			$min = intval($chronoRetour[$a]);
			$ind = $a;
		}
}
//	$nouveauxTemps[count($nouveauxTemps)]=$min;
	$repSite['match'][count($nouveauxIndices)] = $matchRetour[$ind];
	$repSite['chronoBut'][count($nouveauxIndices)] = $chronoRetour[$ind];
	$repSite['video'][count($nouveauxIndices)] = $video[$ind];
	$nouveauxIndices[count($nouveauxIndices)]=$ind;
}


$repSite['heure'] = time();
//$repSite['chronoBut'] = $chronoRetour;
//$repSite['match'] = $matchRetour;
//$repSite['video'] = $video;
//$repSite['ligues'] = $Ligues;
$repSite['ligues'] =$vecLigues;
$repSite['equipes'] = $Equipes;
$repSite['matchs'] = $matchs;

//echo json_encode($Sommaire);
echo json_encode($repSite);
//mysqli_close($conn);
//	header("HTTP/1.1 200 OK");

?>	