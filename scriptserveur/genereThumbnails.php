<?php
$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';


$nomFichier = $_GET['nomFichier'];


	$strEnr = "ffmpeg -i /home/lookatthis/".$nomFichier.".mp4 -ss 00:00:07 -vframes 1 /home/thumbnails/".$nomT.".jpg";
	shell_exec($strEnr);

	header("Content-Type: application/json", true);
		header("HTTP/1.1 200 OK");
?>

