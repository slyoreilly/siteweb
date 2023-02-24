<?php
////////////////////////////////////////////////////////////
//
//	getLigues.php
//	Est appellé dans LigueRepository.kt
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

$qString = "SELECT Ligue.* 
			FROM Ligue 
			JOIN AbonnementLigue 
				ON AbonnementLigue.ligueId = Ligue.ID_Ligue 
			JOIN TableUser 
				ON AbonnementLigue.userId = TableUser.noCompte 
			WHERE TableUser.username = '{$usernamexcv}' 			
			GROUP BY ID_Ligue";


$retour = mysqli_query($conn,$qString) or die(mysqli_error($conn));	
//$strRetour.= mysql_num_rows($retour);
//$strRetour.="rege";

$vecLigues = array();
$Im=0;
while($r = mysqli_fetch_array($retour,MYSQLI_ASSOC)) {
	$uneLigue= array();
	$uneLigue['ligueId']=$r['ID_Ligue'];
	$uneLigue['sportId']=$r['sportId'];
	$uneLigue['nom']=$r['Nom_Ligue'];
	$uneLigue['lieu']=$r['Lieu'];
	$uneLigue['dernierMAJ']=$r['arenaId'];
	$uneLigue['horaire']=$r['Horaire'];
	$uneLigue['cleValeur']=$r['cleValeur'];
	array_push($vecLigues,$uneLigue);
}
mysqli_close($conn);

echo json_encode($vecLigues);
	


?>
