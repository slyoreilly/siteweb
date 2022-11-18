<?php
////////////////////////////////////////////////////////////
//
//	getEquipeDeMatchId.php
//	Est appellé dans EquipeRepository.kt
//
//
////////////////////////////////////////////////////////////


require '../scriptsphp/defenvvar.php';



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

$matchID = null;
$domicile =null;
$visiteur =null;
$date = null;
$ligueId = null;
$arenaId = null;


if(isset($_POST['matchId'])){$matchID = $_POST['matchId'];}
if(isset($_POST['domicile'])){$matchID = $_POST['domicile'];}
if(isset($_POST['visiteur'])){$matchID = $_POST['visiteur'];}
if(isset($_POST['date'])){$matchID = $_POST['date'];}
if(isset($_POST['ligueId'])){$matchID = $_POST['ligueId'];}
if(isset($_POST['arenaId'])){$matchID = $_POST['arenaId'];}
	
//////////////////////////////////////////////

$resultEquipe = mysqli_query($conn,"SELECT TableMatch.*, TEdom.nom_equipe AS NEdom,TEvis.nom_equipe AS NEvis, TEdom.equipe_id AS eqDomId,TEvis.equipe_id AS eqVisId,
 TEdom.logo AS logoDom,TEvis.logo AS logoVis, TEdom.couleur1 AS couleur1Dom,TEvis.couleur1 AS couleur1Vis, TEdom.cleValeur AS cvDom,TEvis.cleValeur AS cvVis,
 TEdom.dernierMAJ AS dMAJDom, TEvis.dernierMAJ AS dMAJVis
									 FROM TableMatch 
									JOIN TableEquipe AS TEdom 
										ON (TableMatch.eq_dom=TEdom.equipe_id)
									JOIN TableEquipe AS TEvis
										ON (TableMatch.eq_vis=TEvis.equipe_id)
									WHERE matchIdRef = '{$matchID}'")
or die(mysqli_error($conn));  


if(mysqli_num_rows($resultEquipe)>0){

	while($rangeeEq=mysqli_fetch_array($resultEquipe))
	{
		$equipeDom=array();
		$equipeVis=array();
		$mDate=$rangeeEq['date'];
		$equipeDom['id']=$rangeeEq['eqDomId'];
		$equipeDom['nom']=$rangeeEq['NEdom'];
		$equipeDom['logo']=$rangeeEq['logoDom'];
		$equipeDom['couleur1']=$rangeeEq['couleur1Dom'];
		$equipeDom['cleValeur']=$rangeeEq['cvDom'];
		$equipeDom['dernierMAJ']=$rangeeEq['dMAJDom'];
		$equipeDom['joueursReg'] = array();
		$equipeVis['id']=$rangeeEq['eqVisId'];
		$equipeVis['nom']=$rangeeEq['NEvis'];
		$equipeVis['logo']=$rangeeEq['logoVis'];
		$equipeVis['couleur1']=$rangeeEq['couleur1Vis'];
		$equipeVis['cleValeur']=$rangeeEq['cvVis'];
		$equipeVis['dernierMAJ']=$rangeeEq['dMAJVis'];
		$equipeVis['joueursReg'] = array();

		$mLigueId = $rangeeEq['ligueRef'];
		$mArenaId = $rangeeEq['arenaId'];
	}



	$qJoueursRegDom ="SELECT TableJoueur.* FROM TableJoueur 
	JOIN abonJoueurEquipe
		ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId) 
		WHERE abonJoueurEquipe.equipeId = '{$mEqDomId}'
		AND (abonJoueurEquipe.finAbon>='{$mDate}'
				AND abonJoueurEquipe.debutAbon<='{$mDate}')";


	$resultJoueursDom = mysqli_query($conn,$qJoueursRegDom) or die(mysqli_error($conn));  
	while($rangeeJD=mysqli_fetch_array($resultJoueursDom))
	{
		$unJoueur=array();
		$unJoueur['nom']=$rangeeJD['NomJoueur'];
		$unJoueur['numero']=$rangeeJD['NumeroJoueur'];
		$unJoueur['position']=$rangeeJD['position'];
		$unJoueur['dernierMAJ']=$rangeeJD['dernierMAJ'];
		$unJoueur['SyncKey']=$rangeeJD['joueur_id'];
		$unJoueur['EquipeComId']=$rangeeJD['equipeId'];
		array_push($equipeDom['joueursReg'],$unJoueur);
	}

	$qJoueursRegVis ="SELECT TableJoueur.* FROM TableJoueur 
	JOIN abonJoueurEquipe
		ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId) 
		WHERE abonJoueurEquipe.equipeId = '{$mEqVisId}'
		AND (abonJoueurEquipe.finAbon>='{$mDate}'
				AND abonJoueurEquipe.debutAbon<='{$mDate}')";


	$resultJoueursVis = mysqli_query($conn,$qJoueursRegVis) or die(mysqli_error($conn));  
	while($rangeeJV=mysqli_fetch_array($resultJoueursVis))
	{
		$unJoueur=array();
		$unJoueur['nom']=$rangeeJV['NomJoueur'];
		$unJoueur['numero']=$rangeeJV['NumeroJoueur'];
		$unJoueur['position']=$rangeeJV['position'];
		$unJoueur['dernierMAJ']=$rangeeJV['dernierMAJ'];
		$unJoueur['SyncKey']=$rangeeJV['joueur_id'];
		$unJoueur['EquipeComId']=$rangeeJV['equipeId'];
		array_push($equipeVis['joueursReg'],$unJoueur);
	}



	//$JSONstring .="\"equipes\": ".json_encode($equipe)."}";

	//echo json_encode($Sommaire);

	$retour = array();


	$retour['domicile']=$equipeDom;
	$retour['visiteur']=$equipeVis;
	$retour['date']=$mDate;
	$retour['ligueId']=$mLigueId;
	$retour['arenaId']=$mArenaId;
} else {

	$resultDom = mysqli_query($conn,"SELECT *  
										FROM TableEquipe 
									   WHERE equipe_id = '{$domicile}'");

	while($rangeeDom=mysqli_fetch_array($resultDom))
	{
		$equipeDom=array();
		$equipeDom['id']=$rangeeDom['equipe_id'];
		$equipeDom['nom']=$rangeeDom['nom_equipe'];
		$equipeDom['logo']=$rangeeDom['logo'];
		$equipeDom['couleur1']=$rangeeDom['couleur1'];
		$equipeDom['cleValeur']=$rangeeDom['cleValeur'];
		$equipeDom['dernierMAJ']=$rangeeDom['denierMAJ'];
		$equipeDom['joueursReg'] = array();
		$mLigueId = $ligueId;
	}

	$resultVis = mysqli_query($conn,"SELECT *  
										FROM TableEquipe 
									   WHERE equipe_id = '{$visiteur}'");

	while($rangeeVis=mysqli_fetch_array($resultVis))
	{
		$equipeVis=array();
		$equipeVis['id']=$rangeeVis['equipe_id'];
		$equipeVis['nom']=$rangeeVis['nom_equipe'];
		$equipeVis['logo']=$rangeeVis['logo'];
		$equipeVis['couleur1']=$rangeeVis['couleur1'];
		$equipeVis['cleValeur']=$rangeeVis['cleValeur'];
		$equipeVis['dernierMAJ']=$rangeeVis['denierMAJ'];
		$equipeVis['joueursReg'] = array();

		$mLigueId = $ligueId;
	}



	$qJoueursRegDom ="SELECT TableJoueur.* FROM TableJoueur 
	JOIN abonJoueurEquipe
		ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId) 
		WHERE abonJoueurEquipe.equipeId = '{$domicile}'
		AND (abonJoueurEquipe.finAbon>='{$date}'
				AND abonJoueurEquipe.debutAbon<='{$date}')";


	$resultJoueursDom = mysqli_query($conn,$qJoueursRegDom) or die(mysqli_error($conn));  
	while($rangeeJD=mysqli_fetch_array($resultJoueursDom))
	{
		$unJoueur=array();
		$unJoueur['nom']=$rangeeJD['NomJoueur'];
		$unJoueur['numero']=$rangeeJD['NumeroJoueur'];
		$unJoueur['position']=$rangeeJD['position'];
		$unJoueur['dernierMAJ']=$rangeeJD['dernierMAJ'];
		$unJoueur['SyncKey']=$rangeeJD['joueur_id'];
		$unJoueur['EquipeComId']=$rangeeJD['equipeId'];
		array_push($equipeDom['joueursReg'],$unJoueur);
	}

	$qJoueursRegVis ="SELECT TableJoueur.* FROM TableJoueur 
	JOIN abonJoueurEquipe
		ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId) 
		WHERE abonJoueurEquipe.equipeId = '{$visiteur}'
		AND (abonJoueurEquipe.finAbon>='{$date}'
				AND abonJoueurEquipe.debutAbon<='{$date}')";


	$resultJoueursVis = mysqli_query($conn,$qJoueursRegVis) or die(mysqli_error($conn));  
	while($rangeeJV=mysqli_fetch_array($resultJoueursVis))
	{
		$unJoueur=array();
		$unJoueur['nom']=$rangeeJV['NomJoueur'];
		$unJoueur['numero']=$rangeeJV['NumeroJoueur'];
		$unJoueur['position']=$rangeeJV['position'];
		$unJoueur['dernierMAJ']=$rangeeJV['dernierMAJ'];
		$unJoueur['SyncKey']=$rangeeJV['joueur_id'];
		$unJoueur['EquipeComId']=$rangeeJV['equipeId'];
		array_push($equipeVis['joueursReg'],$unJoueur);
	}



	//$JSONstring .="\"equipes\": ".json_encode($equipe)."}";

	//echo json_encode($Sommaire);

	$retour = array();


	$retour['domicile']=$equipeDom;
	$retour['visiteur']=$equipeVis;
	$retour['date']=$date;
	$retour['ligueId']=$ligueId;
	$retour['arenaId']=$arenaId;





}

echo json_encode($retour);
	


?>
