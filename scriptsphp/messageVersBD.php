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
	

function trouveIDParNomUser($nomUser,$conn)
{
$fResultUser = mysqli_query($conn, "SELECT noCompte 
								FROM TableUser 
								WHERE username='{$nomUser}'")
or die(mysqli_error($conn));  
$rU = mysqli_fetch_row($fResultUser);
if (mysqli_num_rows($fResultUser)>0)
{
return $rU[0];
}
else{return -1;}

}


$expediteur = trouveIDParNomUser($_POST['expediteur'],$conn);
$recepteur = $_POST['recepteur'];
$titre = mysqli_real_escape_string($conn,$_POST['titre']);
$corps = mysqli_real_escape_string($conn, $_POST['corps']);
$cleValeur = $_POST['cleValeur'];


	$retour = mysqli_query($conn, "INSERT INTO TableMessage (expediteur, recepteur, titre, corps, dateEmission,cleValeur) 
VALUES ('{$expediteur}','{$recepteur}','{$titre}','{$corps}',NOW(),'{$cleValeur}')")or die(mysqli_error($conn)." INSERT INTO");

?>
<?php  ?>
