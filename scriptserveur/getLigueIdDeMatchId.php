<?php
require '../scriptsphp/defenvvar.php';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
	


$matchId = $_GET['matchId'];


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

////////////////////////////
//
///		A partir d'un telId et d'un username, trouver les appareils et leurs statuts.


	
		$querySel = "SELECT ligueRef FROM TableMatch WHERE match_id = '{$matchId}'";
		$resultSel=mysqli_query($conn,$querySel) or die("Erreur: ".$querySel."\n".mysqli_error($conn));
				
		
		$rangSel = mysqli_fetch_row($resultSel);
		$ligueId = $rangSel[0];
		
		
	echo $ligueId;
		header('Content-Type: text/html; charset=utf-8');
	
?>
