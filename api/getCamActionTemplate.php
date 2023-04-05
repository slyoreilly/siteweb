<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';


$eventTypeIdArray=array();
if(isset($_POST['eventTypeIds'])){
$eventTypeIdArray = json_decode($_POST["eventTypeIds"]);
//$eventTypeIdArray = json_decode($eventTypeId);
}
$leagueArray=array();
if(isset($_POST['leagueIds'])){
$leagueArray = json_decode($_POST["leagueIds"]);
//$leagueArray = json_decode($leagueId);
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
	foreach($eventTypeIdArray as $s){
		$eventTypeString  = $eventTypeString . "EventTypeId = '{$s}' OR ";
	}
	if(strlen($eventTypeString)>4){
		$eventTypeString= substr($eventTypeString, 0, -3);
	}
	


$leagueString = "";
	foreach($leagueArray as $s){
		$leagueString  = $leagueString . "LeagueId = '{$s}' OR ";
	}
	if(strlen($leagueString)>4){
		$leagueString= substr($leagueString, 0, -3);
	}
	
	$camActionTemplate=null;

if(count($leagueArray)==0){
	$secondProp = "";
	if(!empty($eventTypeString)){
			$secondProp ="AND  ({$eventTypeString})";}

	$qCAT = "SELECT * FROM CamActionTemplate WHERE LeagueId = 0 {$secondProp}";
	$rfCAT = mysqli_query($conn, $qCAT)
	or die(mysqli_error($conn)." Select EventType leagueArray null".$qCAT." ||| ".$eventTypeIdArray); 
}else{
	$qCAT = "SELECT * FROM CamActionTemplate WHERE (LeagueId =0 OR {$leagueString}) AND ({$eventTypeString})";
	$rfCAT = mysqli_query($conn, $qCAT)
	or die(mysqli_error($conn)." Select EventType leagueArray not null :".$qCAT." ||| ".$eventTypeIdArray." ||| ".$leagueArray); 
}

$camActionTemplate= Array();

while($rangeeCAT=mysqli_fetch_assoc($rfCAT))
{
	$rangeeCAT['CreatedAt'] = 1000*strtotime($rangeeCAT['CreatedAt']); // convert to unix timestamp (in seconds)
	$rangeeCAT['UpdatedAt'] = 1000*strtotime($rangeeCAT['UpdatedAt']); // convert to unix timestamp (in seconds)
	$camActionTemplate[] = $rangeeCAT;
	
}

echo json_encode($camActionTemplate);
	
mysqli_close($conn);

?>
