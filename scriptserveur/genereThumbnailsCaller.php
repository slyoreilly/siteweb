


<?php
//////////////////////////////////////
//
//		Pas Encore Fonctionnel!
//
//

$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';


$arbitreId = $_POST['arbitreId'];
$ligueId = $_POST['ligueId'];
$username = $_POST['username'];

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");


$reqSel = "SELECT nomFichier, emplacement, videoId
						FROM Video		
						 WHERE nomThumbnail is NULL AND emplacement ='5.39.81.14' Order By videoId DESC LIMIT 0,5";
$retour=  mysqli_query($conn,$reqSel) or die("Erreur: "+$reqSel+"\n"+mysqli_error());


$vecMatch = array();
echo "a";
while ($r = mysqli_fetch_assoc($retour)) {
echo "b";
	$nomT = substr($r['nomFichier'], 0, strpos($r['nomFichier'], '.'));
	
	
	$url = 'http://5.39.81.14/scriptsphp/genereThumbnails.php';
$data = array('nomFichier' => $nomT);

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
	
	$nomThumb = $nomT.'.jpg';
	$mVid = $r['videoId'];
	$retour = mysqli_query($conn,"UPDATE Video
						SET nomThumbnail= '{$nomThumb}'
						 WHERE videoId='{$mVid}'")or die(mysql_error());
	
	echo $result;
//	$include('http://5.39.81.14/scriptsphp/genereThumbnail.php?nomFichier='.$nomT);
}
echo "c";

	

	header("Content-Type: application/json", true);
		header("HTTP/1.1 200 OK");
?>

