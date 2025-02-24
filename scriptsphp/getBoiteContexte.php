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

	
//$jDom = json_decode($jDomJSON, true);
//$jVis = json_decode($jVisJSON, true);
$requete="SELECT *
						FROM boiteContexte
						 WHERE boiteId={$boiteId}
						 	";
$retour = mysqli_query($conn,$requete)or die(mysqli_error($conn).$requete);

while($r = mysqli_fetch_assoc($retour)) {
    echo json_encode($r);
}

	//		header("HTTP/1.1 200 OK");
?>
