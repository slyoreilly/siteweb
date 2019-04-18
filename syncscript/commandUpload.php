<?php
$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

	
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

if(isset($_POST['command'])){
	$command =$_POST['command'];
}
 


 $retour2 = mysqli_query($conn, "INSERT INTO TacheShell (commande,date, priorite) 
VALUES ('{$command}',NOW(),10)")or die(mysqli_error($conn)." INSERT INTO TacheShell");
 		header("HTTP/1.1 200 OK"); 	



//////////////////////////////////
//
//	Les queries
//


//include 'library/closedb.php';
	
?>
