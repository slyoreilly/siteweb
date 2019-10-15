<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];

$telId = $_POST['telId'];
$message = $_POST['message'];
$retour=array();


error_log("Appel error_log: ".$telId." avec message: ".$message);
?>
