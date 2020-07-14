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

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////
$con=mysqli_connect($db_host, $db_user, $db_pwd);
if (!$con)
    die("Can't connect to database");

if (!mysqli_select_db($con,$database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}
	mysqli_query($con,"SET NAMES 'utf8'");
mysqli_query($con,"SET CHARACTER SET 'utf8'");

//////////////////////////////////////////////////////
//
//
//////////////////////////////////////////////////////
	
$ligueId = $_GET["LigueID"];
$equipeId = $_GET["equipeId"];
	// Retrieve all the data from la table

if(is_numeric($ligueId))
{
$resultEvent = mysqli_query($con,"SELECT Ligue.*,TableSaison.* FROM Ligue 
										JOIN TableSaison
										ON (Ligue.ID_Ligue=TableSaison.ligueRef)
										WHERE ID_Ligue = '{$ligueId}'")
	or die(mysqli_error($con));
}
else 
{
	if (is_numeric($equipeId))
	{
	$resultEvent = mysqli_query($con,"SELECT Ligue.*,abonEquipeLigue.*, TableSaison.* FROM Ligue
											JOIN abonEquipeLigue 
										 		ON (abonEquipeLigue.ligueId=Ligue.ID_Ligue)
											JOIN TableSaison
												ON (Ligue.ID_Ligue=TableSaison.ligueRef)
											WHERE equipeId = '{$equipeId}'")
	or die(mysqli_error($con));  
	}
	else
	{
	$resultEvent = mysqli_query($con,"SELECT Ligue.cleValeur,Ligue.ID_Ligue, Ligue.nomLigue,Ligue.lieu,Ligue.horaire FROM Ligue 
										JOIN TableSaison
										ON (Ligue.ID_Ligue=TableSaison.ligueRef)
										WHERE 1
										GROUP BY Ligue.ID_Ligue, Ligue.nomLigue,Ligue.lieu,Ligue.horaire")
		or die(mysqli_error($con));
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
	$Ligues[$IL]['nomLigue']=$rangeeEv['Nom_Ligue'];
	$Ligues[$IL]['ligueId']=$rangeeEv['ID_Ligue'];
	$Ligues[$IL]['ficId']=$rangeeEv['ficId'];
	$Ligues[$IL]['lieu']=$rangeeEv['Lieu'];
	$Ligues[$IL]['horaire']=$rangeeEv['Horaire'];
	$Ligues[$IL]['cleValeur']=json_decode($rangeeEv['cleValeur']);
	$Ligues[$IL]['saisons']=Array();
	
	$IS=0;
	}
	$Ligues[$IL]['saisons'][$IS]['pm']=$rangeeEv['premierMatch'];
	$Ligues[$IL]['saisons'][$IS]['dm']=$rangeeEv['dernierMatch'];
	$Ligues[$IL]['saisons'][$IS]['saisonId']=$rangeeEv['saisonId'];
	$Ligues[$IL]['saisons'][$IS]['type']=$rangeeEv['typeSaison'];
	$IS++;	
	
//		if($lId!=$rangeeEv['ID_Ligue'])
//	{	}
	
	
}

$JSONstring = "{\"Ligues\":".json_encode($Ligues)."}" ;

echo $JSONstring;
	
mysqli_close($con);

?>
