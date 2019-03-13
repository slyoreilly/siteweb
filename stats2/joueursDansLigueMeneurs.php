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

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, "utf8");



$getLigue = $_POST["ligueId"];
$saisonId = $_POST["saisonId"];

	
if($saisonId=="null"||$saisonId=="undefined"||$saisonId=="")// Sp�cifie par la saison
	{
		$rfSaison = mysqli_query($conn,"SELECT saisonId FROM TableSaison WHERE ligueRef = '{$getLigue}' ORDER BY premierMatch DESC LIMIT 0,1")
or die(mysqli_error($conn)." Select saisonId"); 

while($rangeeSaison=mysqli_fetch_array($rfSaison))
{
	$saisonId= $rangeeSaison['saisonId'];
	
}
		
		}

//echo $saisonId;
$prSaison = mysqli_query($conn,"SELECT premierMatch 
						FROM TableSaison 
						WHERE saisonId ='{$saisonId}'") or die(mysqli_error($conn) . "query PM: saisonId: " . $saisonId);
						while($rang = mysqli_fetch_array($prSaison)){
							$premierMatch= $rang[0];
						}
$drSaison = mysqli_query($conn,"SELECT dernierMatch FROM TableSaison where saisonId ='{$saisonId}'") or die(mysqli_error($conn) . "query DM");
while($rang = mysqli_fetch_array($drSaison)){
	$dernierMatch= $rang[0];
}

//
$lesMatchs = array();
$I2 = 0;
$resultMatch = mysqli_query($conn,"SELECT * 
							FROM TableMatch 
								WHERE ligueRef={$getLigue}") or die(mysqli_error($conn) . "query Matchs");

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

/*
 while ($rangeeMatch = mysql_fetch_array($resultMatch)) {
 if ($rangeeMatch['date'] >= $premierMatch && $rangeeMatch['date'] <= $dernierMatch) {
 $lesMatchs[$I2] = $rangeeMatch['matchIdRef'];
 $I2++;
 }
 }*/

$Im = 0;
$JoueurSommeEvenement = array();
$I0 = 0;
$vecTemps= Array();
$vecTemps['depart'] = time();
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
unset($evenement);
$evenement = Array();
$pmts = strtotime($premierMatch)*1000;
$dmts = strtotime($dernierMatch)*1000;


//	 match_event_id = '{$lesMatchs[$Im]}' AND
$strQuery = "

SELECT TableEvenement0.*, abJoEq.nom_equipe,abJoEq.ficId, 								
								 TableJoueur.NomJoueur, TableJoueur.NumeroJoueur ,TableJoueur.ficIdPortrait 
				FROM TableEvenement0 		
					INNER JOIN TableMatch 	
						 ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)
					INNER JOIN TableJoueur 			
						 ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id)	
					LEFT JOIN ( 			
						SELECT equipeId,joueurId, TableEquipe.nom_equipe, TableEquipe.ficId FROM abonJoueurEquipe
                        LEFT JOIN TableEquipe			
							ON (abonJoueurEquipe.equipeId=TableEquipe.equipe_id)
						WHERE		
							debutAbon<='{$dateAbon}'		
								AND finAbon>'{$dateAbon}') AS abJoEq	
					ON (TableJoueur.joueur_id=abJoEq.joueurId)			
						WHERE		
						  TableMatch.ligueRef = 	{$getLigue}		
						AND chrono>'{$pmts}'		
						AND chrono<'{$dmts}'		
						AND code<10		
						GROUP BY event_id		
";
/*
					INNER JOIN ( 			
						SELECT equipeId,joueurId FROM abonJoueurEquipe		
						WHERE		
						debutAbon<='{$dateAbon}'		
							AND finAbon>'{$dateAbon}') AS abJoEq	
						 ON (TableEvenement0.joueur_event_ref=abJoEq.joueurId)		
					INNER JOIN ( 			
						SELECT equipeId,ligueId, permission FROM abonEquipeLigue 		
						WHERE		
						debutAbon<='{$dateAbon}'		
							AND finAbon>'{$dateAbon}' AND permission<31) AS abEqLi	
						 ON (abJoEq.equipeId=abEqLi.equipeId AND TableEvenement0.equipe_event_id=abEqLi.equipeId)	
*/

