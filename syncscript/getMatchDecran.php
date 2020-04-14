<?php
require '../scriptsphp/defenvvar.php';


$ecranId = $_POST['ecranId'];

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");
//echo "yo";

$reqSel = "SELECT *
						FROM ParametresEcran		
						 WHERE ecranId='{$ecranId}' order by dernierMAJ DESC limit 0,1";
$retour=  mysqli_query($conn,$reqSel) or die("Erreur: ".$reqSel."\n".mysqli_error($conn));
//echo $reqSel;

$vecMatch = array();
while ($r = mysqli_fetch_assoc($retour)) {
	$vecMatch[] = $r;
}

//echo $vecMatch[0]['dernierMAJ'];
//echo $vecMatch[0]['parametres'];
$parametres= json_decode($vecMatch[0]['parametres'],true);//  $vecMatch[]//abonnementsAppareils
$arrMatchs= Array();
$tmpArray = Array();
$recentMatchId = 0;
$recentMAJ = "1970-01-01 00:00:00";
//echo $parametres;
foreach($parametres['abonnementsAppareils'] as $telId){
	$reqSelM = "SELECT *
						FROM abonAppareilMatch		
						 WHERE telId='{$telId}' order by dernierMAJ DESC limit 0,1";
//echo $reqSelM;
$retSelM=  mysqli_query($conn,$reqSelM) or die("Erreur: ".$reqSelM."\n".mysqli_error($conn));
$tmpArray=mysqli_fetch_assoc($retSelM);

if($tmpArray['dernierMAJ']>$recentMAJ){
	$recentMatchId=$tmpArray['matchId'];
	$recentMAJ = $tmpArray['dernierMAJ'];
}
}
	
	echo utf8_encode($recentMatchId);
	header("Content-Type: application/json", true);
		header("HTTP/1.1 200 OK");
?>

