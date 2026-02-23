<?php
require '../scriptsphp/defenvvar.php';

$ligueId= $_POST['ligueId'];
$cleValeur= $_POST['cleValeur'];

	


//////////////////////////////////
//
//	Les queries
//


			
	$qSUp = "UPDATE Ligue 
							SET cleValeur='{$cleValeur}'
							WHERE ID_Ligue='{$ligueId}' ";
		mysqli_query($conn,$qSUp) or die(mysqli_error($conn).' Error, query failed'.$qSUp);


//mysqli_close($conn);	
?>