<?php
/////////////////////////////////////////////////////////////
//
//  Définitions des variables
//
////////////////////////////////////////////////////////////

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$ligueId = $request->ligueId;
$saisonId = $request->saisonId;

$qVentOff = false;
if(isset($request->ventOff))
$qVentOff =  $request->ventOff;

//$ligueId = 3;
//$saisonId = 145;


//$ligueId = $_POST["ligueId"];
//$saisonId = $_POST["saisonId"];
//echo $ligueId."  t  ".$saisonId." 2 ";

require '../scriptsphp/defenvvar.php';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';

////////////////////////////////////////////////////////////
//
// 	Connections é la base de données
//
////////////////////////////////////////////////////////////

if (!isset($deSyncMatch)) {

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, "utf8");
mysqli_query($conn, "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY','')");
}

/////////////////////////////////////////////////////
//
//   Trouve ID de la ligue é partir du nom.
//
////////////////////////////////////////////////////



function trouveJoueur($joueurId, $array_joueur) {
	foreach ($array_joueur as $valeur) {
		if ($valeur['joueur_id'] == $joueurId) {
			return $valeur;
		}
	}
	return null;

}

function trouveFic($equipeId, $array_fic) {
	foreach ($array_fic as $valeur) {
		if ($valeur['equipe_id'] == $equipeId) {
			return $valeur;
		}
	}
	return null;

}

function trouveArbitre($arbitreId, $array_arb) {
	foreach ($array_arb as $valeur) {
		if ($valeur['arbitreId'] == $arbitreId) {
			return $valeur;
		}
	}
	return null;

}

function trouveMatch($matchId, $array_match) {
	foreach ($array_match as $valeur) {
		if (strcmp($valeur['matchID'], $matchId)==0) {
			return $valeur;
		}
	}
	$nVec = Array();
	$nVec['cpt']=$matchId;
	return $nVec;

}

function contientMatch($matchId, $array_match) {
	foreach ($array_match as $valeur) {
		if (strcmp($valeur['matchID'], $matchId)==0) {
			return true;
		}
	}

	return false;

}

function trouveGardiens($match, $array_gard) {
	foreach ($array_gard as $valeur) {
		if ($valeur['match'] == $match) {
			$gardiens = array();
			if(isset( $valeur['gDom'])){
				$gardiens['dom'] = $valeur['gDom'];
			}
			if(isset( $valeur['gVis'])){
				$gardiens['vis'] = $valeur['gVis'];
			}
			return $gardiens;
		}
	}
	return null;

}

///////////////////////////////////
//
//	 Lister les équipes de la ligue
//
///////////////////////////////////

//	if(!isset($ligueId))
//	$ligueId=3;
//	$ligueId = trouveIDParNomLigue($monGet);

	
if($saisonId=="null"||$saisonId=="undefined"||$saisonId=="")// Sp�cifie par la saison
	{
		$rfSaison = mysqli_query($conn,"SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ligueId}' ORDER BY premierMatch DESC LIMIT 0,1")
or die(mysqli_error($conn)." Select saisonId"); 

while($rangeeSaison=mysqli_fetch_array($rfSaison))
{
	$saisonId= $rangeeSaison['saisonId'];
	
}
		
		}

$qMatchSai = "SELECT premierMatch,dernierMatch FROM TableSaison where saisonId ='{$saisonId}'";

$prSaison = mysqli_query($conn,$qMatchSai) or die(mysqli_error($conn) . " dans " . $qMatchSai);
$rang= mysqli_fetch_row($prSaison);
//echo mysqli_num_rows($prSaison);
$premierMatch = $rang[0];
//echo $premierMatch;
$dernierMatch = $rang[1];
//echo $dernierMatch;
//echo $premierMatch." ".$dernierMatch;

$liste = array();
$Ine = 0;

unset($resultEvent);
unset($rangeeEv);
$lesMatchs = array();

