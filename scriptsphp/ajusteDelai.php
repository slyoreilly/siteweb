
<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////


$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection/
if (!$conn) {
	error_log("Connection failed: " . mysqli_connect_error());
   die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");


$fic=$_POST["fic"];
$cam=$_POST["cam"];
$delai=$_POST["delai"];



	$retour=array();

$qSel="SELECT * FROM StatutCam 
		 WHERE camId='{$cam}'  LIMIT 1";
$resCam=mysqli_query($conn,$qSel) or die(mysqli_error($conn).' damn');
while($rangCam=mysqli_fetch_array($resCam)){
$ajouteBut =mysqli_query($conn,"INSERT INTO Controle (`telId`, `arg0`, `arg1`, `arg2`, `valeur`, `cleValeur`, `etatSync`) 
	VALUES ('{$rangCam['telId']}','videos','recut','{$fic}','{$delai}',NULL, 3)");

}
?>
