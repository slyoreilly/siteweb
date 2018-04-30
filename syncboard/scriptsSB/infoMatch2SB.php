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

$matchId = $_POST['matchId'];
$mode = $_POST['mode'];
///  Mode 1: matchId comme index de TableMatch, Mode 2: matchIdRef.

$match = $_POST['match'];
$ligueId = $_POST['ligueId'];

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

if (is_numeric($match) && is_numeric($ligueId)) {
	$rEquipeDom = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*,TableEquipe.ficId AS eqFic,Ligue.cleValeur AS cvLigue
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
								WHERE TableMatch.ligueRef='$ligueId'
								ORDER BY date DESC
								LIMIT $match,1") or die(mysql_error());

	$rEquipeVis = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableEquipe.ficId AS eqFic ,Ligue.cleValeur AS cvLigue
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_vis=TableEquipe.equipe_id
								WHERE TableMatch.ligueRef='$ligueId'
								ORDER BY date DESC
								LIMIT $match,1") or die(mysql_error());

} else if (!is_numeric($matchId)) {

	$rEquipeDom = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*,TableEquipe.ficId AS eqFic,Ligue.cleValeur AS cvLigue
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
								WHERE TableMatch.matchIdRef='{$matchId}'") or die(mysql_error());

	$rEquipeVis = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableEquipe.ficId AS eqFic,Ligue.cleValeur AS cvLigue
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_vis=TableEquipe.equipe_id
								WHERE TableMatch.matchIdRef='{$matchId}'") or die(mysql_error());

} else {
	if ($mode == 1) {
		$rEquipeDom = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*,TableEquipe.ficId AS eqFic,Ligue.cleValeur AS cvLigue
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
								WHERE TableMatch.match_id='{$matchId}'") or die(mysql_error());

	$rEquipeVis = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableEquipe.ficId AS eqFic,Ligue.cleValeur AS cvLigue
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_vis=TableEquipe.equipe_id
								WHERE TableMatch.match_id='{$matchId}'") or die(mysql_error());
	} else {    //mode =2;
		$limBas = $matchId;

		$rEquipeDom = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*,TableEquipe.ficId AS eqFic,Ligue.cleValeur AS cvLigue
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
									ORDER BY `match_id` DESC
									LIMIT $limBas , 1") or die(mysql_error());

		$rEquipeVis = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableEquipe.ficId AS eqFic ,Ligue.cleValeur AS cvLigue
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_vis=TableEquipe.equipe_id
									ORDER BY `match_id` DESC
									LIMIT $limBas , 1") or die(mysql_error());

	}

}

$equipeDom = mysql_fetch_assoc($rEquipeDom);
$equipeVis = mysql_fetch_assoc($rEquipeVis);
//////////////////////////////////////////////////
//
// 	�crit JSON

$rAutreInfo = mysql_query("SELECT *
								FROM TableEvenement0 
									WHERE match_event_id='{$equipeVis['matchIdRef']}'
									ORDER BY code, souscode ASC") or die(mysql_error());
$codeMatch = 0;
$periode = 0;
$chronoPer = 0;

while ($rangAI = mysql_fetch_array($rAutreInfo)) {
	if ($rangAI['code'] == 10) {$codeMatch = $rangAI['souscode'];
	}
	if ($rangAI['code'] == 11) {$periode = $rangAI['souscode'];
		$chronoPer = $rangAI['chrono'];
	}

}

$cV = stripslashes($equipeVis['cleValeur']);
if (strlen($cV) == 0)
	$cV = "\"\"";

$JSONstring = "{\"ligueNom\": \"" . $equipeDom['Nom_Ligue'] . "\",";
$JSONstring .= "\"ligueId\": \"" . $equipeDom['ligueRef'] . "\",";
$JSONstring .= "\"equipeNomDom\": \"" . $equipeDom['nom_equipe'] . "\",";
$JSONstring .= "\"equipeVilleDom\": \"" . $equipeDom['ville'] . "\",";
$JSONstring .= "\"equipeIdDom\": \"" . $equipeDom['eq_dom'] . "\",";
$JSONstring .= "\"equipeScoreDom\": \"" . $equipeDom['score_dom'] . "\",";
$JSONstring .= "\"equipeFicIdDom\": \"" . $equipeDom['eqFic'] . "\",";
$JSONstring .= "\"equipeCouleurDom\": \"" . $equipeDom['logo'] . "\",";
$JSONstring .= "\"equipeNomVis\": \"" . $equipeVis['nom_equipe'] . "\",";
$JSONstring .= "\"equipeVilleVis\": \"" . $equipeVis['ville'] . "\",";
$JSONstring .= "\"equipeIdVis\": \"" . $equipeDom['eq_vis'] . "\",";
$JSONstring .= "\"equipeScoreVis\": \"" . $equipeDom['score_vis'] . "\",";
$JSONstring .= "\"equipeFicIdVis\": \"" . $equipeVis['eqFic'] . "\",";
$JSONstring .= "\"equipeCouleurVis\": \"" . $equipeVis['logo'] . "\",";
$JSONstring .= "\"matchId\": \"" . $equipeVis['matchIdRef'] . "\",";
$JSONstring .= "\"cleValeur\": " . $cV . ",";
$JSONstring .= "\"cvLigue\": " . $equipeVis['cvLigue'] . ",";
$JSONstring .= "\"codeMatch\": " . $codeMatch . ",";
$JSONstring .= "\"periode\": " . $periode . ",";
$JSONstring .= "\"chronoPer\": " . $chronoPer . ",";
$JSONstring .= "\"countUp\": " . true . ",";
$JSONstring .= "\"heure\": " . time() . ",";

$JSONstring .= "\"date\": \"" . $equipeDom['date'] . "\"}";

echo $JSONstring;
?>
