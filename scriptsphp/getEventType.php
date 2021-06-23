<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';


$sportId=null;
if(isset($_POST['sportId'])){
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

if(IS_NULL($sportId)){
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
	or die(mysqli_error($conn)." Select EventType Sport set to {$sportId} "); 
}

while($rangeeEvent=mysqli_fetch_array($rfEventType))
{
	$event[] = $rangeeEvent;
	
}
	
echo json_encode($event);;
	
mysqli_close($conn);

?>
