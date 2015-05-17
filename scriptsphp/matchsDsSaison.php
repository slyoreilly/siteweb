<?php

/////////////////////////////////////////////////////////////
//
//  Définitions des variables
//
////////////////////////////////////////////////////////////

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

	if (!mysql_connect($db_host, $db_user, $db_pwd))
		die("Can't connect to database");

	if (!mysql_select_db($database)) {
		echo "<h1>Database: {$database}</h1>";
		die("Can't select database");

	}

	mysql_query("SET NAMES 'utf8'");
	mysql_query("SET CHARACTER SET 'utf8'");
}

/////////////////////////////////////////////////////////////
//
//

function trouveNomJoueurParID($ID) {

	$resultJoueur = mysql_query("SELECT * FROM TableJoueur WHERE joueur_id = '{$ID}'") or die(mysql_error() . " dans trouveNomJoueurParID");
	if ($rangeeJoueur = mysql_fetch_array($resultJoueur))
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
	$resultEquipe = mysql_query("SELECT * FROM TableEquipe") or die(mysql_error() . " dans trouveIDParNomEquipe");
	while ($rangeeEquipe = mysql_fetch_array($resultEquipe)) {
		if (!strcmp($rangeeEquipe['nom_equipe'], $nomEq)) {$equipeID = $rangeeEquipe['equipe_id'];
			// Ce sont de INT
		}
	}
	return $equipeID;
}

