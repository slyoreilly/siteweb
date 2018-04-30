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

$ligueId = 3;
$saisonId = 145;

echo "<pre>";

//$ligueId = $_POST["ligueId"];
//$saisonId = $_POST["saisonId"];
//echo $ligueId."  t  ".$saisonId." 2 ";

$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';
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
}

/////////////////////////////////////////////////////////////
//
//

function trouveNomJoueurParID($ID) {

	$resultJoueur = mysqli_query($conn, "SELECT * FROM TableJoueur WHERE joueur_id = '{$ID}'") or die(mysqli_error($conn) . " dans trouveNomJoueurParID");
	if ($rangeeJoueur = mysqli_fetch_array($resultJoueur))
		return ($rangeeJoueur['NomJoueur']);
	else {
		return ("Anonyme");
	}
}

function ismidv4()//is MatchId version 4 variables
{
	$i1 = stripos($ID, '_');
	$i2 = stripos($ID, '_', $i1 + 1);
	$i3 = stripos($ID, '_', $i2 + 1);
	if ($i3 == false) {
		return false;
	} else {
		return true;
	}
}

/////////////////////////////////////////////////////////////
//
//

function parseMatchID($ID) {

	//$monMatch['date'] = str_replace('/', '-', substr($ID,0,stripos($ID,'_')));
	$i1 = stripos($ID, '_');
	$i2 = stripos($ID, '_', $i1 + 1);
	$i3 = stripos($ID, '_', $i2 + 1);
	$monMatch['date'] = substr($ID, 0, $i1);
	$i1 = stripos($ID, '_');

	$longueur = strlen($monMatch['date']);
	$monMatch['dom'] = substr($ID, $i1 + 1, $i2 - $i1 - 1);
	if ($i3 != false)
		$monMatch['vis'] = substr($ID, $i2 + 1, $i3 - $i2 - 1);
	else {$monMatch['vis'] = substr($ID, $i2 + 1);
	}
	return $monMatch;
}

/////////////////////////////////////////////////////
//
//   Trouve ID de l'equipe é partir du nom.
//
////////////////////////////////////////////////////

function trouveIDParNomEquipe($nomEq) {
	$resultEquipe = mysqli_query($conn, "SELECT * FROM TableEquipe") or die(mysqli_error($conn) . " dans trouveIDParNomEquipe");
	while ($rangeeEquipe = mysqli_fetch_array($resultEquipe)) {
		if (!strcmp($rangeeEquipe['nom_equipe'], $nomEq)) {$equipeID = $rangeeEquipe['equipe_id'];
			// Ce sont de INT
		}
	}
	return $equipeID;
}