//echo $strQuery;

mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
$resultEvent = mysqli_query($conn,$strQuery) or die(mysqli_error($conn) . "query gros stock");
$vecTemps['postQ1'] = time();
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
	//if (in_array($rangeeEv['joueur_event_ref'], $joueurs) == false) {
	array_push($evenement, $JoueurSommeEvenement[$I0]['event_id']);
	//}
	$I0++;
}
//while ($Im < count($lesMatchs)) {
$vecTemps['whileQ1'] = time();
unset($resultEvent);
unset($rangeeEv);

//// Section remplaçants: non abonné dans une équipe.
mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
$resultEvent = mysqli_query($conn,"SELECT TableEvenement0.*, TableJoueur.NomJoueur,TableJoueur.joueur_id,TableJoueur.ficIdPortrait, TableJoueur.NumeroJoueur  
				FROM TableEvenement0 
					LEFT JOIN TableJoueur 
						 ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id)
					INNER JOIN TableMatch 
						 ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)		
					WHERE  
					   chrono>'{$pmts}'
						AND chrono<'{$dmts}'
						AND TableMatch.ligueRef = 	{$getLigue}		
					 AND code<10
					") or die(mysqli_error($conn) . "query stats pers");
					
					/// Ligne retirée: INNER JOIN abonJoueurLigue 
					//	 ON (TableEvenement0.joueur_event_ref=abonJoueurLigue.joueurId) 
					
$vecTemps['postQ2'] = time();
while ($rangeeEv = mysqli_fetch_array($resultEvent)) {
	if (in_array($rangeeEv['event_id'], $evenement) == false) {
		array_push($evenement, $JoueurSommeEvenement[$I0]['event_id']);
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
$vecTemps['whileQ2'] = time();
//$Im++;
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
	/*$Itrouve = 0;
	$boule = 0;
	while ($Itrouve < count($joueursEntres)) {
		if ($joueursEntres[$Itrouve] == $ligneEvent1) {$boule = 1;
		}
		$Itrouve++;
	}*/
	if(in_array($ligneEvent1, $joueursEntres)==false)
//	if ($boule == 0)//joueur pas dans la liste
	{
		$Itrouve=count($joueursEntres);
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
$vecTemps['5'] = time();
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
				if ($rangeeStats[$indexJoueur][6] == $JoueurSommeEvenement[$Ievent]['joueur_event_ref']) {$rangeeStats[$indexJoueur][1]++;
				}
				$indexJoueur++;
			}
			break;
		case 1 :
			$indexJoueur = 0;
			while ($indexJoueur < $NbRangeeStats) {
				if ($rangeeStats[$indexJoueur][6] == $JoueurSommeEvenement[$Ievent]['joueur_event_ref']) {$rangeeStats[$indexJoueur][2]++;
				}
				$indexJoueur++;
			}
			break;
		case 2 :
			break;
		case 3 :
			$indexJoueur = 0;
			$trouveJ = 0;
			while ($indexJoueur < $NbRangeeStats && $trouveJ == 0) {
				if (($rangeeStats[$indexJoueur][6] == $JoueurSommeEvenement[$Ievent]['joueur_event_ref']) && $JoueurSommeEvenement[$Ievent]['souscode'] == 0) {
					$rangeeStats[$indexJoueur][3]++;
					$trouveJ = 1;
				}
				$indexJoueur++;
			}

			break;
	}
	$Ievent++;
}
$vecTemps['6'] = time();
//////////////////////////////////////////////////
//
// 	Affichage des stats
$Ievent = 0;
$stats = array();
$JSONstring = "{\"joueurs\": [";

while ($Ievent < $NbRangeeStats) {
	if ($rangeeStats[$Ievent][3] != 0 && $rangeeStats[$Ievent][6] < 1000000 && $rangeeStats[$Ievent][6] != null) {
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
$vecTemps['7'] = time();
//	$JSONstring .= $strQuery;

echo $JSONstring;
//echo json_encode($vecTemps);
mysqli_close($conn);
?>
