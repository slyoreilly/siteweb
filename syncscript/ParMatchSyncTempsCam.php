<?php
require '../scriptsphp/defenvvar.php';


$nomMatch = $_POST['nomMatch'];
$matchId = $_POST['matchId'];
$heure = $_POST['heure'];
$avanceServeur = time() * 1000 - $heure;
$rSServ = $recentSync + $avanceServeur;
//rrs2: Plus recent sync du telephone corrigé àa l'heure serveur et boosté de 1 s.


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection/
if (!$conn) {
	error_log("Connection failed: " . mysqli_connect_error());
   die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");



unset($chronoRetour);
unset($resultChrono);
unset($rangeeChrono);

$rrs2 = $rSServ + 1000;

////////////////////  Définitions
//
///					Pour les évènements, voir plus bas.


$chronoRetour = array();
$matchRetour = array();
					
										
$IM = 0;
$IC = 0;
$IM2 =0;// compteur match
$trouve = false;

$matchs = array();
$video=array();
//////////////////////////////     Pour le match demandé
//////////////////////////////		On trouve les buts marqués


					$qChron = "SELECT `event_id`,`chrono`,`match_event_id`,`TableMatch`.`match_id`,`TableMatch`.`ligueRef`,`TableMatch`.`eq_dom`,`TableMatch`.`eq_vis` 
					FROM `TableEvenement0` 
					INNER JOIN TableMatch
					 ON (TableEvenement0.match_event_id=TableMatch.matchIdRef) 
					WHERE match_id = '{matchId}' AND 
					`code`=0 ORDER BY TableEvenement0.chrono ASC ";
								
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
			
							WHERE  Clips.matchId='{$nomMatch}' ORDER BY Clips.chrono ASC ";
								
		$resultClips = mysqli_query($conn,$qClips) or die(mysql_error($conn) . $qClips);
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
	
//$nouveauxTemps = array();
$nouveauxIndices = array();
$repSite = array();

	$repSite['match']=array();
	$repSite['chronoBut']=array();
	$repSite['videos']=array();
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
	$repSite['videos'][count($nouveauxIndices)] = $video[$ind];
	$nouveauxIndices[count($nouveauxIndices)]=$ind;
}


$repSite['heure'] = time();
//$repSite['chronoBut'] = $chronoRetour;
//$repSite['match'] = $matchRetour;
$repSite['videos'] = $video;
//$repSite['ligues'] = $Ligues;
$repSite['matchs'] = $matchs;

//echo json_encode($Sommaire);
echo json_encode($repSite);
mysqli_close($conn);
//	header("HTTP/1.1 200 OK");

?>	