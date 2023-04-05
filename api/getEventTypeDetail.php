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

$saisonId =null;

/////////////////////////////////////////////////////////////
// 
//

if(!empty($sportIds)){
	$rfEventType = mysqli_query($conn,"SELECT * FROM EventTypeDetail WHERE 1")
	or die(mysqli_error($conn)." Select EventTypeDetail Sport null"); 
}else{
	$sportString = "";
	foreach($s as $sportIdArray){
		$sportString  = $sportString . "SportID = '{$s}' OR ";
	}
	if(strlen($sportString)>4){
		$sportString= substr($sportString, 0, -3);
	}
	


	$rfEventType = mysqli_query($conn,"SELECT * FROM EventTypeDetail WHERE {$sportString} ")
	or die(mysqli_error($conn)." Select EventTypeDetail Sport set to {$sportIds} "); 
}
while($rangeeEvent=mysqli_fetch_assoc($rfEventType))
{
	$rangeeEvent['CreatedAt'] = 1000*strtotime($rangeeEvent['CreatedAt']); // convert to unix timestamp (in seconds)
	$rangeeEvent['UpdateAt'] = 1000*strtotime($rangeeEvent['UpdateAt']); // convert to unix timestamp (in seconds)
	$event[] = $rangeeEvent;
	
}
	
echo json_encode($event);;
	
mysqli_close($conn);

?>
