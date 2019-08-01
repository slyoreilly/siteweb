<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";
$database = 'syncsta1_900';
 
$telId = $_POST['telId'];
$settings = $_POST['settings'];

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, "utf8");

$retour = mysqli_query($conn,
"INSERT INTO 
`Controle` (`telId`,`arg0`,`arg1`,`valeur`,`etatSync`) 
VALUES ('{$telId}','settings','all','{$settings}','3')
")or die(mysqli_error($conn)." UPDATE ");	

echo $adomper;

mysqli_close($conn);
	//		header("HTTP/1.1 200 OK");
?>

