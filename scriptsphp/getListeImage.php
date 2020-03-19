<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';

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
$typeProprio = $_POST["typeProprio"];
$proprio = $_POST["proprio"];

$reqGal = "SELECT * 
			FROM Galerie
			WHERE proprio={$proprio} 
				AND typeProprio='{$typeProprio}'";
$rGal = mysqli_query($conn,$reqGal)
or die(mysqli_error($conn));  

$IM=0;
$image = Array();
while ($rangIm = mysqli_fetch_array($rGal))
{
	$image[$IM]=array();
	$image[$IM]['ficId']=$rangIm['ficId'];
	$image[$IM]['titre']=$rangIm['titre'];
	$image[$IM]['description']=$rangIm['description'];
			$IM++;
}


echo json_encode($image);
	
mysqli_close($conn);

?>
