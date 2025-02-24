<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$dateDeb = $_POST['dateDeb'];
$dateFin = $_POST['dateFin'];
$type = $_POST['type'];
$code = $_POST['code'];
$ligueId = $_POST['ligueId'];
$saisonId = $_POST['saisonId'];
$nom = $_POST['nom'];

//////////////////////////////////
//
//	Les queries
//



	if(1==$code)  // Code 1:  Cr�er une nouvelle ligue.
{


$qVielleSaison="SELECT saisonId FROM TableSaison WHERE ligueRef='{$ligueId}' order by dernierMatch desc limit 0,1";
$resVS=mysqli_query($conn,$qVielleSaison) or die(mysqli_error($conn).'Error, query failed'.$qVielleSaison);
		while ($rVS = mysqli_fetch_array($resVS)) {
	$qSUp = "UPDATE TableSaison 
							SET dernierMatch='{$dateDeb}'
							WHERE saisonId='{$rVS[0]}' ";
		mysqli_query($conn,$qSUp) or die(mysqli_error($conn).' Error, query failed'.$qSUp);

		}
		
	$query_saison = "INSERT INTO TableSaison (typeSaison,saisonActive, premierMatch, dernierMatch,ligueRef,nom) ".
"VALUES ($type, 1, '$dateDeb','$dateFin',$ligueId,'{$nom}' )";
mysqli_query($conn,$query_saison) or die(mysqli_error($conn).'Error, query failed');


}


	
	if(10==$code)  // Code 10:  Modifie ligue existante.
	{
		echo "dans code 10".$dateDeb." / ".$dateFin." / ".$ligueId." / ".$type." / ".$saisonId;
	$query_saison = "UPDATE TableSaison 
							SET premierMatch='{$dateDeb}', dernierMatch='{$dateFin}', typeSaison='{$type}',saisonActive='1',ligueRef='{$ligueId}',nom='{$nom}'
							WHERE saisonId='{$saisonId}' ";
		mysqli_query($conn,$query_saison) or die(mysqli_error($conn).' Error, query failed');
		
	
	}
	mysqli_close($conn);
echo "Fin du script modifiersaison".$query_saison;
?>
