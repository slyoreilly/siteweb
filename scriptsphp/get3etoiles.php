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
mysql_query("SET SQL_BIG_SELECTS=1"); 
  
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


$Ieq = 0;
$mesMatchs = array();
$IF = 0;
$cptM=0;
/*$JSONstring .= $ligueId;
 $JSONstring .= $premierMatch;
 $JSONstring .= $dernierMatch;
 */
while ($rangeeEv = mysql_fetch_array($resultEvent)) {
	
	$mesMatchs[$cptM]['nomMatch']=$rangeeEv['match_event_id'];
	

	$etoile1 = "";
	$etoile2 = "";
	$etoile3 = "";


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
	if (isset($mJS['etoile1'])) {

	
		$mJ = trouveJoueur($mJS['etoile1'], $joueurs_array);
		$etoile1 = $mJ['NomJoueur'];
		//echo "/.".$etoile1;
	}
	if (isset($mJS['etoile2'])) {
		$mJ = trouveJoueur($mJS['etoile2'], $joueurs_array);
		$etoile2 = $mJ['NomJoueur'];
	
	}
	if (isset($mJS['etoile3'])) {
		$mJ = trouveJoueur($mJS['etoile3'], $joueurs_array);
		$etoile3 = $mJ['NomJoueur'];
	}
	$mesMatchs[$cptM]['etoile1']=$etoile1;
	$mesMatchs[$cptM]['etoile2']=$etoile1;
	$mesMatchs[$cptM]['etoile3']=$etoile1;

$cptM++;
}




echo json_encode($mesMatchs);
?>

