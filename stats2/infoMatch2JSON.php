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

$matchId = $_GET['matchId'];

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}
	mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");


	$rEquipeDom = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*,TableEquipe.ficId AS eqFic
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
								WHERE TableMatch.matchIdRef='$matchId'")
or die(mysql_error()); 
	$equipeDom=mysql_fetch_assoc($rEquipeDom);

	$rEquipeVis = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableEquipe.ficId AS eqFic 
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_vis=TableEquipe.equipe_id
								WHERE TableMatch.matchIdRef='$matchId'")
or die(mysql_error()); 
	$equipeVis=mysql_fetch_assoc($rEquipeVis);



	//////////////////////////////////////////////////
	//
	// 	�crit JSON
	$cV = stripslashes($equipeVis['cleValeur']);
	if(strlen($cV)==0)
		$cV="\"\"";

	$JSONstring = "{\"ligueNom\": \"". $equipeDom['ID_Ligue']."\",";
	$JSONstring .= "\"ligueId\": \"". $equipeDom['ligueRef']."\",";
	$JSONstring .= "\"equipeNomDom\": \"". $equipeDom['nomEquipe']."\",";
	$JSONstring .= "\"equipeVilleDom\": \"". $equipeDom['ville']."\",";
		$JSONstring .= "\"equipeIdDom\": \"". $equipeDom['eq_dom']."\",";
	$JSONstring .= "\"equipeScoreDom\": \"". $equipeDom['score_dom']."\",";
	$JSONstring .= "\"equipeFicIdDom\": \"". $equipeDom['eqFic']."\",";
	$JSONstring .= "\"equipeNomVis\": \"". $equipeVis['nomEquipe']."\",";
	$JSONstring .= "\"equipeVilleVis\": \"". $equipeVis['ville']."\",";
		$JSONstring .= "\"equipeIdVis\": \"". $equipeDom['eq_vis']."\",";
	$JSONstring .= "\"equipeScoreVis\": \"". $equipeDom['score_vis']."\",";
	$JSONstring .= "\"equipeFicIdVis\": \"". $equipeVis['eqFic']."\",";
	$JSONstring .= "\"cleValeur\": ".$cV.",";
	$JSONstring .= "\"date\": \"". $equipeDom['date']."\"}";

	
echo $JSONstring;
	

?>
