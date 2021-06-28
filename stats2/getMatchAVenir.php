<?php

require '../scriptsphp/defenvvar.php';

$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
$ligueId = null;
$mavId = null;

if (isset($_POST['mavId'])) {
	$mavId = $_POST['mavId'];
}
if (isset($_POST['ligueId'])) {
	$ligueId = $_POST['ligueId'];
}




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

//						 GROUP BY MatchAVenir.mavId

// Faudrait refaire en cadrant par rapport à la saison en cours plutôt qu'aux équipes abonnées.


								



/////////////////////////

function trouveJoueur($joueurs, $joueurId, $requestedTeam){
	foreach($joueurs as $joueur){
		if($joueur['joueurId']==$joueurId){
			if($joueur['eqId']==$requestedTeam){
				$joueur['presence']='present';
			}else{
				$joueur['presence']='remplace';
			}
			return $joueur;
		}
	}
	return false;
}


function trouveEquipe($joueurs, $requestedTeam){
	$equipe = array();
	foreach($joueurs as $joueur){

			if($joueur['eqId']==$requestedTeam){
				array_push($equipe,$joueur);
			}
	}
	return $equipe;
}


///////////////////////////////////////////////////////////////////////
//
//
//	Faire un array complet avec remplaçants, présents, absents.
//
//
////////////////////////////////////////////////////////////////////////

