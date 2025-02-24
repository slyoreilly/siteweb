<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';

$event=Array();
$sportIds=null;
if(isset($_POST['sportIds'])){
$sportIds = $_POST["sportIds"];
$sportIdArray = json_decode($sportIds);
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

$saisonId =null;

/////////////////////////////////////////////////////////////
// 
//

if(!empty($sportIds)){
	$rfEventType = mysqli_query($conn,"SELECT * FROM EventType WHERE 1")
	or die(mysqli_error($conn)." Select EventType Sport null"); 
}else{
	$sportString = "";
	foreach($s as $sportIdArray){
		$sportString  = $sportString . "SportID = '{$s}' OR ";
	}
	if(strlen($sportString)>4){
		$sportString= substr($sportString, 0, -3);
	}
	


	$rfEventType = mysqli_query($conn,"SELECT * FROM EventType WHERE {$sportString} ")
	or die(mysqli_error($conn)." Select EventType Sport set to {$sportIds} "); 
	
}

//$vecEvent = array();
while($rangeeEvent=mysqli_fetch_assoc($rfEventType))
{
	$rangeeEvent['CreatedAt'] = strtotime($rangeeEvent['CreatedAt']); // convert to unix timestamp (in seconds)
    $rangeeEvent['CreatedAt'] = 1000 * $rangeeEvent['CreatedAt']; // convert seconds to milliseconds
	$rangeeEvent['UpdatedAt'] = strtotime($rangeeEvent['UpdatedAt']); // convert to unix timestamp (in seconds)
    $rangeeEvent['UpdatedAt'] = 1000 * $rangeeEvent['UpdatedAt']; // convert seconds to milliseconds
	$event[] = $rangeeEvent;
//	array_push($vecEvent, $event);
}
	
echo json_encode($event);;
	
mysqli_close($conn);

?>
