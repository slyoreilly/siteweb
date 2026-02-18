<?php

require '../scriptsphp/defenvvar.php';

/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////



$search = $_POST['searchString'];
$type =null;
if(isset($_POST['typeRecherche'])){
$type = $_POST['typeRecherche'];}



$ligue = array();
	$rLigue = mysqli_query($conn,"SELECT  Ligue.*
								FROM Ligue
								WHERE Nom_Ligue LIKE '%".$search."%'
								 AND cleValeur NOT RLIKE '\"statut\":\"efface\"|\"statut\":\"secret\"'
								" )
or die(mysqli_error($conn)); 
$IL=0;
	while($rangLigue=mysqli_fetch_assoc($rLigue)){
		$ligue[$IL]['id']=$rangLigue['ID_Ligue'];
		$ligue[$IL]['nom']=$rangLigue['Nom_Ligue'];
		$IL++;
	}
	
$joueur = array();
	$rJoueur = mysqli_query($conn,"SELECT  TableJoueur.*
								FROM TableJoueur
								WHERE NomJoueur LIKE '%".$search."%'")
or die(mysqli_error($conn)); 
$IJ=0;
	while($rangLigue=mysqli_fetch_assoc($rJoueur)){
		$joueur[$IJ]['id']=$rangLigue['joueur_id'];
		$joueur[$IJ]['nom']=$rangLigue['NomJoueur'];
		$IJ++;
	}
	
	$match = array();
	if(strcmp($type, "match")==0){

	$datedeb = $search." 00:00:00.000";
	$datefin = $search." 23:59:59.999";
		$rMatch = mysqli_query($conn,"SELECT  TableMatch.*, Ligue.Nom_Ligue, TEdom.nom_equipe As eqDom, TEvis.nom_equipe As eqVis
								FROM TableMatch
								JOIN Ligue
									ON (TableMatch.ligueRef=Ligue.ID_Ligue)
								JOIN TableEquipe TEdom
									ON (TEdom.equipe_id=TableMatch.eq_dom)
								JOIN TableEquipe TEvis
									ON (TEvis.equipe_id=TableMatch.eq_vis)
								WHERE date >='{$datedeb}'
								AND date <='{$datefin}'")
						

								
or die(mysqli_error($conn)); 
$IM=0;
	while($rangMatch=mysqli_fetch_assoc($rMatch)){
		$match[$IM]['date']=$rangMatch['date'];
		$match[$IM]['nom']=$rangMatch['Nom_Ligue'];
		$match[$IM]['matchId']=$rangMatch['matchIdRef'];
		$match[$IM]['eqDom']=$rangMatch['eqDom'];
		$match[$IM]['eqVis']=$rangMatch['eqVis'];
				$IM++;
	}
	
	}
	
	/*
$match = array();
	$rMatch = mysql_query("SELECT  TableMatch.*
								FROM TableMatch
								WHERE date LIKE '%".$search."%'")
or die(mysql_error()); 
$IM=0;
	while($rangLigue=mysql_fetch_assoc($rMatch)){
		$match[$IM]['id']=$rangLigue['match_id'];
		$match[$IM]['nom']=$rangLigue['matchIdRef'];
		$IM++;
	}*/
	
	
	
	
	

$resultat =array();
$resultat['ligue']=$ligue;
$resultat['joueur']=$joueur;
$resultat['match']=$match;
//$resultat['match']=$match;
	
echo json_encode($resultat);
	//mysqli_close($conn);

?>
