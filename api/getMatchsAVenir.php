<?php
////////////////////////////////////////////////////////////
//
//	getMatchsAVenir.php
//	Est appellé dans SyncAdapter.kt
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

$qString = "SELECT TableMatch.* 
			FROM TableMatch 
			JOIN abonEquipeLigue 
				ON abonEquipeLigue.ligueId = TableMatch.ligueRef 
			JOIN AbonnementLigue 
				ON abonEquipeLigue.ligueId = AbonnementLigue.ligueid 
			JOIN TableUser 
				ON AbonnementLigue.userId = TableUser.noCompte 
			WHERE TableUser.username = '{$username}'  
				AND abonEquipeLigue.finAbon > NOW() 
				AND TableMatch.date > (NOW() - INTERVAL 1 DAY) 
				AND TableMatch.date < (NOW() + INTERVAL 7 DAY) 
				AND (TableMatch.eq_dom = abonEquipeLigue.equipeId 
					OR TableMatch.eq_vis = abonEquipeLigue.equipeId) 
			GROUP BY match_id";


$retour = mysqli_query($conn,$qString) or die(mysqli_error($conn));	
//$strRetour.= mysql_num_rows($retour);
//$strRetour.="rege";

$vecMatch = array();
$Im=0;
while($r = mysqli_fetch_array($retour,MYSQLI_ASSOC)) {
	$unMatch= array();
	$unMatch['matchLongId']=$r['matchIdRef'];
	$unMatch['eqDom']=$r['eq_dom'];
	$unMatch['eqVis']=$r['eq_vis'];
	$unMatch['date']=$r['date'];
	$unMatch['ligueId']=$r['ligueRef'];
	$unMatch['arenaId']=$r['arenaId'];
	$unMatch['scoreDom']=$r['score_dom'];
	$unMatch['scoreVis']=$r['score_vis'];
	$unMatch['cleValeur']=$r['cleValeur'];
	array_push($vecMatch,$unMatch);
}
mysqli_close($conn);

echo json_encode($vecMatch);
	


?>
