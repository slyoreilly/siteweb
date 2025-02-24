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

$matchId = $_POST['matchId'];



$retour = mysqli_query($conn,"DELETE 
						FROM TableMatch 
						WHERE match_id='{$matchId}'")or die(mysqli_error($conn));	


?>
