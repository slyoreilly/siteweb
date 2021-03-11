<?php
require '../scriptsphp/defenvvar.php';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];

$telId = $_POST['telId'];
$message = $_POST['message'];
$retour=array();


error_log("Appel error_log: ".$telId." avec message: ".$message);
?>