////  Sélectionne tous les débuts de matchs dans une saison.
/*$qGros = "SELECT TableEquipe.*, Ligue.*, TableMatch.*,TableEvenement0.*
 FROM TableMatch
 JOIN Ligue
 ON TableMatch.ligueRef=Ligue.ID_Ligue
 JOIN TableEvenement0
 ON TableMatch.matchIdRef=TableEvenement0.match_event_id
 LEFT JOIN TableEquipe
 ON TableMatch.eq_dom=TableEquipe.equipe_id
 WHERE TableEvenement0.code='10'
 AND TableEvenement0.souscode='0'
 AND TableMatch.ligueRef='{$ligueId}'
 AND TableMatch.date>'{$premierMatch}'
 AND TableMatch.date<'{$dernierMatch}'
 ";*/
 
 $qJoueurs = "SELECT TableJoueur.*
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								JOIN TableEvenement0
									ON TableMatch.matchIdRef=TableEvenement0.match_event_id
								JOIN TableJoueur
									ON TableEvenement0.joueur_event_ref=TableJoueur.joueur_id
								WHERE TableEvenement0.code='3' 
									AND TableMatch.ligueRef='{$ligueId}'
									AND TableMatch.date>'{$premierMatch}'
									AND TableMatch.date<'{$dernierMatch}'
								GROUP BY TableJoueur.joueur_id
									";

 
 
$qGros = "SELECT TableEquipe.*, Ligue.*, TableMatch.*
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
								WHERE  TableMatch.ligueRef='{$ligueId}'
									AND TableMatch.date>'{$premierMatch}'
									AND TableMatch.date<'{$dernierMatch}' 
									";
									
									mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
								
$resultEvent = mysqli_query($conn, $qGros) or die(mysqli_error($conn) . " dans " . $qGros);
//echo "/qG".$qGros;
$qVent = "SELECT TableEquipe.equipe_id,
				Ligue.ID_Ligue, 
				TableMatch.*,
				TableEvenement0.joueur_event_ref,
				TableEvenement0.equipe_event_id,
				TableEvenement0.match_event_id,
				TableEvenement0.code,
				TableEvenement0.chrono 
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
								JOIN TableEvenement0
									ON TableMatch.matchIdRef=TableEvenement0.match_event_id
								WHERE  TableMatch.ligueRef='{$ligueId}'
									AND TableMatch.date>'{$premierMatch}'
									AND TableMatch.date<'{$dernierMatch}' 
									AND (code = 0 OR code = 10 OR code=11)
									ORDER BY TableMatch.match_id ASC, TableEvenement0.chrono ASC
									";
									
if($qVentOff==false){
		
	
$resultJoueurs = mysqli_query($conn, $qJoueurs) or die(mysqli_error($conn) . " dans " . $qJoueurs);
//echo "/qJ".$qJoueurs;
while ($row = mysqli_fetch_assoc($resultJoueurs)) {
	$joueurs_array[] = $row;
	// Inside while loop
}
								
																
									
$resultVent = mysqli_query($conn, $qVent) or die(mysqli_error($conn) . " dans " . $qVent);
//echo "/qV".$qVent;

$cptM =0;
while ($row = mysqli_fetch_assoc($resultVent)) {
	//$cptM = count($lesMatchs);
	if ($cptM == 0) {
		$lesMatchs[0] = array();
		$lesMatchs[0]['matchID'] = $row['match_event_id'];
		$lesMatchs[0]['ventDom'] = Array();
		$lesMatchs[0]['ventVis'] = Array();
			array_push($lesMatchs[0]['ventDom'],0);
			array_push($lesMatchs[0]['ventVis'],0);
			$cptM++;
	} else {
		if (strcmp($lesMatchs[$cptM - 1]['matchID'] , $row['match_event_id'])!=0) {
			$lesMatchs[$cptM] = Array();
			$lesMatchs[$cptM]['matchID'] = $row['match_event_id'];
			$lesMatchs[$cptM]['ventDom'] = Array();
			$lesMatchs[$cptM]['ventVis'] = Array();
			array_push($lesMatchs[$cptM]['ventDom'],0);
			array_push($lesMatchs[$cptM]['ventVis'],0);
			$cptM++;
		}
	}
	if ($row['code'] == 11) {

		array_push($lesMatchs[$cptM-1]['ventDom'],0);
		array_push($lesMatchs[$cptM-1]['ventVis'],0);
	} else if ($row['code'] == 0) {
		if ($row['eq_dom']== $row['equipe_event_id']) {
			$lesMatchs[$cptM - 1]['ventDom'][count($lesMatchs[$cptM - 1]['ventDom'])-1]++;
			if(array_sum($lesMatchs[$cptM - 1]['ventDom'])==$row['score_vis']+1){
				$tmpJ = trouveJoueur($row['joueur_event_ref'], $joueurs_array);
				$lesMatchs[$cptM - 1]['butGagnant'] = $tmpJ != null ? $tmpJ['NomJoueur']: "";
			}
		} else {
			$lesMatchs[$cptM - 1]['ventVis'][count($lesMatchs[$cptM - 1]['ventVis'])-1]++;
			if(array_sum($lesMatchs[$cptM - 1]['ventVis'])==$row['score_dom']+1){
				$tmpJ = trouveJoueur($row['joueur_event_ref'], $joueurs_array);
				$lesMatchs[$cptM - 1]['butGagnant'] = $tmpJ != null ? $tmpJ['NomJoueur']: "";
			}
		}

	}

}
}