function trouveIDParNomEqEtLigue($nomEq, $ligueId) {
	$resultEquipe = mysql_query("SELECT * FROM TableEquipe
										JOIN abonEquipeLigue
											ON (TableEquipe.equipe_id =abonEquipeLigue.equipeId) 
										WHERE 
											abonEquipeLigue.ligueId='{$ligueId}'
										AND
											TableEquipe.nom_equipe='{$nomEq}'") or die(mysql_error() . " dans trouveIDParNomEqEtLigue");
	while ($rangeeEquipe = mysql_fetch_array($resultEquipe)) { {
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
	$resultLigue = mysql_query("SELECT * FROM Ligue WHERE 1") or die(mysql_error() . " dans trouveIDParNomLigue");
	while ($rangeeLigue = mysql_fetch_array($resultLigue)) {
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
	$resultEquipe2 = mysql_query("SELECT * FROM TableEquipe WHERE equipe_id='{$IEq}'") or die(mysql_error() . " dans trouveNomParIDEquipe");
	while ($rangeeEquipe2 = mysql_fetch_array($resultEquipe2)) {

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
	$rfSaison = mysql_query("SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ID}' and saisonActive=1") or die(mysql_error() . " trouveSaisonActiveDeLigueId");
	return (mysql_result($rfSaison, 0));
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

$ligueId = $_POST["ligueId"];
$saisonId = $_POST["saisonId"];

//	if(!isset($ligueId))
//	$ligueId=3;
//	$ligueId = trouveIDParNomLigue($monGet);

$qMatchSai = "SELECT premierMatch,dernierMatch FROM TableSaison where saisonId ='{$saisonId}'";
$prSaison = mysql_query($qMatchSai) or die(mysql_error() . " dans " . $qMatchSai);
$premierMatch = mysql_result($prSaison, 0, 0);
$dernierMatch = mysql_result($prSaison, 0, 1);
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
$qGros = "SELECT TableEquipe.*, Ligue.*, TableMatch.*,TableEvenement0.* 
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
									";
$resultEvent = mysql_query($qGros) or die(mysql_error() . " dans " . $qGros);

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
$resultJoueurs = mysql_query($qJoueurs) or die(mysql_error() . " dans " . $qJoueurs);

while ($row = mysql_fetch_assoc($resultJoueurs)) {
	$joueurs_array[] = $row;
	// Inside while loop
}

$qFic = "SELECT *
													FROM TableEquipe
													WHERE 1";

$resultFic = mysql_query($qFic) or die(mysql_error() . " dans " . $qFic);

while ($row = mysql_fetch_assoc($resultFic)) {
	$fic_array[] = $row;
	// Inside while loop
}

$qArb = "SELECT *
													FROM TableArbitre
													JOIN TableUser
														ON (TableArbitre.userId=TableUser.noCompte)
													WHERE 1";
$rArb = mysql_query($qArb) or die(mysql_error() . " dans " . $qArb);

while ($row = mysql_fetch_assoc($rArb)) {
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
$resultGard = mysql_query($qGard) or die(mysql_error() . " dans " . $qGard);

$array_gard = array();
while ($rGard = mysql_fetch_assoc($resultGard)) {
	$matchGard = array();
	$matchGard['match'] = $rGard['matchIdRef'];
	$matchDeja = false;
	$cle=-1;

	for($a=0;$a<count($array_gard);$a++){
		if($array_gard[$a]['match']==$matchGard['match']){
			$cle=$a;
		}	
	}

	if ($rGard['eq_dom'] == $rGard['equipe_event_id']) {
		$matchGard['gDom'] = $rGard['joueur_event_ref'];

		if ($cle>-1) {
			$array_gard[$cle]['gDom'] = $rGard['joueur_event_ref'];
		} else {array_push($array_gard, $matchGard);
		}

	}
	if ($rGard['eq_vis'] == $rGard['equipe_event_id']) {
		$matchGard['gVis'] = $rGard['joueur_event_ref'];

		if ($cle>-1) {
			$array_gard[$cle]['gVis'] = $rGard['joueur_event_ref'];
		} else {array_push($array_gard, $matchGard);
		}

	}

}

$Ieq = 0;
$mesFic = array();
$IF = 0;
/*$JSONstring .= $ligueId;
 $JSONstring .= $premierMatch;
 $JSONstring .= $dernierMatch;
 */
while ($rangeeEv = mysql_fetch_array($resultEvent)) {
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
		/*$qFic = "SELECT ficId
		 FROM TableEquipe
		 WHERE equipe_id= '{$rangeeEv['eq_dom']}'";
		 $rFic = mysql_query($qFic) or die(mysql_error() . " dans " . $qFic);
		 $tmpFic = mysql_fetch_row($rFic);
		 $mesFic[$IF]['ficId'] = $tmpFic[0];
		 $trouveDom = $tmpFic[0];*/
		$trouveDom = $mE['ficId'];
		$mesFic[$IF]['ficId'] = $trouveDom;
	}
	if ($trouveVis == 0) {
		$mesFic[$IF] = array();
		$mesFic[$IF]['eqId'] = $rangeeEv['eq_vis'];
		/*		$qFic = "SELECT ficId
		 FROM TableEquipe
		 WHERE equipe_id= '{$rangeeEv['eq_vis']}'";
		 $rFic = mysql_query($qFic) or die(mysql_error() . " dans " . $qFic);
		 $tmpFic = mysql_fetch_row($rFic);
		 $mesFic[$IF]['ficId'] = $tmpFic[0];
		 $trouveVis = $tmpFic[0];*/
		$mE = trouveFic($rangeeEv['eq_vis'], $fic_array);
		$trouveVis = $mE['ficId'];
		$mesFic[$IF]['ficId'] = $trouveVis;

	}

	$etoile1 = "";
	$etoile2 = "";
	$etoile3 = "";
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
	if (isset($tmpJS['etoile1'])) {$mJS['etoile1'] = $tmpJS['etoile1'];
	}
	if (isset($tmpJS['etoile2'])) {$mJS['etoile2'] = $tmpJS['etoile2'];
	}
	if (isset($tmpJS['etoile3'])) {$mJS['etoile3'] = $tmpJS['etoile3'];
	}
	if (isset($tmpJS['arbitreId'])) {$mJS['arbitreId'] = $tmpJS['arbitreId'];
	}
	if (isset($mJS['etoile1'])) {

		/*$qJou = "SELECT NomJoueur
		 FROM TableJoueur
		 WHERE joueur_id= '{$mJS['etoile1']}'";
		 $rJou = mysql_query($qJou) or die(mysql_error() . " dans " . $qJou);
		 $tmpJou = mysql_fetch_row($rJou);
		 $etoile1 = $tmpJou[0];*/
		$mJ = trouveJoueur($mJS['etoile1'], $joueurs_array);
		$etoile1 = $mJ['NomJoueur'];
		//echo "/.".$etoile1;
	}
	if (isset($mJS['etoile2'])) {
		$mJ = trouveJoueur($mJS['etoile2'], $joueurs_array);
		$etoile2 = $mJ['NomJoueur'];
		/*		$qJou2 = "SELECT NomJoueur
		 FROM TableJoueur
		 WHERE joueur_id= '{$mJS['etoile2']}'";
		 $rJou2 = mysql_query($qJou2) or die(mysql_error() . " dans " . $qJou2);
		 $tmpJou2 = mysql_fetch_row($rJou2);
		 $etoile2 = $tmpJou2[0];*/
	}
	if (isset($mJS['etoile3'])) {
		$mJ = trouveJoueur($mJS['etoile3'], $joueurs_array);
		$etoile3 = $mJ['NomJoueur'];

		/*		$qJou3 = "SELECT NomJoueur
		 FROM TableJoueur
		 WHERE joueur_id= '{$mJS['etoile3']}'";
		 $rJou3 = mysql_query($qJou3) or die(mysql_error() . " dans " . $qJou3);
		 $tmpJou3 = mysql_fetch_row($rJou3);
		 $etoile3 = $tmpJou3[0];*/
	}
	$arbitre = "";
	if (isset($mJS['arbitreId'])) {
		/*		$qArb = "SELECT prenom,nom
		 FROM TableArbitre
		 JOIN TableUser
		 ON (TableArbitre.userId=TableUser.noCompte)
		 WHERE arbitreId= '{$mJS['arbitreId']}'";
		 $rArb = mysql_query($qArb) or die(mysql_error() . " dans " . $qArb);*/
		$mA = trouveArbitre($mJS['arbitreId']);
		if (!is_null($mA)) {
			$arbitre = $mA['prenom'] + " " + $mA['nom'];
		}
	}

	///////////   Gardiens gagnants, perdant.

	$gardiens = trouveGardiens($rangeeEv['match_event_id'], $array_gard);

	/*	$qGardDom = "SELECT TableJoueur.NomJoueur
	 FROM TableEvenement0
	 JOIN TableJoueur
	 ON (TableJoueur.joueur_id=TableEvenement0.joueur_event_ref)
	 WHERE
	 match_event_id='{$rangeeEv['match_event_id']}'
	 AND code='3'
	 AND souscode='5'
	 AND equipe_event_id='{$rangeeEv['eq_dom']}'";
	 $rGardDom = mysql_query($qGardDom) or die(mysql_error() . " dans " . $qGardDom);

	 $tmpGardDom = mysql_fetch_row($rGardDom);
	 $gardDom = $tmpGardDom[0];
	 */
	$gD = trouveJoueur($gardiens['dom'], $joueurs_array);
	$gardDom = $gD['NomJoueur'];
	/*
	 $qGardVis = "SELECT TableJoueur.NomJoueur
	 FROM TableEvenement0
	 JOIN TableJoueur
	 ON (TableJoueur.joueur_id=TableEvenement0.joueur_event_ref)
	 WHERE
	 match_event_id='{$rangeeEv['match_event_id']}'
	 AND code='3'
	 AND souscode='5'
	 AND equipe_event_id='{$rangeeEv['eq_vis']}'";
	 $rGardVis = mysql_query($qGardVis) or die(mysql_error() . " dans " . $qGardVis);
	 $tmpGardVis = mysql_fetch_row($rGardVis);
	 $gardVis = $tmpGardVis[0];*/
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
	if ($rangeeEv['score_dom'] > $rangeeEv['score_vis']) {
		$qBG = "SELECT TableJoueur.NomJoueur
													FROM TableEvenement0
													JOIN TableJoueur
														ON (TableJoueur.joueur_id=TableEvenement0.joueur_event_ref)
													WHERE 
														match_event_id='{$rangeeEv['match_event_id']}'
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
														match_event_id='{$rangeeEv['match_event_id']}'
														AND code='0'
														AND equipe_event_id='{$rangeeEv['eq_vis']}'
														ORDER BY `TableEvenement0`.`chrono` ASC
														LIMIT {$rangeeEv['score_dom']} , 1";
	}
	//			echo "/////".$qBG;
	if ($rangeeEv['score_dom'] != $rangeeEv['score_vis'])// S'il n'y a pas d'égalité
	{
		$rButGagne = mysql_query($qBG) or die(mysql_error() . " dans " . $qBG);
		$tmpButGagne = mysql_fetch_row($rButGagne);
		$butGagne = $tmpButGagne[0];
	}
	////// Section ventilation des buts

	$qVentPer = "SELECT chrono, code, souscode
													FROM TableEvenement0
													
													WHERE 
														match_event_id='{$rangeeEv['match_event_id']}'
														AND (code='10' OR code='11') 
														ORDER BY TableEvenement0.souscode ASC";

	//			echo "/////".$qBG;

	$lesPer = array();
	$IP = 0;
	$chronoDeb = 0;
	$rVentPer = mysql_query($qVentPer) or die(mysql_error() . " dans " . $qVentPer);
	while ($rangVentPer = mysql_fetch_array($rVentPer)) {
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
														match_event_id='{$rangeeEv['match_event_id']}'
														AND (code='0') 
														ORDER BY `TableEvenement0`.`chrono` ASC";

	$ventDom = array_fill(0, count($lesPer) + 1, 0);
	$ventVis = array_fill(0, count($lesPer) + 1, 0);

	$rVentBut = mysql_query($qVentBut) or die(mysql_error() . " dans " . $qVentBut);

	$perCour = 0;
	while ($rangVentBut = mysql_fetch_array($rVentBut)) {
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

	unset($matchID);
	$matchID = $rangeeEv['match_event_id'];

	$leMatch = parseMatchID($matchID);
	$dateMatch = strtotime($leMatch['date']);
	//$pm = strtotime($premierMatch);
	//$dm = strtotime($dernierMatch);

	//	if(($dateMatch>=$pm)&&($dateMatch<=$dm))
	//	{
	$JSONstring .= "{\"matchID\": \"" . $matchID . "\",";
	$JSONstring .= "\"date\": \"" . $rangeeEv['date'] . "\",";

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
	if (strlen($etoile1) > 0)
		$JSONstring .= "\"etoile1\": \"" . $etoile1 . "\",";
	if (strlen($etoile2) > 0)
		$JSONstring .= "\"etoile2\": \"" . $etoile2 . "\",";
	if (strlen($etoile3) > 0)
		$JSONstring .= "\"etoile3\": \"" . $etoile3 . "\",";
	$JSONstring .= "\"arbitre\": \"" . $arbitre . "\",";
	$JSONstring .= "\"butGagnant\": \"" . $butGagne . "\",";
	$JSONstring .= "\"gGagnant\": \"" . $gGagne . "\",";
	$JSONstring .= "\"gPerdant\": \"" . $gPerd . "\",";
	$JSONstring .= "\"ventDom\":" . json_encode($ventDom) . ",";
	$JSONstring .= "\"ventVis\":" . json_encode($ventVis) . ",";
	$JSONstring .= "\"cleValeur\":" . json_encode($tmpJS) . ",";
	$mE = trouveFic($rangeeEv['eq_vis'], $fic_array);
	$JSONstring .= "\"eqVis\": \"" . $mE['nom_equipe'] . "\"},";
	array_push($liste, $matchID);
	//	}
}

$Ine++;

if (!strcmp(",", substr($JSONstring, -1)))// Pour �viter les vides;
{
	$JSONstring = substr($JSONstring, 0, -1);
}
$JSONstring .= "]}";

//echo json_encode($Sommaire);
echo $JSONstring;
//	echo json_encode($matchEnCours);
//	echo json_encode($liste);
?>

