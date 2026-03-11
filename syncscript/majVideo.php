<?php

require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$connMJ = $conn;
if (!$connMJ) {
    die("Connection failed: " . mysqli_connect_error());
}
	

$videofile = $_GET['videofile'];


$nouvVideo  = $videofile.'?x='.rand(1000,9999);

	
	$resultNouveau = mysqli_query($connMJ,"UPDATE Video SET nomFichier='{$nouvVideo}'
	WHERE nomFichier LIKE '{$videofile}%' LIMIT 2")
				or die(mysqli_error($connMJ)."update bug");  
	
	
	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////


?>
