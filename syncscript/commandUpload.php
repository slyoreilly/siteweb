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
	
require '../scriptsphp/defenvvar.php';

	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

if(isset($_POST['command'])){
	$command =$_POST['command'];
}
 


 $retour2 = mysqli_query($conn, "INSERT INTO TacheShell (commande,date, priorite) 
VALUES ('{$command}',NOW(),10)")or die(mysqli_error($conn)." INSERT INTO TacheShell");
 		header("HTTP/1.1 200 OK"); 	



//mysqli_close($conn);	
?>
