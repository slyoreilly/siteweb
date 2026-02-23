<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$equipeId = $_POST['equipeId'];
$taJoueurs = $_POST['taJoueurs'];
$lesJoueurs = preg_split('/\r\n|\r|\n/', $taJoueurs);



	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

//////////////////////////////////
//
//	Les queries
//



foreach($lesJoueurs as $nomJoueur)
{
	$requeteJou = "INSERT INTO TableJoueur (NomJoueur, NumeroJoueur, Equipe,nom, prenom, taille,poids, anneeNaissance,villeOrigine, ficIdJoueur,proprio, ficIdPortrait, dernierMAJ) ".
"VALUES ('$nomJoueur', '0','aucune','','',0,0,0,'',95,0, 95, NOW())";

	$retour3 = mysqli_query($conn,$requeteJou) or die("Erreur: ".$requeteJou.mysqli_error($conn));
		$last_id = mysqli_insert_id($conn);
	
$requeteAbon = "INSERT INTO abonJoueurEquipe (equipeId, joueurId, permission, debutAbon, finAbon) ".
"VALUES ('$equipeId', '$last_id', 30, NOW(), '2030-01-01')";

$retour2 = mysqli_query($conn,$requeteAbon) or die("Erreur: ".$requeteAbon.mysqli_error($conn));


}
	
?>
