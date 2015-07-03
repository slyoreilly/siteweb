<?php

/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
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
$tableSaison = 'TableSaison';

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

if (!mysql_connect($db_host, $db_user, $db_pwd))
	die("Can't connect to database");

if (!mysql_select_db($database)) {
	echo "<h1>Database: {$database}</h1>";
	die("Can't select database");

}

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

/////////////////////////////////////////////////////////////
//
//

function trouveNomJoueurParID($ID) {
	unset($resultJoueur);
	$resultJoueur = mysql_query("SELECT * FROM TableJoueur WHERE joueur_id = '{$ID}'") or die(mysql_error() . "query f1");
	while ($rangeeJoueur = mysql_fetch_array($resultJoueur)) {
		if (strcmp($rangeeJoueur['NomJoueur'], "null")) {
			return ($rangeeJoueur['NomJoueur']);
		} else {
			return ("Anonyme");
		}
	}
	return ("Anonyme");
}

/////////////////////////////////////////////////////////////
//
//

function parseMatchID($ID) {

	$monMatch['date'] = substr($ID, 0, stripos($ID, '_'));
	$longueur = strlen($monMatch['date']);
	$monMatch['dom'] = substr($ID, stripos($ID, '_') + 1, stripos(substr($ID, $longueur + 2), '_') + 1);
	$monMatch['vis'] = substr($ID, strripos($ID, '_') + 1);
	return $monMatch;
}

/////////////////////////////////////////////////////
//
//   Trouve ID de la ligue � partir du nom.
//
////////////////////////////////////////////////////

function trouveIDParNomLigue($nomLi) {

	$resultLigue = mysql_query("SELECT * FROM {$tableLigue}") or die(mysql_error() . "query f2");
	while ($rangeeLigue = mysql_fetch_array($resultLigue)) {
		if (!strcmp($rangeeLigue['Nom_Ligue'], $ligue)) {$ligueSelect = $rangeeLigue['ID_Ligue'];
		}
		// Prend le ID de la ligue pour trouver les �quipes.
	}

	return $LigueID;
}

