<?php
require '../scriptsphp/defenvvar.php';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
$maxSec = $_POST['timeout'];
$deviceId = $_POST['deviceId'];
$userId = $_POST['userId'];
$lastUpdate = $_POST['lastUpdate'];


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

$retourStream = array();
$retourStream['lastUpdate']=$lastUpdate ;


do {



    $chronoRetour = array();
    $matchRetour = array();
     
    $qStream = "SELECT * FROM Stream
                WHERE deviceId=$deviceId 
AND userId=$userId AND updatedAt>'{$lastUpdate}'";
      
    $resultStreams = mysqli_query($conn,$qStream) or die(mysqli_error($conn) . $qStream);					
                                        
    $IS = 0;
    $trouve = false;
  
    
    while ($rangeeStream=mysqli_fetch_array($resultStreams)){// && !$trouve) {
        $retourStream['lastUpdate']=$rangeeStream['updatedAt'];
        $retourStream['settings']=$rangeeStream['settings'];
        $retourStream['status']=$rangeeStream['status'];
        $IS++;
    }
    if($IS==0){
        $mSleep = sleep(5);
        flush();
        $cpt = $cpt+5000;
    }  
    $comp =$maxSec-30000;
    }
     while (($IS==0)&&($cpt<$comp));
    
     echo json_encode($retourStream);

mysqli_close($conn);
	
?>