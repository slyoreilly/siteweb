<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';

$matchId = $_GET['matchId'];

$match = $_POST['match'];
$ligueId = $_POST['ligueId'];


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

if(is_numeric($match)&&is_numeric($ligueId))
{
		$rEquipeDom = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableArena.nomArena,TableArena.nomGlace ,TableEquipe.ficId AS eqFic
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
								LEFT JOIN TableArena ON
									TableMatch.arenaId=TableArena.arenaId	
								WHERE TableMatch.ligueRef='$ligueId'
								ORDER BY date DESC
								LIMIT $match,1")or die(mysql_error()); 

	$rEquipeVis = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableArena.nomArena,TableArena.nomGlace, TableEquipe.ficId AS eqFic 
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_vis=TableEquipe.equipe_id
								LEFT JOIN TableArena ON
									TableMatch.arenaId=TableArena.arenaId	
								WHERE TableMatch.ligueRef='$ligueId'
								ORDER BY date DESC
								LIMIT $match,1")or die(mysql_error()); 
	
}

else if(!is_numeric($matchId)){

	$rEquipeDom = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableArena.nomArena,TableArena.nomGlace, TableEquipe.ficId AS eqFic
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
								LEFT JOIN TableArena ON
									TableMatch.arenaId=TableArena.arenaId	
								WHERE TableMatch.matchIdRef='$matchId'")
or die(mysql_error()); 


	$rEquipeVis = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableEquipe.ficId AS eqFic 
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_vis=TableEquipe.equipe_id
								WHERE TableMatch.matchIdRef='$matchId'")
or die(mysql_error()); 
	


	}
else {
			$limBas= $matchId;
					
	$rEquipeDom = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*,TableEquipe.ficId AS eqFic
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
									ORDER BY `match_id` DESC
									LIMIT $limBas , 1")
									or die(mysql_error()); 


	$rEquipeVis = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableEquipe.ficId AS eqFic 
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_vis=TableEquipe.equipe_id
									ORDER BY `match_id` DESC
									LIMIT $limBas , 1")
									or die(mysql_error()); 

			
			
		
	
	
}
	

	$equipeDom=mysql_fetch_assoc($rEquipeDom);
	$equipeVis=mysql_fetch_assoc($rEquipeVis);
	//////////////////////////////////////////////////
	//
	// 	�crit JSON
	$cV = stripslashes($equipeVis['cleValeur']);
	if(strlen($cV)==0)
		$cV="\"\"";	

	$JSONstring = "{\"ligueNom\": \"". $equipeDom['Nom_Ligue']."\",";
	$JSONstring .= "\"ligueId\": \"". $equipeDom['ligueRef']."\",";
	$JSONstring .= "\"equipeNomDom\": \"". $equipeDom['nom_equipe']."\",";
	$JSONstring .= "\"equipeVilleDom\": \"". $equipeDom['ville']."\",";
		$JSONstring .= "\"equipeIdDom\": \"". $equipeDom['eq_dom']."\",";
	$JSONstring .= "\"equipeScoreDom\": \"". $equipeDom['score_dom']."\",";
	$JSONstring .= "\"equipeFicIdDom\": \"". $equipeDom['eqFic']."\",";
	$JSONstring .= "\"equipeCouleurDom\": \"". $equipeDom['logo']."\",";
	$JSONstring .= "\"equipeNomVis\": \"". $equipeVis['nom_equipe']."\",";
		$JSONstring .= "\"equipeVilleVis\": \"". $equipeVis['ville']."\",";
	$JSONstring .= "\"equipeIdVis\": \"". $equipeDom['eq_vis']."\",";
	$JSONstring .= "\"equipeScoreVis\": \"". $equipeDom['score_vis']."\",";
	$JSONstring .= "\"equipeFicIdVis\": \"". $equipeVis['eqFic']."\",";
	$JSONstring .= "\"equipeCouleurVis\": \"". $equipeVis['logo']."\",";
	$JSONstring .= "\"nomArena\": \"". $equipeDom['nomArena']."\",";
	$JSONstring .= "\"nomGlace\": \"". $equipeDom['nomGlace']."\",";
	$JSONstring .= "\"arenaId\": \"". $equipeDom['arenaId']."\",";
	$JSONstring .= "\"statut\": \"". $equipeDom['statut']."\",";
	$JSONstring .= "\"matchId\": \"". $equipeVis['matchIdRef']."\",";
	$JSONstring .= "\"noMatchId\": \"". $equipeVis['match_id']."\",";
	$JSONstring .= "\"cleValeur\": ".$cV.",";
	$JSONstring .= "\"date\": \"". $equipeDom['date']."\"}";


	
echo $JSONstring;
	

?>
