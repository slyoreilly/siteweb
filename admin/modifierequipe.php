<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$equipeId = $_POST['equipeId'];
$nomEquipe = $_POST['nom'];
$logo = $_POST['logo'];
$ligueId = $_POST['ligueId'];
$code = $_POST['code'];
$ficId = $_POST['ficId'];
$couleur1 = $_POST['couleur'];
$ville = $_POST['ville'];



$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

//////////////////////////////////
//
//	Les queries
//

	if(1==$code)  // Code 1:  Cr�er une nouvelle equipe.
{
	$query_equipe = "INSERT INTO TableEquipe (nom_equipe, logo, ligue_equipe_ref,ficId,equipeActive,dernierMAJ,couleur1) ".
"VALUES ('$nomEquipe', '$logo', '$ligueId','$ficId',1,NOW(),'$couleur1')";
		
		$retour = mysqli_query($conn,$query_equipe) or die("Erreur: ".$query_equipe.mysqli_error($conn));


$requeteDernId = "SELECT * FROM TableEquipe WHERE nom_equipe='$nomEquipe' ORDER BY equipe_id DESC";
$rDernId = mysqli_query($conn,$requeteDernId) or die("Erreur: ".$requeteDernId.mysqli_error($conn));


$rEID = mysqli_fetch_array($rDernId);



$requeteAbon = "INSERT INTO abonEquipeLigue (equipeId, ligueId, permission, debutAbon, finAbon) ".
"VALUES ('$rEID[0]', '$ligueId', 30, NOW(), '2050-01-01')";

$retour2 = mysqli_query($conn,$requeteAbon) or die("Erreur: ".$requeteAbon.mysqli_error($conn));
echo "$rEID[0]";

}
	
	if(10==$code)  // Code 10:  Modifie ligue existante.
	{
//		$resultFic = mysql_query("SELECT ficId FROM TableEquipe WHERE equipe_id= '$equipeId'");
		
		
		
	$query_update = "UPDATE TableEquipe SET nom_equipe='{$nomEquipe}', logo='{$logo}', ville='{$ville}',ficId='{$ficId}', couleur1='{$couleur1}', dernierMAJ=NOW() WHERE equipe_id= '$equipeId'";	

	mysqli_query($conn,$query_update)or die(mysqli_error($conn)." update");	

	}
mysqli_close($conn);
	
?>
