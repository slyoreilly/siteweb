<?php
require '../scriptsphp/defenvvar.php';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
$maxSec = $_POST['timeout'];  // en milisecondes
$deviceId = $_POST['deviceId'];
$userId = $_POST['userId'];
$lastUpdate = $_POST['lastUpdate'];

$retourStream = array();
$retourStream['lastUpdate']=$lastUpdate ;
$cpt=0;
$comp =$maxSec-0; ///  Le "0" est tunable, il sert a donner un espèce de marge sur le timeout
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
    
    }
     while (($IS==0)&&($cpt<$comp));
    
     echo json_encode($retourStream);

//mysqli_close($conn);
	
?>