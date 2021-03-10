<?php
require '../scriptsphp/defenvvar.php';

$ligueId= $_POST['ligueId'];
$cleValeur= $_POST['cleValeur'];

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");
	


//////////////////////////////////
//
//	Les queries
//


			
	$qSUp = "UPDATE Ligue 
							SET cleValeur='{$cleValeur}'
							WHERE videoId='{$ligueId}' ";
		mysqli_query($conn,$qSUp) or die(mysqli_error($conn).' Error, query failed'.$qSUp);


mysqli_close($conn);	
?>