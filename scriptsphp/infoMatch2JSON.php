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
if($matchId=="" OR $matchId==NULL OR $matchId==undefined ){
$matchId = $_POST['matchId'];
	
}
$refParString=true;
if(is_numeric($matchId)){
	if($matchId<1000000){
		$refParString=false;
	}
}


$match = $_POST['match'];
$ligueId = $_POST['ligueId'];


////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

if(is_numeric($match)&&is_numeric($ligueId))
{
		$rEquipeDom = mysqli_query($conn,"SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableArena.nomArena,TableArena.nomGlace ,TableEquipe.ficId AS eqFic
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
								LEFT JOIN TableArena ON
									TableMatch.arenaId=TableArena.arenaId	
								WHERE TableMatch.ligueRef='$ligueId' AND TableMatch.date <= DATE_ADD(CURDATE(), INTERVAL 6 DAY)
								ORDER BY date DESC
								LIMIT $match,1")or die(mysqli_error($conn)); 

	$rEquipeVis = mysqli_query($conn,"SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableArena.nomArena,TableArena.nomGlace, TableEquipe.ficId AS eqFic 
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_vis=TableEquipe.equipe_id
								LEFT JOIN TableArena ON
									TableMatch.arenaId=TableArena.arenaId	
								WHERE TableMatch.ligueRef='$ligueId' AND TableMatch.date <= DATE_ADD(CURDATE(), INTERVAL 6 DAY)
								ORDER BY date DESC
								LIMIT $match,1")or die(mysqli_error($conn)); 
	
}

else if(!is_numeric($matchId)OR $refParString){

	$rEquipeDom = mysqli_query($conn,"SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableArena.nomArena,TableArena.nomGlace, TableEquipe.ficId AS eqFic
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
								LEFT JOIN TableArena ON
									TableMatch.arenaId=TableArena.arenaId	
								WHERE TableMatch.matchIdRef='$matchId'")
or die(mysqli_error($conn)); 


	$rEquipeVis = mysqli_query($conn,"SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableEquipe.ficId AS eqFic 
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_vis=TableEquipe.equipe_id
								WHERE TableMatch.matchIdRef='$matchId'")
or die(mysqli_error($conn)); 
	


	}
else {
			$limBas= $matchId;
					
	$rEquipeDom = mysqli_query($conn,"SELECT TableEquipe.*, Ligue.*, TableMatch.*,TableEquipe.ficId AS eqFic
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
									ORDER BY `match_id` DESC
									LIMIT $limBas , 1")
									or die(mysqli_error($conn)); 


	$rEquipeVis = mysqli_query($conn,"SELECT TableEquipe.*, Ligue.*, TableMatch.*, TableEquipe.ficId AS eqFic 
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								LEFT JOIN TableEquipe
									ON TableMatch.eq_vis=TableEquipe.equipe_id
									ORDER BY `match_id` DESC
									LIMIT $limBas , 1")
									or die(mysqli_error($conn)); 

			
			
		
	
	
}
	

	$equipeDom=mysqli_fetch_assoc($rEquipeDom);
	$equipeVis=mysqli_fetch_assoc($rEquipeVis);
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
	
mysqli_close($conn);

?>
