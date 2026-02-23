<?php


header("Access-Control-Allow-Origin: https://syncstats.com");
header("Access-Control-Allow-Origin: http://syncstats.com");
header("Access-Control-Allow-Origin: https://syncstats.ddns.net");
header("Access-Control-Allow-Origin: http://syncstats.ddns.net");
header("Access-Control-Allow-Origin: https://syncstats.ca");
header("Access-Control-Allow-Origin: http://syncstats.ca");
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }

 

//
$liste = scandir( '../lookatthis/' , SCANDIR_SORT_DESCENDING);
if (($key = array_search('..', $liste)) !== false) {
    unset($liste[$key]);
}
if (($key = array_search('.', $liste)) !== false) {
    unset($liste[$key]);
}

$source=$_POST['source'];  
$maxDownload=0;
$maxDownload=$_POST['maxDownload'];   

	/// Get de destination
	$url = $source.'/scriptserveur/getListeVideos.php';
					
	// use key 'http' even if you send the request to https://...
	$options = array(
					'http' => array(
    				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
    				'method'  => 'POST',
    				'content' => http_build_query($data)
					)
	);
	$context  = stream_context_create($options);
	$dest = json_decode(file_get_contents($url, false, $context));
    if ($result === FALSE) { 						error_log("erreur dans autoDownloadInterserveur.php",0);
	}
					






//$fichiers=json_decode($_POST['fichiers']);

$aTelecharger = array_diff($source, $dest);
sort($aTelecharger);

$options = array('ftp' => array('overwrite' => true));
$stream = stream_context_create($options);

for($a=0;$a<count($aTelecharger) && $a<$maxDownload; $a++){
    file_put_contents($_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."lookatthis".DIRECTORY_SEPARATOR.$aTelecharger[$a], fopen($source.DIRECTORY_SEPARATOR."lookatthis".DIRECTORY_SEPARATOR.$aTelecharger[$a], 'r'), 0, $stream);
}

 
 

	
?>
