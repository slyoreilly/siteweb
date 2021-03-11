<?php

require '../scriptsphp/defenvvar.php';
////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

//////////////////////////////////////////////////////
//
//  	Section "Full Videos"
//
//////////////////////////////////////////////////////
	
//$receveur = $_POST["receveur"];
$gameId = $_POST["gameId"];

$reqChrono = "SELECT videoId, nomFichier, chrono, camId, emplacement 
			FROM Video
			WHERE nomMatch={$gameId} and type=10000 ORDER BY chrono DESC";
//Anciennement TableMatch.matchIdRef


$rChrono = mysqli_query($conn, $reqChrono)
or die(mysqli_error($conn));  


$fullVideos = Array();

while ($rangChrono = mysqli_fetch_array($rChrono))
{
				$myVid=Array();	
                $myVid['emplacement']=$rangChrono['emplacement'];
                $myVid['chrono']=$rangChrono['chrono'];
					$myVid['nomFichier']=$rangChrono['nomFichier'];
                    $myVid['videoId']=$rangChrono['videoId'];
                    $myVid['camId']=$rangChrono['camId'];
                    array_push($fullVideos,$myVid);

}

echo json_encode($fullVideos);
	
mysqli_close($conn);

?>
