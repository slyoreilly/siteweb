<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$pm = $_POST['premierMatch'];
$dm = $_POST['dernierMatch'];
$equipeId = $_POST['equipeId'];
$ligueId = $_POST['ligueId'];
$code = $_POST['code'];

mysqli_query($conn, "
SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')
");
///////////////////////////////////////////////////////////////////////////////////////



//////////////////////////////////
//
//	Les queries
//



	if(1==$code)  // Code 1:  Cr�er une nouvelle equipe.
{
	$query_equipe = "INSERT INTO abonEquipeLigue (equipeId, ligueId, permission, debutAbon, finAbon) ".
"VALUES ($equipeId, $ligueId, 30, '$pm','$dm' )";
		
$retour = mysqli_query($conn,$query_equipe)
or die(mysqli_error($conn));

}

	if(2==$code)  // Code 1:  desabonne une equipe.
{
	$query_equipe = "UPDATE abonEquipeLigue 
						SET finAbon= DATE_SUB('$dm',INTERVAL 1 DAY)
						WHERE equipeId=$equipeId AND ligueId=$ligueId
						ORDER BY finAbon DESC
						LIMIT 1 ";
		
$retour =mysqli_query($conn,$query_equipe)
or die(mysqli_error($conn).$query_equipe);

}

	
	if(10==$code)  // Code 10:  Modifie ligue existante.
	{
	
	}
	echo $retour;

	//mysqli_close($conn);
?>
