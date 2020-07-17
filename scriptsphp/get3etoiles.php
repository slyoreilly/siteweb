<?php

/////////////////////////////////////////////////////////////
//
//  Définitions des variables
//
////////////////////////////////////////////////////////////

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

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

}



function trouveJoueur($joueurId, $array_joueur,$conn) {
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
$prSaison = mysqli_query($conn,$qMatchSai) or die(mysqli_error($conn) . " dans " . $qMatchSai);
mysqli_data_seek($prSaison,0);
$row=mysqli_fetch_row($prSaison);
$premierMatch = $row[0];
$dernierMatch =  $row[1];
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
mysqli_query($conn,"SET SQL_BIG_SELECTS=1"); 
  
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
$resultEvent = mysqli_query($conn,$qGros) or die(mysqli_error($conn) . " dans " . $qGros);

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
$resultJoueurs = mysqli_query($conn,$qJoueurs) or die(mysqli_error($conn) . " dans " . $qJoueurs);

while ($row = mysqli_fetch_assoc($resultJoueurs)) {
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
while ($rangeeEv = mysqli_fetch_array($resultEvent)) {
	
	$mesMatchs[$cptM]['nomMatch']=$rangeeEv['match_event_id'];
	

	$etoile1 = "";
	$etoile2 = "";
	$etoile3 = "";


	//echo $tmpCV."/.-";
	if (strlen($rangeeEv['cleValeur']) > 0) {
		try {$tmpJS = json_decode($rangeeEv['cleValeur'], true);
		} catch(Exception $e) {echo $e -> getMessage();
		}
	}//json_decode(stripcslashes($tmpCV));}
	else {$tmpJS = $rangeeEv['cleValeur'];
	}
	$mJS = new \stdClass();
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
mysqli_close($conn);
?>

