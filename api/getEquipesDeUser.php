<?php
////////////////////////////////////////////////////////////
//
//	getEquipesDeUser.php
//	Est appellé dans equipeRepository.kt
//
//
////////////////////////////////////////////////////////////


require '../scriptsphp/defenvvar.php';



////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");
	
$username = $_POST['username'];

$qString = "SELECT TableEquipe.* 
			FROM TableEquipe 
			JOIN abonEquipeLigue 
				ON abonEquipeLigue.equipeId = TableEquipe.equipe_id
			JOIN AbonnementLigue 
				ON AbonnementLigue.ligueId = abonEquipeLigue.ligueId 
			JOIN TableUser 
				ON AbonnementLigue.userId = TableUser.noCompte 
			WHERE TableUser.username = '{$username}' 			
			GROUP BY equipe_id";


$retour = mysqli_query($conn,$qString) or die(mysqli_error($conn));	
//$strRetour.= mysql_num_rows($retour);
//$strRetour.="rege";

$vecEquipes = array();
$Im=0;
while($r = mysqli_fetch_array($retour,MYSQLI_ASSOC)) {
	$uneEquipe= array();
	$uneEquipe['equipeId']=$r['equipe_id'];
	$uneEquipe['ligueId']=$r['ligue_equipe_ref'];
	$uneEquipe['nom']=$r['nom_equipe'];
	$uneEquipe['couleur1']=$r['couleur1'];
	$uneEquipe['dernierMAJ']=strtotime($r['dernierMAJ'])*1000;
	$uneEquipe['cleValeur']=$r['cleValeur'];
	array_push($vecEquipes,$uneEquipe);
}
mysqli_close($conn);

echo json_encode($vecEquipes);
	


?>
