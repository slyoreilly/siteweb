<?php

$chemin=$_POST['chemin'];

if($_FILES['fichier']['size'] > 0)
{
$fileName = $_FILES['fichier']['name'];
$tmpName  = $_FILES['fichier']['tmp_name'];
$fileSize = $_FILES['fichier']['size'];
$fileType = $_FILES['fichier']['type'];

$fp      = fopen($tmpName, 'r');
$content = fread($fp, filesize($tmpName));
//$contentEsc = mysql_real_escape_string($content);
fclose($fp);

if(!get_magic_quotes_gpc())
{
    $fileName = addslashes($fileName);
}

//error_log("preStream ".$_FILES['fichier']['name'], 0);
 $options = array('ftp' => array('overwrite' => true));
 $stream = stream_context_create($options);
// error_log("postStream ".$_FILES['fichier']['name'], 0);
 $retByte =file_put_contents($_SERVER["DOCUMENT_ROOT"].$chemin, $content, 0, $stream); 
 
 		
}
else {echo "fichier grosseur nulle?";
		header("HTTP/1.1 204 No Content");}

	
?>
