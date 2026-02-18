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

$saisonId =null;

/////////////////////////////////////////////////////////////
// 
//


$rfEventType = mysqli_query($conn,"SELECT * FROM EventTypeDetail WHERE 1")
or die(mysqli_error($conn)." Select EventTypeDetail Sport null"); 

while($rangeeEvent=mysqli_fetch_assoc($rfEventType))
{
	$rangeeEvent['CreatedAt'] = 1000*strtotime($rangeeEvent['CreatedAt']); // convert to unix timestamp (in seconds)
	$rangeeEvent['UpdatedAt'] = 1000*strtotime($rangeeEvent['UpdatedAt']); // convert to unix timestamp (in seconds)
	$event[] = $rangeeEvent;
	
}
	
echo json_encode($event);;
	
//mysqli_close($conn);

?>
