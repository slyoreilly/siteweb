<?php

include_once ($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR . "syncstatsconfig.php");

require '../scriptsphp/defenvvar.php';
// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");
	
$clips = json_decode($_POST['clips']);

$heure = $_POST['heure'];
$heureServeur = time()*1000;

$syncOK = array();

foreach ($clips as $unClip) {


		if (isset($heure)) {
			// retourner le but, sans correction de chrono.
			$unClip['chrono'] = $unClip['chrono'] + $heureServeur - $heure;
		}


			

				$qInsM = "INSERT INTO Clips (matchId, chrono, scoringEnd, type) VALUES ('{$unClip['GameStringID']}','{$unClip['chrono']}',Null,5)";

				mysqli_query($conn,$qInsM) or die(mysqli_error($conn) . $qInsM);
				$webIdClip=mysqli_insert_id($conn);
				

				
				$retObj = array("id"=>$unClip["id"],"SyncKey"=>$webIdClip);
				array_push($syncOK, $retObj);
		
	}

echo json_encode($syncOK);


mysqli_close($conn);

?>