function trouveIDParNomEqEtLigue($nomEq, $ligueId) {
	$resultEquipe = mysqli_query($conn, "SELECT * FROM TableEquipe
										JOIN abonEquipeLigue
											ON (TableEquipe.equipe_id =abonEquipeLigue.equipeId) 
										WHERE 
											abonEquipeLigue.ligueId='{$ligueId}'
										AND
											TableEquipe.nom_equipe='{$nomEq}'") or die(mysqli_error($conn) . " dans trouveIDParNomEqEtLigue");
	while ($rangeeEquipe = mysqli_fetch_array($resultEquipe)) { {
			return $rangeeEquipe['equipe_id'];
			// Ce sont de INT
		}
	}
}

/////////////////////////////////////////////////////
//
//   Trouve ID de la ligue é partir du nom.
//
////////////////////////////////////////////////////

function trouveIDParNomLigue($nomLi) {
	$resultLigue = mysqli_query($conn, "SELECT * FROM Ligue WHERE 1") or die(mysqli_error($conn) . " dans trouveIDParNomLigue");
	while ($rangeeLigue = mysqli_fetch_array($resultLigue)) {
		if (!strcmp($rangeeLigue['Nom_Ligue'], $nomLi)) {$LigueID = $rangeeLigue['ID_Ligue'];
			// Ce sont de INT
		}
	}
	return $LigueID;
}

/////////////////////////////////////////////////////
//
//   Trouve Nom de l'equipe é partir du ID.
//
////////////////////////////////////////////////////
function trouveNomParIDEquipe($IEq) {
	$resultEquipe2 = mysqli_query($conn, "SELECT * FROM TableEquipe WHERE equipe_id='{$IEq}'") or die(mysqli_error($conn) . " dans trouveNomParIDEquipe");
	while ($rangeeEquipe2 = mysqli_fetch_array($resultEquipe2)) {

		if ($rangeeEquipe2['equipe_id'] == $IEq) {
			$NomEquipe = $rangeeEquipe2['nom_equipe'];
			// Ce sont de INT
		}
	}
	return $NomEquipe;
}

/////////////////////////////////////////////////////////////
//
//

function trouveSaisonActiveDeLigueId($ID) {
	$rfSaison = mysqli_query($conn,"SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ID}' and saisonActive=1") or die(mysqli_error($conn) . " trouveSaisonActiveDeLigueId");
	return (mysqli_result($rfSaison, 0));
}

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

function trouveGardiens($match, $array_gard) {
	foreach ($array_gard as $valeur) {
		if ($valeur['match'] == $match) {
			$gardiens = array();
			$gardiens['dom'] = $valeur['gDom'];
			$gardiens['vis'] = $valeur['gVis'];
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

$qMatchSai = "SELECT premierMatch,dernierMatch FROM TableSaison where saisonId ='{$saisonId}'";

$prSaison = mysqli_query($conn,$qMatchSai) or die(mysqli_error($conn) . " dans " . $qMatchSai);
$rang= mysqli_fetch_row($prSaison);
//echo mysqli_num_rows($prSaison);
$premierMatch = $rang[0];
//echo $premierMatch;
$dernierMatch = $rang[1];
//echo $dernierMatch;
//echo $premierMatch." ".$dernierMatch;
////////////////////////////////////////
//
//  Bâtir JSON
//
///////////////////////////////////////

$JSONstring = "{";
//$JSONstring .=$aEnr[0];
$JSONstring .= "\"matchs\": [";
//$JSONstring .=$premierMatch;
//$JSONstring .=$dernierMatch;

$liste = array();
$Ine = 0;

unset($resultEvent);
unset($rangeeEv);
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
 
 $qJoueurs = "SELECT Ligue.*, TableMatch.*,TableEvenement0.*,TableJoueur.*
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
$resultJoueurs = mysqli_query($conn, $qJoueurs) or die(mysqli_error($conn) . " dans " . $qJoueurs);
//echo "/qJ".$qJoueurs;
while ($row = mysqli_fetch_assoc($resultJoueurs)) {
	$joueurs_array[] = $row;
	// Inside while loop
}
 
 
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
$resultVent = mysqli_query($conn, $qVent) or die(mysqli_error($conn) . " dans " . $qVent);
//echo "/qV".$qVent;
$lesMatchs = Array();
$cptM =0;
while ($row = mysqli_fetch_assoc($resultVent)) {
	//$cptM = count($lesMatchs);
	if ($cptM == 0) {
		$lesMatchs[0] = Array();
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
				$lesMatchs[$cptM - 1]['butGagnant'] =$tmpJ['NomJoueur'];
			}
		} else {
			$lesMatchs[$cptM - 1]['ventVis'][count($lesMatchs[$cptM - 1]['ventVis'])-1]++;
			if(array_sum($lesMatchs[$cptM - 1]['ventVis'])==$row['score_dom']+1){
				$tmpJ = trouveJoueur($row['joueur_event_ref'], $joueurs_array);
				$lesMatchs[$cptM - 1]['butGagnant'] =$tmpJ['NomJoueur'];
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

$Ieq = 0;
$mesFic = array();
$IF = 0;
$mesMatchs = array();
/*$JSONstring .= $ligueId;
 $JSONstring .= $premierMatch;
 $JSONstring .= $dernierMatch;
 */
////////////////////////////////////////
//
//  	Début du gros While, ou on boucle sur les équipes
//

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

	$gardiens = trouveGardiens($rangeeEv['matchIdRef'], $array_gard);

	$gD = trouveJoueur($gardiens['dom'], $joueurs_array);
	$gardDom = $gD['NomJoueur'];

	$gV = trouveJoueur($gardiens['vis'], $joueurs_array);
	$gardVis = $gV['NomJoueur'];
	$gGagne = "";
	$gPerd = "";

	if ($rangeeEv['score_dom'] > $rangeeEv['score_vis']) {$gGagne = $gardDom;
		$gPerd = $gardVis;
	}
	if ($rangeeEv['score_dom'] < $rangeeEv['score_vis']) {$gGagne = $gardVis;
		$gPerd = $gardDom;
	}
	$butGagne = "";
	/*
	if ($rangeeEv['score_dom'] > $rangeeEv['score_vis']) {
		$qBG = "SELECT TableJoueur.NomJoueur
													FROM TableEvenement0
													JOIN TableJoueur
														ON (TableJoueur.joueur_id=TableEvenement0.joueur_event_ref)
													WHERE 
														match_event_id='{$rangeeEv['matchIdRef']}'
														AND code='0'
														AND equipe_event_id='{$rangeeEv['eq_dom']}'
														ORDER BY `TableEvenement0`.`chrono` ASC
														LIMIT {$rangeeEv['score_vis']} , 1";

	}

	if ($rangeeEv['score_dom'] < $rangeeEv['score_vis']) {
		$qBG = "SELECT TableJoueur.NomJoueur
													FROM TableEvenement0
													JOIN TableJoueur
														ON (TableJoueur.joueur_id=TableEvenement0.joueur_event_ref)
													WHERE 
														match_event_id='{$rangeeEv['matchIdRef']}'
														AND code='0'
														AND equipe_event_id='{$rangeeEv['eq_vis']}'
														ORDER BY `TableEvenement0`.`chrono` ASC
														LIMIT {$rangeeEv['score_dom']} , 1";
	}
	//			echo "/////".$qBG;
	if ($rangeeEv['score_dom'] != $rangeeEv['score_vis'])// S'il n'y a pas d'égalité
	{
		$rButGagne = mysqli_query($conn, $qBG) or die(mysqli_error($conn) . " dans " . $qBG);
		$tmpButGagne = mysqli_fetch_row($rButGagne);
		$butGagne = $tmpButGagne[0];
	}
*/
	/////////////////////////////////////
	//
	//
	//
	//
	////// Section ventilation des buts
	/*
	 $qVentPer = "SELECT chrono, code, souscode
	 FROM TableEvenement0

	 WHERE
	 match_event_id='{$rangeeEv['matchIdRef']}'
	 AND (code='10' OR code='11')
	 ORDER BY TableEvenement0.souscode ASC";

	 //			echo "/////".$qBG;

	 $lesPer = array();
	 $IP = 0;
	 $chronoDeb = 0;
	 $rVentPer = mysqli_query($conn, $qVentPer) or die(mysqli_error($conn) . " dans " . $qVentPer);
	 while ($rangVentPer = mysqli_fetch_array($rVentPer)) {
	 if ($rangVentPer['code'] == 11) {
	 $lesPer[$IP] = $rangVentPer['chrono'];
	 $IP++;
	 }
	 if ($rangVentPer['code'] == 10 && $rangVentPer['souscode'] == 0) {$chronoDeb = $rangVentPer['chrono'];
	 }
	 }

	 $qVentBut = "SELECT chrono,equipe_event_id
	 FROM TableEvenement0

	 WHERE
	 match_event_id='{$rangeeEv['matchIdRef']}'
	 AND (code=0)
	 ORDER BY `TableEvenement0`.`chrono` ASC";

	 $ventDom = array_fill(0, count($lesPer) + 1, 0);
	 $ventVis = array_fill(0, count($lesPer) + 1, 0);

	 $rVentBut = mysqli_query($conn, $qVentBut) or die(mysqli_error($conn) . " dans " . $qVentBut);

	 $perCour = 0;
	 while ($rangVentBut = mysqli_fetch_array($rVentBut)) {
	 if ($rangVentBut['equipe_event_id'] == $rangeeEv['eq_dom']) {
	 $trouve = false;
	 while (!$trouve) {
	 if ($perCour >= count($lesPer) - 1)// Cas de la derniere periode
	 {
	 $ventDom[$perCour]++;
	 $trouve = true;
	 } else {

	 if ($rangVentBut['chrono'] > $chronoDeb && $rangVentBut['chrono'] < $lesPer[0]) {$ventDom[0]++;
	 $trouve = true;
	 } else if ($rangVentBut['chrono'] > $lesPer[$perCour] && $rangVentBut['chrono'] < $lesPer[$perCour + 1]) {$ventDom[$perCour + 1]++;
	 $trouve = true;
	 } else {$perCour++;
	 }
	 }
	 }
	 } else if ($rangVentBut['equipe_event_id'] == $rangeeEv['eq_vis']) {
	 $trouve = false;
	 while (!$trouve) {
	 if ($perCour >= count($lesPer) - 1)// Cas de la derniere periode
	 {
	 $ventVis[$perCour]++;
	 $trouve = true;
	 } else {

	 if ($rangVentBut['chrono'] > $chronoDeb && $rangVentBut['chrono'] < $lesPer[0]) {$ventVis[0]++;
	 $trouve = true;
	 } else if ($rangVentBut['chrono'] > $lesPer[$perCour] && $rangVentBut['chrono'] < $lesPer[$perCour + 1]) {$ventVis[$perCour + 1]++;
	 $trouve = true;
	 } else {$perCour++;
	 }
	 }
	 }

	 }

	 }
	 */
	unset($matchID);
	$matchID = $rangeeEv['matchIdRef'];

	//$leMatch = parseMatchID($matchID);
	//$dateMatch = strtotime($leMatch['date']);
	//$pm = strtotime($premierMatch);
	//$dm = strtotime($dernierMatch);

	//	if(($dateMatch>=$pm)&&($dateMatch<=$dm))
	//	{
	/*
	 $JSONstring .= "{\"matchID\": \"" . $matchID . "\",";
	 $JSONstring .= "\"date\": \"" . $rangeeEv['date'] . "\",";
	 $JSONstring .= "\"mavId\": \"" . $rangeeEv['matchId'] . "\",";

	 $mE = trouveFic($rangeeEv['eq_dom'], $fic_array);
	 $JSONstring .= "\"eqDom\": \"" . $mE['nom_equipe'] . "\",";

	 $JSONstring .= "\"eqDomId\": \"" . $rangeeEv['eq_dom'] . "\",";
	 $JSONstring .= "\"ficIdDom\": \"" . $trouveDom . "\",";
	 //	$JSONstring .="\"eqDom\": \"".$leMatch['dom']."\",";
	 $JSONstring .= "\"equipeScoreDom\": \"" . $rangeeEv['score_dom'] . "\",";
	 $JSONstring .= "\"equipeScoreVis\": \"" . $rangeeEv['score_vis'] . "\",";
	 $JSONstring .= "\"statut\": \"" . $rangeeEv['statut'] . "\",";
	 //$JSONstring .="\"eqVis\": \"".$leMatch['vis']."\"},";
	 $JSONstring .= "\"eqVisId\": \"" . $rangeeEv['eq_vis'] . "\",";
	 $JSONstring .= "\"ficIdVis\": \"" . $trouveVis . "\",";

	 $JSONstring .= "\"arbitre\": \"" . $arbitre . "\",";
	 $JSONstring .= "\"butGagnant\": \"" . $butGagne . "\",";
	 $JSONstring .= "\"gGagnant\": \"" . $gGagne . "\",";
	 $JSONstring .= "\"gPerdant\": \"" . $gPerd . "\",";
	 $JSONstring .= "\"ventDom\":" . json_encode($ventDom) . ",";
	 $JSONstring .= "\"ventVis\":" . json_encode($ventVis) . ",";
	 $JSONstring .= "\"cleValeur\":" . json_encode($tmpJS) . ",";
	 $mE = trouveFic($rangeeEv['eq_vis'], $fic_array);
	 $JSONstring .= "\"eqVis\": \"" . $mE['nom_equipe'] . "\"},";
	 */

	$JS2 = Array();

	$mMatch = trouveMatch($matchID, $lesMatchs);

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
	$JS2['butGagnant'] = $mMatch['butGagnant'];
	$JS2['gGagnant'] = $gGagne;
	$JS2['gPerdant'] = $gPerd;
	$JS2['ventDom'] = $mMatch['ventDom'];
	$JS2['ventVis'] = $mMatch['ventVis'];
	//	$JS2['ventVis'] =$ventVis;
	$JS2['cleValeur'] = $tmpJS;
	$mE = trouveFic($rangeeEv['eq_vis'], $fic_array);
	$JS2['coulVis'] = $mE['couleur1'];
	$JS2['eqVis'] = $mE['nom_equipe'];
	$JS2['arenaId'] = $rangeeEv['arenaId'];
	$JSONstring .= json_encode($JS2) . ", ";
	
array_push($mesMatchs, $JS2);
	
	array_push($liste, $matchID);
	//	}
$Ieq++;
}
	
$Ine++;

if (!strcmp(",", substr($JSONstring, -1)))// Pour �viter les vides;
{
	$JSONstring = substr($JSONstring, 0, -1);
}
$JSONstring .= "]}";

$jsRetour= array();
$jsRetour['matchs'] = $mesMatchs;
echo json_encode($jsRetour);


//$JSONstring .= "------------------------------------------------------------------------------------------------------------";
//$JSONstring .= json_encode($lesMatchs);

//echo json_encode($Sommaire);
//echo $JSONstring;
//	echo json_encode($matchEnCours);
//	echo json_encode($liste);
?>

