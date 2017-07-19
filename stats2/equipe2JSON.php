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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$equipeId = $_GET["equipeId"];

if(!function_exists("array_column"))
{

    function array_column($array,$column_name)
    {

        return array_map(function($element) use($column_name){return $element[$column_name];}, $array);

    }

}

$rLiEq = mysql_query("SELECT TableEquipe.*, abonEquipeLigue.* FROM {$tableEquipe}
								JOIN abonEquipeLigue
								ON (TableEquipe.equipe_id=abonEquipeLigue.equipeId) 
								
								WHERE equipe_id = '{$equipeId}'") or die(mysql_error());
//echo "Q1: ".mysql_num_rows($rLiEq)."\n";
$IV = 0;
$lesMatchs = array();
$I2 = 0;
while ($rangeeLiEq = mysql_fetch_array($rLiEq))//  Pour chacun des abonnements de cette équipe à une ligue quelconque:
{
	$resultSaison = mysql_query("SELECT TableSaison.*,Ligue.* FROM TableSaison 
										JOIN Ligue
										ON (TableSaison.ligueRef=Ligue.ID_Ligue)
										WHERE ligueRef = '{$rangeeLiEq['ligueId']}'") or die(mysql_error());
	//echo "Q2: ".mysql_num_rows($resultSaison)."\n";

	while ($rangeeSaison = mysql_fetch_array($resultSaison))// Pour chacune des saison associé à la ligue de l'abonnement visé:
	{
		//$vecStats[$IV]['stats']=Array();
		$stats = Array();
		$reqMatchs = "SELECT TableMatch.*, TableEvenement0.*, TableJoueur.* 
		 		FROM TableEvenement0
		 		JOIN TableMatch
		 			ON (TableMatch.matchIdRef=TableEvenement0.match_event_id)
		 		JOIN TableJoueur
		 			ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id)
		 		WHERE  
					equipe_event_id='{$equipeId}' 
		 			AND chrono>=(UNIX_TIMESTAMP('{$rangeeSaison['premierMatch']}')*1000) 
		 			AND chrono<=(UNIX_TIMESTAMP('{$rangeeSaison['dernierMatch']}')*1000) 
		 			AND TableMatch.ligueRef='{$rangeeSaison['ID_Ligue']}'
		 			AND code<9
		 			AND joueur_event_ref IS NOT NULL
		 			ORDER BY joueur_id";
		$reqAbon = "SELECT abonJoueurEquipe.*, TableJoueur.*
		 		FROM abonJoueurEquipe
		 		JOIN TableJoueur
		 			ON (abonJoueurEquipe.joueurId=TableJoueur.joueur_id)
		 		WHERE  
				equipeId='{$equipeId}'
				AND finAbon>='{$rangeeSaison['premierMatch']}'
				AND debutAbon<='{$rangeeSaison['dernierMatch']}'
					ORDER BY joueurId";

		$rMatch = mysql_query($reqMatchs) or die(mysql_error() . " reqMatchs");
		//	echo "Q3: ".mysql_num_rows($rMatch)."\n";
		$IE = -1;
		$jId = 0;
		while ($rangMatch = mysql_fetch_array($rMatch)) {
			if ($rangMatch['joueur_id'] != $jId) {

				$IE++;
				$stats[$IE] = Array();
				$stats[$IE]['joueurId'] = $rangMatch['joueur_id'];
				$stats[$IE]['nom'] = $rangMatch['NomJoueur'];
				$stats[$IE]['nbButs'] = 0;
				$stats[$IE]['nbPasses'] = 0;
				$stats[$IE]['minPun'] = 0;
				$stats[$IE]['pj'] = 0;
				$jId = $rangMatch['joueur_id'];

			}
			switch($rangMatch['code']) {
				case 0 :
					$stats[$IE]['nbButs']++;
					break;
				case 1 :
					$stats[$IE]['nbPasses']++;
					break;
				case 3 :
					$stats[$IE]['pj']++;
					break;
				case 4 :
					$stats[$IE]['minPun']++;
					break;
			}
		}// Fin du while sur les evenements
		$rAbon = mysql_query($reqAbon) or die(mysql_error() . " reqMatchs");
		//	echo "Q3: ".mysql_num_rows($rMatch)."\n";
		$jId = 0;
		while ($rangAbon = mysql_fetch_array($rAbon)) {
			//print_r(array_column($stats, 'joueurId'));
			$key = array_search($rangAbon['joueurId'], array_column($stats, 'joueurId'));
//			echo $key+"-";

			if ($key == false) {			$IE++;
				$stats[$IE] = Array();
				$stats[$IE]['joueurId'] = $rangAbon['joueurId'];
				$stats[$IE]['nom'] = $rangAbon['NomJoueur'];
				$stats[$IE]['nbButs'] = 0;
				$stats[$IE]['nbPasses'] = 0;
				$stats[$IE]['minPun'] = 0;
				$stats[$IE]['pj'] = 0;
				$jId = $rangAbon['joueurId'];
			}
		}

		if (count($stats) > 0)// SI cette saison est vide, elle ne sera pas comptabilisée
		{
			
			$vecStats[$IV]['ligueId'] = $rangeeSaison['ID_Ligue'];
			$vecStats[$IV]['nom'] = $rangeeSaison['Nom_Ligue'];
			$vecStats[$IV]['saisonId'] = $rangeeSaison['saisonId'];
			$vecStats[$IV]['pm'] = $rangeeSaison['premierMatch'];
			$vecStats[$IV]['dm'] = $rangeeSaison['dernierMatch'];
			$vecStats[$IV]['type'] = $rangeeSaison['typeSaison'];
			$vecStats[$IV]['nom_equipe'] = $rangeeLiEq['nom_equipe'];
			$vecStats[$IV]['logo'] = $rangeeLiEq['logo'];
			$vecStats[$IV]['couleur1'] = $rangeeLiEq['couleur1'];
			$vecStats[$IV]['equipeId'] = $rangeeLiEq['equipe_id'];
			$vecStats[$IV]['joueurs'] = $stats;
			$IV++;
		}

	}

}

//
echo "{\"Saisons\":" . json_encode($vecStats) . "}";
?>
