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
while($rangeePosition=mysqli_fetch_array($rfPosition))
{
	$rangeePosition['CreatedAt'] = strtotime($rangeePosition['CreatedAt']); // convert to unix timestamp (in seconds)
    $rangeePosition['CreatedAt'] = 1000 * $rangeePosition['CreatedAt']; // convert seconds to milliseconds
	$rangeePosition['UpdateAt'] = strtotime($rangeePosition['UpdateAt']); // convert to unix timestamp (in seconds)
    $rangeePosition['UpdateAt'] = 1000 * $rangeePosition['UpdateAt']; // convert seconds to milliseconds
	$Position[] = $rangeePosition;
	
}
	
echo json_encode($Position);;
	
mysqli_close($conn);

?>
