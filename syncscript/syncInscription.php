<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$nom = $_POST['nom'];
$mdp = $_POST['mdp'];
$courriel = $_POST['courriel'];

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
	

$erreur=false;
$qVerif ="SELECT * from TableUser WHERE username='{$nom}'";
$retVerif= 	 mysqli_query($conn,$qVerif)or die(mysqli_error($conn).$qVerif);
if(mysqli_num_rows($retVerif)>0)
	{$erreur=true;}
	
if(!$erreur)
{
	$qIns = "INSERT INTO TableUser (username, password, type, sexe,courriel) 
VALUES ('{$nom}', '{$mdp}', 10, 'M','{$$courriel}')";
$retIns= 	 mysqli_query($conn,$qIns)or die(mysqli_error($conn).$qIns);
	
}	
	
			if($retIns)
				echo "1";//.$matchjson.json_encode($leMatch);
			else {
				echo "0";
			}
		
mysqli_close($conn);

			header("HTTP/1.1 200 OK");


?>
