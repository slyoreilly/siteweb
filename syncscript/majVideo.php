<?php

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$connMJ = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$connMJ) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($connMJ, "SET NAMES 'utf8'");
mysqli_query($connMJ, "SET CHARACTER SET 'utf8'");
	

$videofile = $_GET['videofile'];


$nouvVideo  = $videofile.'?x='.rand(1000,9999);

	
	$resultNouveau = mysqli_query($connMJ,"UPDATE Video SET nomFichier='{$nouvVideo}'
	WHERE nomFichier LIKE '{$videofile}%' LIMIT 2")
				or die(mysqli_error($connMJ)."update bug");  
	
	
	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

mysqli_close($connMJ);

?>
