<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';


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
	
//mysqli_close($conn);

?>
