<?php
////////////////////////////////////////////////////////////
//
//	getLigues.php
//	Est appellé dans LigueRepository.kt
//
//
////////////////////////////////////////////////////////////


require '../scriptsphp/defenvvar.php';
	
$username = $_POST['username'];

$qString = "SELECT Ligue.* 
			FROM Ligue 
			JOIN AbonnementLigue 
				ON AbonnementLigue.ligueId = Ligue.ID_Ligue 
			JOIN TableUser 
				ON AbonnementLigue.userId = TableUser.noCompte 
			WHERE TableUser.username = '{$username}' 			
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
