<?php
$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection/
if (!$conn) {
   die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

	
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

error_log("ecriture du fichier ".$_FILES['fichier']['name']. " grosseur: ".$_FILES['fichier']['size'], 0);


if(isset($_POST['params'])){
	$params =$_POST['params'];
	$paramsJD=json_decode($params,true);
	if(isset($paramsJD['nomMatch'])){
	$matchId=$paramsJD['nomMatch'];
	$ligueId = file_get_contents('http://syncstats.com/scriptserveur/getLigueIdDeMatchId.php?matchId='.$matchId);
	error_log("Match appartenant à la ligue ".$ligueId." ".$params, 0);}
} 

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
  $chemin = '/home3/syncsta1/public_html/lookatthis/'.$fileName;   // Pour SyncStats.com
 $retByte =file_put_contents($chemin, $content, 0, $stream); 
 error_log("postPut ".$_FILES['fichier']['name']."/ callé: ".$_FILES['fichier']['size']."/ ecrit: ".$retByte, 0);


 // ffmpeg -i /home/lookatthis/1486439644268_15.mp4 -i /home/images/logoFondNoir.png -filter_complex "overlay=10:10" /home/images/testVid1.mp4
 
// echo "Good!";
 if($retByte==$_FILES['fichier']['size']){
 	$cmdwatermark = 'sudo -u www-data ffmpeg -y -i '.$chemin.' -i /home/images/logoFondNoir3.png -filter_complex "overlay=x=main_w-overlay_w-10:y=10"'." /home/vidtmp/".$fileName."; "."sudo -u www-data mv -f /home/vidtmp/".$fileName." ".$chemin;
// $retour2 = mysqli_query($conn, "INSERT INTO TacheShell (commande,date) 
//VALUES ('{$cmdwatermark}',NOW())")or die(mysqli_error($conn)." INSERT INTO TacheShell");
$strEnr = "sudo ffmpeg -y -i /home/lookatthis/".$fileName." -ss 00:00:07 -vframes 1 /home/thumbnails/".$fileName.".jpg";
 	
// $retour2 = mysqli_query($conn, "INSERT INTO TacheShell (commande,date) 
//VALUES ('{$strEnr}',NOW())")or die(mysqli_error($conn)." INSERT INTO TacheShell");
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
 $url = 'https://syncstats.com/syncscript/majVideo.php?videofile='.$fileName;
  
 //Use file_get_contents to GET the URL in question.
file_get_contents($url);




else {echo "fichier grosseur nulle?";
	error_log("HTTP/1.1 204 No Content");		 
	http_response_code(204);}

//////////////////////////////////
//
//	Les queries
//

error_log(http_response_code());
//include 'library/closedb.php';
	
?>
