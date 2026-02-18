<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$videoId= $_POST['videoId'];

//////////////////////////////////
//
//	Les queries
//


$qVielleSaison="SELECT nbVues FROM Video WHERE videoId='{$videoId}' limit 0,1";
$resVS=mysqli_query($conn,$qVielleSaison) or die(mysqli_error($conn).'Error, query failed'.$qVielleSaison);
		while ($rVS = mysqli_fetch_array($resVS)) {
			$aMettre = $rVS[0]+1;
			
	$qSUp = "UPDATE Video 
							SET nbVues='{$aMettre}'
							WHERE videoId='{$videoId}' ";
		mysqli_query($conn,$qSUp) or die(mysqli_error($conn).' Error, query failed'.$qSUp);

		}
		

//mysqli_close($conn);	
?>
