<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

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


$reqSel = "SELECT *
						FROM Job	
						 WHERE contexte='arbitre'";
$retour=  mysqli_query($conn,$reqSel) or die("Erreur: ".$reqSel."\n".mysqli_error($conn));


$vecMatch = array();
while ($r = mysqli_fetch_assoc($retour)) {
	$vecMatch[] = $r;
}
$reqReq = "SELECT *
						FROM Requis				
						 WHERE 1";
$retour2=  mysqli_query($conn,$reqReq) or die("Erreur: ".$reqReq."\n".mysqli_error($conn));


$vecMatch2 = array();
while ($r2 = mysqli_fetch_assoc($retour2)) {
	$vecMatch2[] = $r2;
}

$retourJA = array();
$retourJA['jobs']= $vecMatch ;
$retourJA['requis']=$vecMatch2;
 $adomper = stripslashes(json_encode($retourJA));

$adomper = str_replace('"[', '[', $adomper);
$adomper = str_replace(']"', ']', $adomper);
	
	echo utf8_encode($adomper);
	header("Content-Type: application/json", true);
		header("HTTP/1.1 200 OK");
?>

