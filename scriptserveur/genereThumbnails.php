<?php
require '../scriptsphp/defenvvar.php';
// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection/
if (!$conn) {
   die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");


$nomFichier = $_POST['nomFichier'];

//$nomT = substr($nomFichier, 0, strpos($nomFichier, '.',0));

	$strEnr = "ffmpeg -i /home/lookatthis/".$nomFichier.".mp4 -ss 00:00:07 -vframes 1 /home/thumbnails/".$nomFichier.".jpg";
 	
 $retour2 = mysqli_query($conn, "INSERT INTO TacheShell (commande,date) 
VALUES ($strEnr,NOW())")or die(mysqli_error($conn)." INSERT INTO TacheShell");

	
	//	$res= shell_exec($strEnr);
//	error_log($res.$strEnr.$nomFichier, 0);
//echo $res;
	header("Content-Type: application/json", true);
		header("HTTP/1.1 200 OK");
?>