/////////////////////////////////////////////////////
//
//   Trouve ID de l'equipe � partir du nom.
//
////////////////////////////////////////////////////
function trouveNomParIDEquipe($IEq) {

	$resultEquipe = mysql_query("SELECT * FROM {$tableEquipe}") or die(mysql_error() . "query f3");
	while ($rangeeEquipe = mysql_fetch_array($resultEquipe)) {
		if (!strcmp($rangeeEquipe['nom_equipe'], $equipe)) {$equipeID = $rangeeEquipe['equipe_id'];
			// Ce sont de INT
		}
	}
	return $NomEquipe;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
//
//
function trouveSaisonActiveDeLigueId($ID) {
	$rfSaison = mysql_query("SELECT saisonId FROM TableSaison WHERE ligueRef = {$ID} ORDER BY premierMatch DESC") or die(mysql_error() . " Select saisonId");
	//echo mysql_result($rfSaison, 0)."\n";
	//$tmp= (mysql_fetch_array($rfSaison));
	//echo $tmp['saisonId']."\n";
	return (mysql_result($rfSaison, 0));
}

$getLigue = $_POST["ligueId"];
$saisonId = $_POST["saisonId"];

if (!strcmp($saisonId, "")&& strcmp($getLigue, ""))// Sp�cifie par la ligue
{
	$saisonId = trouveSaisonActiveDeLigueId($getLigue);
	//	$saisonId =2;
}
$prSaison = mysql_query("SELECT premierMatch 
						FROM TableSaison 
						WHERE saisonId ='{$saisonId}'") or die(mysql_error() . "query PM: saisonId: " . $saisonId);
$premierMatch = mysql_result($prSaison, 0);
$drSaison = mysql_query("SELECT dernierMatch FROM TableSaison where saisonId ='{$saisonId}'") or die(mysql_error() . "query DM");
$dernierMatch = mysql_result($drSaison, 0);

//
$lesMatchs = array();
$I2 = 0;
$resultMatch = mysql_query("SELECT * 
							FROM TableMatch 
								WHERE ligueRef={$getLigue}") or die(mysql_error() . "query Matchs");
								
	//$strBigMatch = "SELECT TableMatch.*, abonEquipeLigue.*
	//			FROM TableMatch 
	//				
	//				LEFT JOIN abonEquipeLigue 
	//					 ON (TableMatch.ligueRef=abonEquipeLigue.ligueId)
	//					 						 
	//				WHERE  ligueRef={$getLigue}//
//
//						AND abonEquipeLigue.debutAbon<='{$dateAbon}'
//						AND abonEquipeLigue.finAbon>'{$dateAbon}'
//						AND abonEquipeLigue.ligueId = 	{$getLigue}		
//						AND code<10
//						GROUP BY match_id";
//						
//						mysql_query("SET SQL_BIG_SELECTS=1");
//	$resultMatch = mysql_query($strBigMatch) or die(mysql_error() . "query gros stock de match");
								
								
								

while ($rangeeMatch = mysql_fetch_array($resultMatch)) {
	if ($rangeeMatch['date'] >= $premierMatch && $rangeeMatch['date'] <= $dernierMatch) {
		$lesMatchs[$I2] = $rangeeMatch['matchIdRef'];
		$I2++;
	}
}

$Im = 0;
$JoueurSommeEvenement = array();
$I0 = 0;

/////////////////  Section avec abonnement, on liste les equipes.

$resultEq = mysql_query("SELECT abonEquipeLigue.*  
				FROM abonEquipeLigue
					WHERE  ligueId={$getLigue}") or die(mysql_error() . "query abon 1");

$eqAbon = array();
$Ieq = 0;
while ($rangeeEq = mysql_fetch_array($resultEq)) {
	$eqAbon[$Ieq] = $rangeeEq['equipeId'];
	$Ieq++;
}

////////////////////////////////////////////////////////////////////
// Quelle date considérer? Maintenant si saison en cours, dernierMatch si saison ancienne.
if ($dernierMatch > date("Y-m-d")) {$dateAbon = date("Y-m-d");
} else {
	$dateAbon = $dernierMatch;
}

//while ($Im < count($lesMatchs)) {
	// Retrieve all the data from la table
	unset($resultEvent);
	unset($rangeeEv);
	unset($joueurs);
	$joueurs = Array();
//	 match_event_id = '{$lesMatchs[$Im]}' AND 
	$strQuery = "SELECT TableEvenement0.*, abonJoueurEquipe.*, TableEquipe.nom_equipe,TableEquipe.ficId, 
								TableEquipe.ligue_equipe_ref, TableJoueur.NomJoueur, TableJoueur.NumeroJoueur ,TableJoueur.ficIdPortrait 
				FROM TableEvenement0 
					LEFT JOIN TableJoueur 
						 ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id)
					INNER JOIN abonJoueurEquipe 
						 ON (TableEvenement0.joueur_event_ref=abonJoueurEquipe.joueurId)
					INNER JOIN abonEquipeLigue 
						 ON (abonJoueurEquipe.equipeId=abonEquipeLigue.equipeId)
						  
					INNER JOIN TableEquipe
						 ON (abonJoueurEquipe.equipeId=TableEquipe.equipe_id) 
						 						 
					WHERE abonJoueurEquipe.debutAbon<='{$dateAbon}'
						AND abonJoueurEquipe.finAbon>'{$dateAbon}'
						AND abonEquipeLigue.debutAbon<='{$dateAbon}'
						AND abonEquipeLigue.finAbon>'{$dateAbon}'
						AND abonEquipeLigue.ligueId = 	{$getLigue}		
						AND abonEquipeLigue.permission<31		
						AND code<10
						GROUP BY event_id";
						
						mysql_query("SET SQL_BIG_SELECTS=1");
	$resultEvent = mysql_query($strQuery) or die(mysql_error() . "query gros stock");

	while ($rangeeEv = mysql_fetch_array($resultEvent)) {
		$JoueurSommeEvenement[$I0]['event_id'] = $rangeeEv['event_id'];
		$JoueurSommeEvenement[$I0]['joueur_event_ref'] = $rangeeEv['joueur_event_ref'];
		$JoueurSommeEvenement[$I0]['code'] = $rangeeEv['code'];
		$JoueurSommeEvenement[$I0]['souscode'] = $rangeeEv['souscode'];
		$JoueurSommeEvenement[$I0]['nom_equipe'] = $rangeeEv['nom_equipe'];
		$JoueurSommeEvenement[$I0]['ficId'] = $rangeeEv['ficId'];
		$JoueurSommeEvenement[$I0]['nom'] = $rangeeEv['NomJoueur'];
		$JoueurSommeEvenement[$I0]['numero'] = $rangeeEv['NumeroJoueur'];
			$JoueurSommeEvenement[$I0]['ficIdPortrait'] = $rangeeEv['ficIdPortrait'];
		
		array_push($joueurs, $JoueurSommeEvenement[$I0]['joueur_event_ref']);

		$I0++;
	}
	unset($resultEvent);
	unset($rangeeEv);

	//// Section remplaçants: non abonné dans une équipe.
	
	$resultEvent = mysql_query("SELECT TableEvenement0.*, TableJoueur.NomJoueur,TableJoueur.joueur_id,TableJoueur.ficIdPortrait, TableJoueur.NumeroJoueur  
				FROM TableEvenement0 
					LEFT JOIN TableJoueur 
						 ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id)
					INNER JOIN abonJoueurLigue 
						 ON (TableEvenement0.joueur_event_ref=abonJoueurLigue.joueurId)
						 		 						 
					WHERE  match_event_id = '{$lesMatchs[$Im]}'
						AND abonJoueurLigue.ligueId = 	{$getLigue}		
					 AND code<10
						") or die(mysql_error() . "query stats pers");

	while ($rangeeEv = mysql_fetch_array($resultEvent)) {
		if (in_array($rangeeEv['joueur_event_ref'], $joueurs) == false) {
			$JoueurSommeEvenement[$I0]['event_id'] = $rangeeEv['event_id'];
			$JoueurSommeEvenement[$I0]['joueur_event_ref'] = $rangeeEv['joueur_event_ref'];
			$JoueurSommeEvenement[$I0]['code'] = $rangeeEv['code'];
			$JoueurSommeEvenement[$I0]['souscode'] = $rangeeEv['souscode'];
			$JoueurSommeEvenement[$I0]['nom_equipe'] = "";
			$JoueurSommeEvenement[$I0]['ficId'] = "0";
			$JoueurSommeEvenement[$I0]['nom'] = $rangeeEv['NomJoueur'];
			$JoueurSommeEvenement[$I0]['numero'] = $rangeeEv['NumeroJoueur'];
			$JoueurSommeEvenement[$I0]['ficIdPortrait'] = $rangeeEv['ficIdPortrait'];
						$I0++;
		}
	}
//	$Im++;
//}

///////////////////////////////////////////////////////////
//
// 	Construit la liste de joueur, Initialise les stats.

$rangeeStats = array();
$Inom = 0;
$Ibuts = 0;
$Ipasses = 0;
$NbEntre = count($JoueurSommeEvenement);
unset($joueursEntres);
$joueursEntres = array();

$Ievent = 0;

while ($Ievent < $NbEntre) {
	$ligneEvent0 = $JoueurSommeEvenement[$Ievent]['event_id'];
	$ligneEvent1 = $JoueurSommeEvenement[$Ievent]['joueur_event_ref'];
	$ligneEvent2 = $JoueurSommeEvenement[$Ievent]['code'];
	$Itrouve = 0;
	$boule = 0;
	while ($Itrouve < count($joueursEntres)) {
		if (!strcmp($joueursEntres[$Itrouve], $ligneEvent1)) {$boule = 1;
		}
		$Itrouve++;
	}
	if ($boule == 0)//joueur pas dans la liste
	{
		$joueursEntres[$Itrouve] = $ligneEvent1;

		///////////////////////////
		//////  tests...

		$rangeeStats[$Itrouve][0] = $JoueurSommeEvenement[$Ievent]['nom'];
		$rangeeStats[$Itrouve][1] = 0;
		$rangeeStats[$Itrouve][2] = 0;
		$rangeeStats[$Itrouve][3] = 0;
		$rangeeStats[$Itrouve][4] = $JoueurSommeEvenement[$Ievent]['nom_equipe'];
		$rangeeStats[$Itrouve][5] = $JoueurSommeEvenement[$Ievent]['ficId'];
		$rangeeStats[$Itrouve][6] = $ligneEvent1;
		$rangeeStats[$Itrouve][7] = $JoueurSommeEvenement[$Ievent]['numero'];
		$rangeeStats[$Itrouve][8] = $JoueurSommeEvenement[$Ievent]['ficIdPortrait'];
	}

	$Ievent++;

}

///////////////////////////////////////////////////////
//
// 	Construit la matrice de stats

$NbRangeeStats = count($rangeeStats);
$Ievent = 0;
while ($Ievent < $NbEntre) {
	$ligneEvent0 = $JoueurSommeEvenement[$Ievent]['event_id'];
	$ligneEvent1 = $JoueurSommeEvenement[$Ievent]['joueur_event_ref'];
	$ligneEvent2 = $JoueurSommeEvenement[$Ievent]['code'];
	switch ($ligneEvent2) {
		case 0 :
			$indexJoueur = 0;
			while ($indexJoueur < $NbRangeeStats) {
				if (!strcmp($rangeeStats[$indexJoueur][0], $JoueurSommeEvenement[$Ievent]['nom'])) {$rangeeStats[$indexJoueur][1]++;
				}
				$indexJoueur++;
			}
			break;
		case 1 :
			$indexJoueur = 0;
			while ($indexJoueur < $NbRangeeStats) {
				if (!strcmp($rangeeStats[$indexJoueur][0], $JoueurSommeEvenement[$Ievent]['nom'])) {$rangeeStats[$indexJoueur][2]++;
				}
				$indexJoueur++;
			}
			break;
		case 2 :
			break;
		case 3 :
			$indexJoueur = 0;
			$trouveJ=0;
			while($indexJoueur < $NbRangeeStats &&$trouveJ==0) {
				if (($rangeeStats[$indexJoueur][6]== $JoueurSommeEvenement[$Ievent]['joueur_event_ref']) && $JoueurSommeEvenement[$Ievent]['souscode'] == 0) {
					$rangeeStats[$indexJoueur][3]++;
					$trouveJ=1;
				}
				$indexJoueur++;
			}

			break;
	}
	$Ievent++;
}

//////////////////////////////////////////////////
//
// 	Affichage des stats
$Ievent = 0;
$stats = array();
$JSONstring = "{\"joueurs\": [";

while ($Ievent < $NbRangeeStats) {
	if ($rangeeStats[$Ievent][3] != 0 && $rangeeStats[$Ievent][6]<1000000 && $rangeeStats[$Ievent][6]!=null) {
		$stats[$Ievent]['nom'] = $rangeeStats[$Ievent][0];
		$stats[$Ievent]['nbButs'] = $rangeeStats[$Ievent][1];
		$stats[$Ievent]['nbPasses'] = $rangeeStats[$Ievent][2];
		$stats[$Ievent]['pj'] = $rangeeStats[$Ievent][3];
		$stats[$Ievent]['nomEquipe'] = $rangeeStats[$Ievent][4];
		$stats[$Ievent]['ficId'] = $rangeeStats[$Ievent][5];
		$stats[$Ievent]['id'] = $rangeeStats[$Ievent][6];
		$stats[$Ievent]['numero'] = $rangeeStats[$Ievent][7];
		$stats[$Ievent]['ficIdPortrait'] = $rangeeStats[$Ievent][8];

		$JSONstring .= json_encode($stats[$Ievent]) . ",";
	}
	$Ievent++;
}
if ($Ievent != 0)
	$JSONstring = substr($JSONstring, 0, -1);
$JSONstring .= "]}";
//	$JSONstring .= $strQuery;

echo $JSONstring;
?>
