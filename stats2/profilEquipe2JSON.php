<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////
require '../scriptsphp/defenvvar.php';

$equipeId = $_GET["equipeId"];


	$resultEquipe = mysqli_query($conn,"SELECT * FROM TableEquipe WHERE equipe_id = '{$equipeId}'")
	or die(mysqli_error($conn));  

$liste=array();

$liste = array('Equipes'=>array());

while($rangeeEv=mysqli_fetch_array($resultEquipe))
{
 array_push($liste['Equipes'],
				 array(
					 "equipeId"=>$rangeeEv['equipe_id'], 
					 "nomEquipe"=>$rangeeEv['nom_equipe'], 
					 "ficId"=>$rangeeEv['ficId'], 
					 "logo"=>$rangeeEv['logo']));
}


//echo json_encode($Sommaire);
echo json_encode($liste);
	
//mysqli_close($conn);

?>
