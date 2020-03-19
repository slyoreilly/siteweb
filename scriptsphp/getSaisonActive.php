<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';
$tableMatch = 'TableMatch';


$ligueId = $_POST["ligueId"];

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");



/////////////////////////////////////////////////////////////
// 
//

$rfSaison = mysqli_query($conn,"SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ligueId}' ORDER BY premierMatch DESC LIMIT 0,1")
or die(mysqli_error($conn)." Select saisonId"); 

while($rangeeSaison=mysqli_fetch_array($rfSaison))
{
	echo $rangeeSaison['saisonId'];
	
}
	
echo $saisonId;
	
mysqli_close($conn);

?>
