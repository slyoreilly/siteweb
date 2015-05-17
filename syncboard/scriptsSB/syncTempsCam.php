<?php
$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';

$username = $_POST['username'];
$recentSync = $_POST['recentSync'];
$dernierMatch = $_POST['nomMatch'];
$heure = $_POST['heure'];
$avanceServeur = time() * 1000 - $heure;
$rSServ = $recentSync + $avanceServeur;
//rrs2: Plus recent sync du telephone corrigé àa l'heure serveur et boosté de 1 s.

if (!mysql_connect($db_host, $db_user, $db_pwd))
	die("Can't connect to database");

if (!mysql_select_db($database)) {
	echo "<h1>Database: {$database}</h1>";
	echo "<h1>Table: {$table}</h1>";
	die("Can't select database");

}
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");
unset($chronoRetour);
unset($resultChrono);
unset($rangeeChrono);

$rrs2 = $rSServ + 1000;

$chronoRetour = array();
$matchRetour = array();
$qMatch = "SELECT TableEvenement0.chrono,match_event_id,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,TableMatch.date FROM TableEvenement0 
			INNER JOIN TableMatch
				ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)
			INNER JOIN AbonnementLigue
				ON (TableMatch.ligueRef=AbonnementLigue.ligueid)
			INNER JOIN TableUser
				ON (AbonnementLigue.userid=TableUser.noCompte)
			WHERE TableUser.username='{$username}' AND
			TableEvenement0.chrono>$rrs2 
				AND TableEvenement0.code=0 
					GROUP BY match_event_id
					ORDER BY TableEvenement0.chrono DESC";
$qMatchClips = "SELECT Clips.chrono,Clips.matchId,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,TableMatch.date FROM Clips 
			INNER JOIN TableMatch
				ON (Clips.matchId=TableMatch.matchIdRef)
			JOIN AbonnementLigue
				ON (TableMatch.ligueRef=AbonnementLigue.ligueid)
			JOIN TableUser
				ON (AbonnementLigue.userid=TableUser.noCompte)
			WHERE TableUser.username='{$username}' AND
				Clips.chrono>$rrs2 
					GROUP BY matchId
					ORDER BY Clips.chrono DESC";
					$resultMatch = mysql_query($qMatch) or die(mysql_error() . $qMatch);
					if( mysql_numrows($resultMatch)<1)
					{
					$resultMatch = mysql_query($qMatchClips) or die(mysql_error() . $qMatchClips);
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

while ($rangeeMatch = mysql_fetch_array($resultMatch)){// && !$trouve) {
	$matchOK[$IM] = $rangeeMatch[1];
	if(!in_array($rangeeMatch['ligueRef'], $Ligues))
	{
		$qSelLigue="SELECT * From Ligue
						WHERE ID_Ligue='{$rangeeMatch['ligueRef']}'";
		$resLigue = mysql_query($qSelLigue) or die(mysql_error() . $qSelLigue);
		$nomLigue = mysql_result($resLigue, 0,'Nom_Ligue');				
		$Ligues[$IL]['ligueId']=$rangeeMatch['ligueRef'];
		$Ligues[$IL]['nomLigue']=$nomLigue;
		$IL++;
				
	}
	if(!in_array($rangeeMatch['eq_dom'], $Equipes))
	{		$qSelEqDom="SELECT * From TableEquipe
						WHERE equipe_id='{$rangeeMatch['eq_dom']}'";
		$resEqDom = mysql_query($qSelEqDom) or die(mysql_error() . $qSelEqDom);
		$nomEqDom = mysql_result($resEqDom, 0,'nom_equipe');				
		$Equipes[$IE]['equipeId']=$rangeeMatch['eq_dom'];
		$Equipes[$IE]['nomEquipe']=$nomEqDom;
		$IE++;
	}
	if(!in_array($rangeeMatch['eq_vis'], $Equipes))
	{		$qSelEqVis="SELECT * From TableEquipe
						WHERE equipe_id='{$rangeeMatch['eq_vis']}'";
		$resEqVis = mysql_query($qSelEqVis) or die(mysql_error() . $qSelEqVis);
		$nomEqVis = mysql_result($resEqVis, 0,'nom_equipe');				
		$Equipes[$IE]['equipeId']=$rangeeMatch['eq_vis'];
		$Equipes[$IE]['nomEquipe']=$nomEqVis;
		$IE++;
	}
	if(!in_array($rangeeMatch['match_id'], $matchs))
	{
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
								AND TableEvenement0.code=0 
								AND match_event_id='{$matchOK[$IM]}' ORDER BY TableEvenement0.chrono ASC LIMIT 0,20";
								
		$resultChrono = mysql_query($qChron) or die(mysql_error() . $qChron);
		while ($rangeeChrono = mysql_fetch_array($resultChrono)) {
			//			array_push($chronoRetour, $rangeeChrono['chrono']);
			$chronoRetour[$IC] = $rangeeChrono[1];
			$matchRetour[$IC] = $rangeeChrono[2];
			$video[$IC]['match_id'] = $rangeeChrono['match_id'];
			$video[$IC]['chrono'] = $rangeeChrono['chrono'];
/*			$video[$IC]['ligueId'] = $rangeeChrono['ligueRef'];
			$video[$IC]['eqDom'] = $rangeeChrono['eq_dom'];
			$video[$IC]['eqVis'] = $rangeeChrono['eq_vis'];*/
			
			/////	Section Clips
		$IC++;
		}						
			
					$qClips = "
					SELECT clipId,chrono,matchId,TableMatch.match_id,TableMatch.ligueRef,TableMatch.eq_dom,TableMatch.eq_vis FROM Clips
							INNER JOIN TableMatch
								ON (Clips.matchId=TableMatch.matchIdRef)
			
								WHERE Clips.chrono>$rrs2 
									AND matchId='{$matchOK[$IM]}' ORDER BY Clips.chrono ASC LIMIT 0,20";
		
								
		$resClips = mysql_query($qClips) or die(mysql_error() . $qClips);
		while ($rangeeClips = mysql_fetch_array($resClips)) {
			//			array_push($ClipsRetour, $rangeeClips['Clips']);
			$chronoRetour[$IC] = $rangeeClips[1];
			$matchRetour[$IC] = $rangeeClips[2];
			$video[$IC]['match_id'] = $rangeeClips['match_id'];
			$video[$IC]['chrono'] = $rangeeClips['chrono'];
						
			
			
			
			
			
			
			
			$IC++;
		}
//	}
	$IM++;

}
//  matchs: Metadonnées des matchs
//  video: Liste des videos à prendre
// 	match: Vecteur de noms de match
//	chronoBut: Vecteur des chrono


$repSite = array();
$repSite['heure'] = time();
$repSite['chronoBut'] = $chronoRetour;
$repSite['match'] = $matchRetour;
$repSite['video'] = $video;
$repSite['ligues'] = $Ligues;
$repSite['equipes'] = $Equipes;
$repSite['matchs'] = $matchs;

//echo json_encode($Sommaire);
echo json_encode($repSite);
//	header("HTTP/1.1 200 OK");
?>	