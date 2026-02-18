<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';

$Position=Array();
$sportIds=null;
if(isset($_POST['sportIds'])){
$sportIds = $_POST["sportIds"];
$sportIdArray = json_decode($sportIds);
}

$saisonId =null;

/////////////////////////////////////////////////////////////
// 
//

if(!empty($sportIds)){
	$rfPosition = mysqli_query($conn,"SELECT * FROM Positions WHERE 1")
	or die(mysqli_error($conn)." Select Positions Sport null"); 
}else{
	$sportString = "";
	foreach($s as $sportIdArray){
		$sportString  = $sportString . "SportID = '{$s}' OR ";
	}
	if(strlen($sportString)>4){
		$sportString= substr($sportString, 0, -3);
	}
	


	$rfPosition = mysqli_query($conn,"SELECT * FROM Positions WHERE {$sportString} ")
	or die(mysqli_error($conn)." Select Positions Sport set to {$sportIds} "); 
}
$vecPositions = Array();

while($rangeePosition=mysqli_fetch_assoc($rfPosition))
{
	$rangeePosition['CreatedAt'] = strtotime($rangeePosition['CreatedAt'])*1000; // convert to unix timestamp (in seconds)
	$rangeePosition['UpdatedAt'] = strtotime($rangeePosition['UpdatedAt'])*1000; // convert to unix timestamp (in seconds)
    $Position[] = $rangeePosition;
}

echo json_encode($Position);
	
//mysqli_close($conn);

?>