$joueursLigue = array();
$IJ=0;
$result = mysqli_query($conn, "


              SELECT    DISTINCT J.joueur_id as joueurId, J.NomJoueur as nomJoueur, J.NumeroJoueur as noJoueur, J.position , aEL.equipeId as eqId
						FROM TableMatch as M
                        JOIN abonEquipeLigue as aEL
							ON (M.ligueRef=aEL.ligueId AND M.date BETWEEN aEL.debutAbon AND aEL.finAbon)
                        JOIN abonJoueurEquipe as aJE
							ON (aEL.equipeId=aJE.equipeId AND M.date BETWEEN aJE.debutAbon AND aJE.finAbon)
                        Join TableJoueur as J
                            on(aJE.joueurId = J.joueur_id)
                                            WHERE M.mavId='$mavId'
				
UNION ALL							
											SELECT  DISTINCT J.joueur_id as joueurId, J.NomJoueur as nomJoueur, J.NumeroJoueur as noJoueur, J.position , (0) as eqId
						FROM TableMatch as M
						JOIN abonJoueurLigue as aJL
							ON (M.ligueRef=aJL.ligueId AND M.date BETWEEN aJL.debutAbon AND aJL.finAbon)
                        Join TableJoueur as J
                            on(aJL.joueurId = J.joueur_id)
                             WHERE M.mavId='$mavId'
											
											") or die(mysqli_error($conn));
											while ($r = mysqli_fetch_assoc($result)) 
											{
												$joueursLigue[$IJ] = $r;
												$IJ++;
											}







$retour = mysqli_query($conn, "SELECT TableMatch.*	
						FROM TableMatch
						/*LEFT JOIN TableSaison
							ON (TableMatch.ligueRef=TableSaison.ligueRef)*/
						 WHERE TableMatch.ligueRef='{$ligueId}'
						 /*GROUP BY TableMatch.mavId*/") or die(mysqli_error($conn));
/* AND MatchAVenir.date > (NOW()-INTERVAL 30 DAY) AND TableSaison.dernierMatch>NOW()*/

$vecMatch = array();
$IM = 0;


while ($r = mysqli_fetch_assoc($retour)) {

	if (!is_numeric($mavId) || $r['mavId'] == $mavId) {
		$vecMatch[$IM] = $r;
		$vecMatch[$IM]['cleValeur'] = json_decode($r['cleValeur'], true);
		$vecMatch[$IM]['alDom'] = array();
		$vecMatch[$IM]['alVis'] = array();
		$vecMatch[$IM]['gDom'] = array();
		$vecMatch[$IM]['gVis'] = array();
		$alDom = json_decode($r['alignementDom'], true);
		if (!is_array($alDom)) {
			$alDom = array();
		}
		$alVis = json_decode($r['alignementVis'], true);
		if (!is_array($alVis)) {
			$alVis = array();
		}/*
		for ($a = 0; $a < count($alDom); $a++) {
			//	$vecMatch[$IM]['alDom'][$a]=array();

			$qAlDom = "SELECT joueur_id,NomJoueur,NumeroJoueur,position		
						FROM TableJoueur
						 WHERE joueur_id='{$alDom[$a]}'";
			$rAlDom = mysqli_query($conn, $qAlDom) or die(mysqli_error($conn));
			while ($rangAlDom = mysqli_fetch_assoc($rAlDom)) {
				$vecMatch[$IM]['alDom'][$a] = $rangAlDom;
			}
		}
		for ($a = 0; $a < count($alVis); $a++) {
			//	$vecMatch[$IM]['alVis'][$a]=array();

			$qAlVis = "SELECT joueur_id,NomJoueur,NumeroJoueur,position		
						FROM TableJoueur
						 WHERE joueur_id='{$alVis[$a]}'";
			$rAlVis = mysqli_query($conn, $qAlVis) or die(mysqli_error($conn));
			while ($rangAlVis = mysqli_fetch_assoc($rAlVis)) {
				$vecMatch[$IM]['alVis'][$a] = $rangAlVis;
			}
		}*/

		$qGDom = "SELECT joueur_id ,NomJoueur,NumeroJoueur,position		
						FROM TableJoueur
						 WHERE joueur_id='{$r['gardienDom']}'";
		$rGDom = mysqli_query($conn, $qGDom) or die(mysqli_error($conn));
		while ($rangGDom = mysqli_fetch_assoc($rGDom)) {
			$vecMatch[$IM]['gDom'] = $rangGDom;
		}
		$qGVis = "SELECT joueur_id,NomJoueur,NumeroJoueur,position		
						FROM TableJoueur
						 WHERE joueur_id='{$r['gardienVis']}'";
		$rGVis = mysqli_query($conn, $qGVis) or die(mysqli_error($conn));
		while ($rangGVis = mysqli_fetch_assoc($rGVis)) {
			$vecMatch[$IM]['gVis'] = $rangGVis;
		}


		
			foreach($alDom as $unJoueurId){
		        $joueur = trouveJoueur($joueursLigue,$unJoueurId,$vecMatch[$IM]['eq_dom']);
			    array_push($vecMatch[$IM]['alDom'],$joueur );
			}
		


			foreach($alVis as $unJoueurId){
		        $joueur = trouveJoueur($joueursLigue,$unJoueurId,$vecMatch[$IM]['eq_vis']);
			    array_push($vecMatch[$IM]['alVis'],$joueur );
			}
			

			$regDom = trouveEquipe($joueursLigue, $vecMatch[$IM]['eq_dom']);
			foreach($regDom as $joueurReg){
				if(!trouveJoueur($vecMatch[$IM]['alDom'],$joueurReg['joueurId'],$vecMatch[$IM]['eq_dom'])){
					$joueurReg['presence']='absent';
					array_push($vecMatch[$IM]['alDom'],$joueurReg );
				}
			}

			$regVis = trouveEquipe($joueursLigue, $vecMatch[$IM]['eq_vis']);
			foreach($regVis as $joueurReg){
				if(!trouveJoueur($vecMatch[$IM]['alVis'],$joueurReg['joueurId'],$vecMatch[$IM]['eq_vis'])){
					$joueurReg['presence']='absent';
					array_push($vecMatch[$IM]['alVis'],$joueurReg );
				}
			}








		$IM++;
	}
}








//$adomper= stripslashes(json_encode($vecMatch));
$adomper = json_encode($vecMatch);
echo $adomper;


	//		header("HTTP/1.1 200 OK");
