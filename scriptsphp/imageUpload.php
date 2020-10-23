<?php
require '../scriptsphp/defenvvar.php';


	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////




$headers = apache_request_headers();

if(strcasecmp($headers['confirmation-key'],'z0d2N9IvAifAoZZgVqDfGDB2QGakzuo9pmb0MOIf')){
	error_log("ecriture de l'image: header non valide. On se fait hacker? ");

}else{
error_log("ecriture de l'image ".$_FILES['fichier']['name']. " grosseur: ".$_FILES['fichier']['size'], 0);



if($_FILES['fichier']['size'] > 0)
{
//	 echo "presque...";
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
// $chemin = '/home/lookatthis/'.$fileName;
  $chemin = $_SERVER["DOCUMENT_ROOT"]."/".$image_loc.$fileName;   // Pour SyncStats.com
 $retByte =file_put_contents($chemin, $content, 0, $stream); 
 error_log("postPut Image".$_FILES['fichier']['name']."/ callé: ".$_FILES['fichier']['size']."/ ecrit: ".$retByte, 0);


error_log("HTTP/1.1 200 OK");		 
http_response_code(200);
 } else{
 	if($retByte==false){
		error_log("HTTP/1.1 409 Conflict");		 
		http_response_code(409);
	 	} else {
		error_log("HTTP/1.1 206 Partial Content");		 
		http_response_code(206);
 	}
 }
		 
 


	 
 //The URL that we want to GET.
 $url = $fileserver_baseurl.$image_loc.$fileName;
  
 //Use file_get_contents to GET the URL in question.


echo $url;
}
?>
