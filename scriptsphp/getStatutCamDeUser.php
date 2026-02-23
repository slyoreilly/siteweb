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


//mysqli_close($conn);

?>
