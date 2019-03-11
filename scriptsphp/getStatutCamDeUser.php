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

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////
// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");



//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////
	
//$receveur = $_POST["receveur"];
$userId = $_POST["userId"];

$reqMes = "SELECT  camId, telId, codeEtat, DATE_FORMAT(dernierModif, '%Y-%m-%dT%TZ') AS Modif , DATE_FORMAT(dernierMaJ, '%Y-%m-%dT%TZ') AS MaJ, batterie, memoire, temperature, TableArena.nomArena, TableArena.nomGlace
			FROM StatutCam
			JOIN TableArena
			on (StatutCam.arenaId=TableArena.arenaId)
			
			WHERE userId='{$userId}' ORDER BY MaJ DESC" ;
$rMes = mysqli_query($conn,$reqMes)
or die(mysqli_error($conn));  

$json = mysqli_fetch_all ($rMes, MYSQLI_ASSOC);
echo json_encode($json );


mysqli_close($conn);

?>
