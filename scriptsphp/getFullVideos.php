<?php

require '../scriptsphp/defenvvar.php';


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
	
//mysqli_close($conn);

?>
