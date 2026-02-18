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



//////////////////////////////////////////////////////
//
//
//////////////////////////////////////////////////////
$ligueId =null;
$equipeId =null;
	if(isset($_GET["LigueID"])){
$ligueId = $_GET["LigueID"];}
if(isset($_GET["equipeId"])){

$equipeId = $_GET["equipeId"];
}
	// Retrieve all the data from la table

if(is_numeric($ligueId))
{
$resultEvent = mysqli_query($conn,"SELECT Ligue.*,TableSaison.* FROM Ligue 
										JOIN TableSaison
										ON (Ligue.ID_Ligue=TableSaison.ligueRef)
										WHERE ID_Ligue = '{$ligueId}'")
	or die(mysqli_error($conn));
}
else 
{
	if (is_numeric($equipeId))
	{
	$resultEvent = mysqli_query($conn,"SELECT Ligue.*,abonEquipeLigue.*, TableSaison.* FROM Ligue
											JOIN abonEquipeLigue 
										 		ON (abonEquipeLigue.ligueId=Ligue.ID_Ligue)
											JOIN TableSaison
												ON (Ligue.ID_Ligue=TableSaison.ligueRef)
											WHERE equipeId = '{$equipeId}'")
	or die(mysqli_error($conn));  
	}
	else
	{
	$resultEvent = mysqli_query($conn,"SELECT Ligue.cleValeur,Ligue.ID_Ligue, Ligue.Nom_Ligue,Ligue.Lieu,Ligue.Horaire FROM Ligue 
										JOIN TableSaison
										ON (Ligue.ID_Ligue=TableSaison.ligueRef)
										WHERE 1
										GROUP BY Ligue.ID_Ligue, Ligue.Nom_Ligue,Ligue.Lieu,Ligue.Horaire,Ligue.cleValeur")
		or die(mysqli_error($conn));
	}
	}
$Ieq =0;

$Ligues=Array();
$IL=-1;
$IS=0;
$lId=-1;
while($rangeeEv=mysqli_fetch_array($resultEvent))
{
	if($lId!=$rangeeEv['ID_Ligue'])
	{
		$lId=$rangeeEv['ID_Ligue'];
	$IL++;
	$Ligues[$IL]=array();
	$Ligues[$IL]['nomLigue']=$rangeeEv['Nom_Ligue'];
	$Ligues[$IL]['ligueId']=$rangeeEv['ID_Ligue'];
	$Ligues[$IL]['ficId']= isset($rangeeEv['ficId'])? $rangeeEv['ficId']:null;
	$Ligues[$IL]['lieu']= isset($rangeeEv['Lieu'])? $rangeeEv['Lieu']:null;
	$Ligues[$IL]['horaire']= isset($rangeeEv['Horaire'])? $rangeeEv['Horaire']:null;
	$Ligues[$IL]['cleValeur']=json_decode($rangeeEv['cleValeur']);
	$Ligues[$IL]['saisons']=Array();
	
	$IS=0;
	}
	$Ligues[$IL]['saisons'][$IS]['pm']= isset($rangeeEv['premierMatch'])? $rangeeEv['premierMatch']:null;
	$Ligues[$IL]['saisons'][$IS]['dm']= isset($rangeeEv['dernierMatch'])? $rangeeEv['dernierMatch']:null;
	$Ligues[$IL]['saisons'][$IS]['saisonId']= isset($rangeeEv['saisonId'])? $rangeeEv['saisonId']:null;
	$Ligues[$IL]['saisons'][$IS]['type']= isset($rangeeEv['typeSaison'])? $rangeeEv['typeSaison']:null;
	$IS++;	
	
//		if($lId!=$rangeeEv['ID_Ligue'])
//	{	}
	
	
}

$JSONstring = "{\"Ligues\":".json_encode($Ligues)."}" ;

echo $JSONstring;
	
//mysqli_close($conn);

?>
