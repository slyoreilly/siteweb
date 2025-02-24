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

while($rangeeCAT=mysqli_fetch_array($rfCAT))
{
	$camActionTemplate[] = $rangeeCAT;
	
}

echo json_encode($camActionTemplate);
	
mysqli_close($conn);

?>
