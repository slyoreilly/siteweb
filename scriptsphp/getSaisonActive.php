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

$ligueId=null;
if(isset($_POST['ligueId'])){
$ligueId = $_POST["ligueId"];
}


$saisonId =null;

/////////////////////////////////////////////////////////////
// 
//

$rfSaison = mysqli_query($conn,"SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ligueId}' ORDER BY premierMatch DESC LIMIT 0,1")
or die(mysqli_error($conn)." Select saisonId"); 

while($rangeeSaison=mysqli_fetch_array($rfSaison))
{
	$saisonId = $rangeeSaison['saisonId'];
	
}
	
echo $saisonId;
	
mysqli_close($conn);

?>
