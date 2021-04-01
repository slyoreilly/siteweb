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
    error_log("HTTP/1.1 403 Forbidden");	
    http_response_code(403);
}else{

    if($_FILES['fichier']['size'] > 0)
{
$fileName = $_FILES['fichier']['filetowrite'];
$tmpName  = $_FILES['fichier']['tmp_name'];
$fileSize = $_FILES['fichier']['size'];
$fileType = $_FILES['fichier']['type'];
$fp      = fopen($tmpName, 'r');
$content = fread($fp, filesize($tmpName));

$options = array('ftp' => array('overwrite' => true));
$stream = stream_context_create($options);

$chemin = $_SERVER["DOCUMENT_ROOT"]."/".$POST['app']."/".$POST['channel']."/".$fileName;   // Pour SyncStats.com

$retByte =file_put_contents($chemin, $content, 0, $stream); 
$retByte > 0 ? http_response_code(200) :  http_response_code(409);
http_response_code(200);
 } else{
	error_log("POURQUOI!!!!");	
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
