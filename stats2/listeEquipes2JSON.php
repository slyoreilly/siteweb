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


//$equipeId = $_POST["equipeId"];
$ligueId = $_POST["ligueId"];
	

//	if($equipeId==null||$equipeId==undefined)
//	{
		
		$qEq ="SELECT * FROM TableEquipe
								INNER JOIN abonEquipeLigue
									ON (equipeId=equipe_id) 
									WHERE ligueId = '{$ligueId}'";
$resultEquipe = mysqli_query($conn, $qEq)
or die(mysqli_error($conn));  
		
		$equipe=array();
while($rangeeEquipe=mysqli_fetch_assoc($resultEquipe))
{
	$equipe[]=$rangeeEquipe;
}
		
		
		
//	}else{
			/*	$qEq ="SELECT * FROM {$tableEquipe} WHERE equipe_id = '{$equipeId}'";
		
$resultEquipe = mysql_query($qEq)
or die(mysql_error());  	

while($rangeeEquipe=mysql_fetch_assoc($resultEquipe))
{
	$equipe[]=$rangeeEquipe;
}
*/

		
//	}
	
//	echo $qEq;
echo json_encode($equipe);
	
mysqli_close($conn);

?>
