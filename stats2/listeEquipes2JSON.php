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
	
//mysqli_close($conn);

?>
