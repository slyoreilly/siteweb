<?php
require '../scriptsphp/defenvvar.php';
 
$telId = $_POST['telId'];
$settings = $_POST['settings'];

mysqli_set_charset($conn, "utf8");

$retour = mysqli_query($conn,
"INSERT INTO 
`Controle` (`telId`,`arg0`,`arg1`,`valeur`,`etatSync`) 
VALUES ('{$telId}','settings','all','{$settings}','3')
")or die(mysqli_error($conn)." UPDATE ");	

echo $adomper;

//mysqli_close($conn);
	//		header("HTTP/1.1 200 OK");
?>

