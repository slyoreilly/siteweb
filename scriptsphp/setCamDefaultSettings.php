<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";
$database = 'syncsta1_900';
 
$telId = $_POST['telId'];
$settings = $_POST['settings'];

mysqli_set_charset($conn, "utf8");

$retour = mysqli_query($conn,
"UPDATE StatutCam SET 
defaultSettings='{$settings}'
WHERE telId='{$telId}'")or die(mysqli_error($conn)." UPDATE ");	

echo $adomper;

mysqli_close($conn);
	//		header("HTTP/1.1 200 OK");
?>