$qFic = "SELECT *
													FROM TableEquipe
													WHERE 1";

$resultFic = mysqli_query($conn, $qFic) or die(mysqli_error($conn) . " dans " . $qFic);

while ($row = mysqli_fetch_assoc($resultFic)) {
	$fic_array[] = $row;
	// Inside while loop
}

$qArb = "SELECT *
													FROM TableArbitre
													JOIN TableUser
														ON (TableArbitre.userId=TableUser.noCompte)
													WHERE 1";
$rArb = mysqli_query($conn, $qArb) or die(mysqli_error($conn) . " dans " . $qArb);

while ($row = mysqli_fetch_assoc($rArb)) {
	$arb_array[] = $row;
	// Inside while loop
}


if($qVentOff==false){

$qGard = "SELECT TableMatch.*,TableEvenement0.* 
								FROM TableMatch 
								JOIN TableEvenement0
									ON TableMatch.matchIdRef=TableEvenement0.match_event_id
								WHERE TableEvenement0.code='3' 
									AND TableEvenement0.souscode='5'
									AND TableMatch.ligueRef='{$ligueId}'
									AND TableMatch.date>'{$premierMatch}'
									AND TableMatch.date<'{$dernierMatch}'
									";
$resultGard = mysqli_query($conn, $qGard) or die(mysqli_error($conn) . " dans " . $qGard);

$array_gard = array();
while ($rGard = mysqli_fetch_assoc($resultGard)) {
	$matchGard = array();
	$matchGard['match'] = $rGard['matchIdRef'];
	$matchDeja = false;
	$cle = -1;

	for ($a = 0; $a < count($array_gard); $a++) {
		if ($array_gard[$a]['match'] == $matchGard['match']) {
			$cle = $a;
		}
	}

	if ($rGard['eq_dom'] == $rGard['equipe_event_id']) {
		$matchGard['gDom'] = $rGard['joueur_event_ref'];

		if ($cle > -1) {
			$array_gard[$cle]['gDom'] = $rGard['joueur_event_ref'];
		} else {array_push($array_gard, $matchGard);
		}

	}
	if ($rGard['eq_vis'] == $rGard['equipe_event_id']) {
		$matchGard['gVis'] = $rGard['joueur_event_ref'];

		if ($cle > -1) {
			$array_gard[$cle]['gVis'] = $rGard['joueur_event_ref'];
		} else {array_push($array_gard, $matchGard);
		}

	}

}
}

$Ieq = 0;
$mesFic = array();
$IF = 0;
$mesMatchs = array();

////////////////////////////////////////
//
//  	Début du gros While, ou on boucle sur les équipes
//
$gGagne = "";
$gPerd = "";

