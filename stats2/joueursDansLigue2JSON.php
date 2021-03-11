<?php

/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
//
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';

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

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");







/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
//
//
function trouveSaisonActiveDeLigueId($ID,$conn) {
	$rfSaison = mysqli_query($conn,"SELECT saisonId FROM TableSaison WHERE ligueRef = {$ID} ORDER BY premierMatch DESC") or die(mysqli_error($conn) . " Select saisonId");
	//echo mysql_result($rfSaison, 0)."\n";
	//$tmp= (mysql_fetch_array($rfSaison));
	//echo $tmp['saisonId']."\n";
	return (mysqli_data_seek($rfSaison, 0));
}
$saisonId="";

$getLigue = $_POST["ligueId"];
if(isset($_POST["saisonId"])){
$saisonId = $_POST["saisonId"];}

if (!strcmp($saisonId, "")&& strcmp($getLigue, ""))// Sp�cifie par la ligue
{
	$saisonId = trouveSaisonActiveDeLigueId($getLigue,$conn);
	//	$saisonId =2;
}
$prSaison = mysqli_query($conn,"SELECT premierMatch, dernierMatch 
						FROM TableSaison 
						WHERE saisonId ='{$saisonId}'") or die(mysqli_error($conn) . "query PM: saisonId: " . $saisonId);

mysqli_data_seek($prSaison, 0);
$row = mysqli_fetch_row($prSaison);
$premierMatch = $row[0];
$dernierMatch =$row[1];

//
$lesMatchs = array();
$I2 = 0;
$resultMatch = mysqli_query($conn,"SELECT * 
							FROM TableMatch 
								WHERE ligueRef={$getLigue}") or die(mysqli_error($conn) . "query Matchs");
								
	
while ($rangeeMatch = mysqli_fetch_array($resultMatch)) {
	if ($rangeeMatch['date'] >= $premierMatch && $rangeeMatch['date'] <= $dernierMatch) {
		$lesMatchs[$I2] = $rangeeMatch['matchIdRef'];
		$I2++;
	}
}

$Im = 0;
$JoueurSommeEvenement = array();
$I0 = 0;

/////////////////  Section avec abonnement, on liste les equipes.

$resultEq = mysqli_query($conn,"SELECT abonEquipeLigue.*  
				FROM abonEquipeLigue
					WHERE  ligueId={$getLigue}") or die(mysqli_error($conn) . "query abon 1");

$eqAbon = array();
$Ieq = 0;
while ($rangeeEq = mysqli_fetch_array($resultEq)) {
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
	$strQuery = "SELECT TableEvenement0.*, abonJoueurEquipe.*,abonEquipeLigue.ligueId, TableEquipe.nom_equipe,TableEquipe.ficId, 
								 TableJoueur.NomJoueur, TableJoueur.NumeroJoueur ,TableJoueur.ficIdPortrait 
				FROM TableEvenement0 
					INNER JOIN TableJoueur 
						 ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id)
					INNER JOIN abonJoueurEquipe 
						 ON (TableEvenement0.joueur_event_ref=abonJoueurEquipe.joueurId)
					INNER JOIN abonEquipeLigue 
						 ON (abonJoueurEquipe.equipeId=abonEquipeLigue.equipeId AND TableEvenement0.equipe_event_id=abonEquipeLigue.equipeId)
						  
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
						
						mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
	$resultEvent = mysqli_query($conn,$strQuery) or die(mysqli_error($conn) . "query gros stock");

	while ($rangeeEv = mysqli_fetch_array($resultEvent)) {
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
	while ($Im < count($lesMatchs)) {
	
	unset($resultEvent);
	unset($rangeeEv);

	//// Section remplaçants: non abonné dans une équipe.
	
	$resultEvent = mysqli_query($conn,"SELECT TableEvenement0.*, TableJoueur.NomJoueur,TableJoueur.joueur_id,TableJoueur.ficIdPortrait, TableJoueur.NumeroJoueur  
				FROM TableEvenement0 
					LEFT JOIN TableJoueur 
						 ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id)
					INNER JOIN abonJoueurLigue 
						 ON (TableEvenement0.joueur_event_ref=abonJoueurLigue.joueurId)
						 		 						 
					WHERE  match_event_id = '{$lesMatchs[$Im]}'
						AND abonJoueurLigue.ligueId = 	{$getLigue}		
					 AND code<10
						") or die(mysqli_error($conn) . "query stats pers");

	while ($rangeeEv = mysqli_fetch_array($resultEvent)) {
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
	$Im++;
	}

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
mysqli_close($conn);
?>
