<?php
require '../scriptsphp/defenvvar.php';

$username = $_POST['username'];
$mdp =$_POST['password'];


$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
 
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
 
	$userSelect=array();
	
	// Retrieve all the data from the "example" table
$resultUser = mysqli_query($conn,"SELECT * FROM TableUser")
or die(mysqli_error($conn));  
while($rangeeUser=mysqli_fetch_array($resultUser))
{
		if(!strcmp($rangeeUser['username'],$username)&&!strcmp($rangeeUser['password'],$mdp))
	{$userSelect =$rangeeUser;
	}
		// Prend le ID du user pour trouver les ligues abonn�es.
}


/*
	
 */
 header("HTTP/1.1 200 OK");
 header("Content-Type: application/json;charset=utf-8");
 header("Accept: application/json");
//echo " ".count($AbonSelect);
	
		echo json_encode($userSelect);

mysqli_close($conn);

?>