while ($rangeeEv = mysqli_fetch_array($resultEvent)) {

	$trouveDom = 0;
	$trouveVis = 0;
	for ($a = 0; $a < $IF; $a++) {
		if ($mesFic[$a]['eqId'] == $rangeeEv['eq_dom']) {
			$trouveDom = $mesFic[$a]['ficId'];
		}
		if ($mesFic[$a]['eqId'] == $rangeeEv['eq_vis']) {
			$trouveVis = $mesFic[$a]['ficId'];
		}
	}

	if ($trouveDom == 0) {
		$mesFic[$IF] = array();
		$mesFic[$IF]['eqId'] = $rangeeEv['eq_dom'];
		$mE = trouveFic($rangeeEv['eq_dom'], $fic_array);

		$trouveDom = $mE['ficId'];
		$mesFic[$IF]['ficId'] = $trouveDom;
		$IF++;
	}
	if ($trouveVis == 0) {
		$mesFic[$IF] = array();
		$mesFic[$IF]['eqId'] = $rangeeEv['eq_vis'];

		$mE = trouveFic($rangeeEv['eq_vis'], $fic_array);
		$trouveVis = $mE['ficId'];
		$mesFic[$IF]['ficId'] = $trouveVis;
		$IF++;

	}

	$tmpCV = str_replace('"{', '{', $rangeeEv['cleValeur']);
	$tmpCV = str_replace('}"', '}', $tmpCV);

	//echo $tmpCV."/.-";
	if (strlen($tmpCV) > 0) {
		try {$tmpJS = json_decode($tmpCV, true);
		} catch(Exception $e) {echo $e -> getMessage();
		}
	}//json_decode(stripcslashes($tmpCV));}
	else {$tmpJS = $tmpCV;
	}
	$mJS = array();

	$arbitre = "";
	if (isset($mJS['arbitreId'])) {

		$mA = trouveArbitre($mJS['arbitreId']);
		if (!is_null($mA)) {
			$arbitre = $mA['prenom'] + " " + $mA['nom'];
		}
	}

	///////////   Gardiens gagnants, perdant.

	if($qVentOff==false){
	
	$gardiens = trouveGardiens($rangeeEv['matchIdRef'], $array_gard);
	$gGagne = "";
	$gPerd = "";
	 
	if(!is_null($joueurs_array)&& !is_null($gardiens) ){
	if(isset($gardiens['dom'])){
	$gD = trouveJoueur($gardiens['dom'], $joueurs_array);
	if(!is_null($gD)){
	$gardDom = $gD['NomJoueur'];}}

	if(isset($gardiens['vis'])){
	
	$gV = trouveJoueur($gardiens['vis'], $joueurs_array);	
	if(!is_null($gV)){
		$gardVis = $gV['NomJoueur'];}
	}



	if ($rangeeEv['score_dom'] > $rangeeEv['score_vis']) {$gGagne = $gardDom;
		$gPerd = $gardVis;
	}
	if ($rangeeEv['score_dom'] < $rangeeEv['score_vis']) {$gGagne = $gardVis;
		$gPerd = $gardDom;
	}

	
	}
}


	$butGagne = "";


	unset($matchID);
	$matchID = $rangeeEv['matchIdRef'];

	
	$JS2 = Array();

	$mMatch = trouveMatch($matchID, $lesMatchs);

	$JS2['matchId'] = $rangeeEv['match_id'];
	$JS2['matchID'] = $matchID;
	$JS2['date'] = $rangeeEv['date'];
	$JS2['mavId'] = $rangeeEv['mavId'];
	$mE = trouveFic($rangeeEv['eq_dom'], $fic_array);
	$JS2['eqDom'] = $mE['nom_equipe'];
	$JS2['coulDom'] = $mE['couleur1'];
	$JS2['eqDomId'] = $rangeeEv['eq_dom'];
	$JS2['alDom'] = json_decode($rangeeEv['alignementDom'],false);
	$JS2['alVis'] = json_decode($rangeeEv['alignementVis'],false);
	$JS2['gDom'] = $rangeeEv['gardienDom'];
	$JS2['gVis'] = $rangeeEv['gardienVis'];
	$JS2['ficIdDom'] = $trouveDom;
	$JS2['equipeScoreDom'] = $rangeeEv['score_dom'];
	$JS2['equipeScoreVis'] = $rangeeEv['score_vis'];
	$JS2['statut'] = $rangeeEv['statut'];
	$JS2['eqVisId'] = $rangeeEv['eq_vis'];
	$JS2['ficIdVis'] = $trouveVis;
	$JS2['arbitre'] = $arbitre;
	$JS2['butGagnant'] = isset($mMatch['butGagnant'])? $mMatch['butGagnant']: "";
	$JS2['gGagnant'] = $gGagne;
	$JS2['gPerdant'] = $gPerd;
	$JS2['ventDom'] =  isset($mMatch['ventDom'])? $mMatch['ventDom']:"";
	$JS2['ventVis'] =  isset($mMatch['ventVis'])? $mMatch['ventVis']:"";
	//	$JS2['ventVis'] =$ventVis;
	$JS2['cleValeur'] = $tmpJS;
	$mE = trouveFic($rangeeEv['eq_vis'], $fic_array);
	$JS2['coulVis'] = $mE['couleur1'];
	$JS2['eqVis'] = $mE['nom_equipe'];
	$JS2['arenaId'] = $rangeeEv['arenaId'];
	
	
	
	if(contientMatch($matchID, $mesMatchs)==false){
array_push($mesMatchs, $JS2);		
	}
	
//	array_push($liste, $matchID);
	//	}
$Ieq++;
}
	
$Ine++;

$jsRetour= array();
$jsRetour['matchs'] = $mesMatchs;
echo json_encode($jsRetour);
mysqli_close($conn);

?>

