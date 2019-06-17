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


$equipeId = $_GET["equipeId"];


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
	
mysqli_close($conn);

?>
