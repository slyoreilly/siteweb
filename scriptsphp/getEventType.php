<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';

$event=Array();
$sportIds=null;
$sportIdArray = [];
if(isset($_POST['sportIds'])){
$sportIds = $_POST["sportIds"];
$decodedSports = json_decode($sportIds, true);
if (is_array($decodedSports)) {
	$sportIdArray = $decodedSports;
}
}

$saisonId =null;

/////////////////////////////////////////////////////////////
// 
//

if(empty($sportIdArray)){
	$rfEventType = mysqli_query($conn,"SELECT * FROM EventType WHERE 1")
	or die(mysqli_error($conn)." Select EventType Sport null"); 
}else{
	$sportString = "";
	foreach($sportIdArray as $sportId){
		$sportId = (int)$sportId;
		$sportString  = $sportString . "SportID = '{$sportId}' OR ";
	}
	if(strlen($sportString)>4){
		$sportString= substr($sportString, 0, -3);
	}
	


	$rfEventType = mysqli_query($conn,"SELECT * FROM EventType WHERE {$sportString} ")
	or die(mysqli_error($conn)." Select EventType Sport set to {$sportIds} "); 
}
while($rangeeEvent=mysqli_fetch_array($rfEventType))
{
	$createdAt = isset($rangeeEvent['CreatedAt']) ? strtotime($rangeeEvent['CreatedAt']) : false;
	$rangeeEvent['CreatedAt'] = ($createdAt !== false) ? (1000 * $createdAt) : null;

	$updatedSource = $rangeeEvent['UpdatedAt'] ?? ($rangeeEvent['UpdateAt'] ?? null);
	$updatedAt = $updatedSource ? strtotime((string)$updatedSource) : false;
	$rangeeEvent['UpdatedAt'] = ($updatedAt !== false) ? (1000 * $updatedAt) : null;
	unset($rangeeEvent['UpdateAt']);

	$event[] = $rangeeEvent;
	
}

mysqli_free_result($rfEventType);
	
echo json_encode($event);;
	
//mysqli_close($conn);

?>
