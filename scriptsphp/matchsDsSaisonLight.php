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
	if($workEnv=="production"){
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
} else{
	$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database, $db_port);
}
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

function trouveIDParNomLigue($nomLi) {
	$resultLigue = mysqli_query($conn, "SELECT * FROM Ligue WHERE 1") or die(mysqli_error($conn) . " dans trouveIDParNomLigue");
	while ($rangeeLigue = mysqli_fetch_array($resultLigue)) {
		if (!strcmp($rangeeLigue['Nom_Ligue'], $nomLi)) {$LigueID = $rangeeLigue['ID_Ligue'];
			// Ce sont de INT
		}
	}
	return $LigueID;
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

 
$qGros = "SELECT `Ligue`.*, `TableMatch`.*, TD.nom_equipe as nomEqDom, TV.nom_equipe as nomEqVis
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								Inner JOIN TableEquipe TD
									ON TD.equipe_id = TableMatch.eq_dom
								Inner JOIN TableEquipe TV
									ON TV.equipe_id = TableMatch.eq_vis
								WHERE  TableMatch.ligueRef='{$ligueId}'
									AND TableMatch.date>'{$premierMatch}'
									AND TableMatch.date<'{$dernierMatch}' 
									";
								
$resultEvent = mysqli_query($conn, $qGros) or die(mysqli_error($conn) . " dans " . $qGros);

/*

$qFic = "SELECT *
													FROM TableEquipe
													WHERE 1";

$resultFic = mysqli_query($conn, $qFic) or die(mysqli_error($conn) . " dans " . $qFic);

while ($row = mysqli_fetch_assoc($resultFic)) {
	$fic_array[] = $row;
	// Inside while loop
}

*/

$Ieq = 0;
$mesFic = array();
$IF = 0;
$mesMatchs = array();

////////////////////////////////////////
//
//  	Début du gros While, ou on boucle sur les équipes
//

while ($rangeeEv = mysqli_fetch_array($resultEvent)) {

	/*$trouveDom = 0;
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

	*/

	
	unset($matchID);
	$matchID = $rangeeEv['matchIdRef'];

	
	$JS2 = Array();

//	$mMatch = trouveMatch($matchID, $lesMatchs);

	$JS2['matchID'] = $matchID;
	$JS2['date'] = $rangeeEv['date'];
	//$mE = trouveFic($rangeeEv['eq_dom'], $fic_array);
	$JS2['eqDom'] = $rangeeEv['nomEqDom'];
	//$JS2['coulDom'] = $mE['couleur1'];
	$JS2['eqDomId'] = $rangeeEv['eq_dom'];
	//$JS2['ficIdDom'] = $trouveDom;
	$JS2['equipeScoreDom'] = $rangeeEv['score_dom'];
	$JS2['equipeScoreVis'] = $rangeeEv['score_vis'];
	$JS2['statut'] = $rangeeEv['statut'];
	$JS2['eqVisId'] = $rangeeEv['eq_vis'];
	//$JS2['ficIdVis'] = $trouveVis;
	//	$JS2['ventVis'] =$ventVis;
	//$JS2['cleValeur'] = $tmpJS;
	//$mE = trouveFic($rangeeEv['eq_vis'], $fic_array);
	//$JS2['coulVis'] = $mE['couleur1'];
	$JS2['eqVis'] = $rangeeEv['nomEqVis'];
	$JS2['arenaId'] = $rangeeEv['arenaId'];
	
	
	
//	if(contientMatch($matchID, $mesMatchs)==false){
array_push($mesMatchs, $JS2);		
//	}
	
//	array_push($liste, $matchID);
	//	}
$Ieq++;
}
	
$Ine++;

$jsRetour= array();
$jsRetour['matchs'] = $mesMatchs;
echo json_encode($jsRetour);


?>

