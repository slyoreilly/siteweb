<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
$boiteId = $_POST['boiteId'];
$con=mysqli_connect($db_host, $db_user, $db_pwd);
if (!$con)
    die("Can't connect to database");

if (!mysqli_select_db($con,$database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	
	mysqli_query($con,"SET NAMES 'utf8'");
mysqli_query($con,"SET CHARACTER SET 'utf8'");
	
//$jDom = json_decode($jDomJSON, true);
//$jVis = json_decode($jVisJSON, true);
$requete="SELECT *
						FROM boiteContexte
						 WHERE boiteId={$boiteId}
						 	";
$retour = mysqli_query($con,$requete)or die(mysqli_error().$requete);

while($r = mysqli_fetch_assoc($retour)) {
    echo json_encode($r);
}

	//		header("HTTP/1.1 200 OK");
?>
