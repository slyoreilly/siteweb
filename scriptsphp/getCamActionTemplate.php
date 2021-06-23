<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';


$eventTypeId=null;
if(isset($_POST['eventTypeIds'])){
$eventTypeId = $_POST["eventTypeIds"];
$eventTypeIdArray = json_decode($eventTypeId);
}
$leagueId=null;
if(isset($_POST['leagueIds'])){
$leagueId = $_POST["leagueIds"];
$leagueArray = json_decode($leagueId);
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

$eventTypeString = "";
	foreach($s as $eventTypeIdArray){
		$eventTypeString  = $eventTypeString . "EventTypeId = '{$s}' OR ";
	}
	if(strlen($eventTypeString)>4){
		$eventTypeString= substr($eventTypeString, 0, -3);
	}
	


$leagueString = "";
	foreach($s as $leagueArray){
		$leagueString  = $leagueString . "LeagueId = '{$s}' OR ";
	}
	if(strlen($leagueString)>4){
		$leagueString= substr($leagueString, 0, -3);
	}
	


if(IS_NULL($leagueId)){
	$rfCAT = mysqli_query($conn,"SELECT * FROM CamActionTemplate WHERE LeagueId IS NULL AND  ({$eventTypeString})")
	or die(mysqli_error($conn)." Select EventType leagueId null"); 
}else{
	$qCAT = "SELECT * FROM CamActionTemplate WHERE (LeagueId IS NULL OR {$leagueString}) AND ({$eventTypeString})";
	$rfCAT = mysqli_query($conn,$qCAT)
	or die(mysqli_error($conn)." Select EventType leagueId null :".$qCAT." ||| ".$eventTypeId." ||| ".json_encode($eventTypeIdArray )); 
}

while($rangeeCAT=mysqli_fetch_array($rfCAT))
{
	$camActionTemplate[] = $rangeeCAT;
	
}
	
echo json_encode($camActionTemplate);;
	
mysqli_close($conn);

?>
