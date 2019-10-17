<?php $db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';
// Create connection

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection/
if (!$conn) {
	error_log("Connection failed: " . mysqli_connect_error());
   die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

$strMAJ = $_POST['strMAJ'];

$punMAJ = json_decode(stripslashes($strMAJ));
//echo stripslashes(json_encode($butMAJ));
//echo $butMAJ->matchId;

$resultBut = mysqli_query($conn,"SELECT * 
												FROM TableEvenement0 
													WHERE match_event_id='{$punMAJ->matchId}'
														AND code='4' ORDER BY chrono");
//	echo $_POST['strMAJ'];
if ($punMAJ -> nouvellePun) {

	$cRow = 0;
	while ($row = mysqli_fetch_assoc($resultBut)) {
		$cRow++;
	}
	$punMAJ -> noSeq = $cRow;
	$ajouteBut =mysqli_query($conn,"INSERT INTO TableEvenement0 (`match_event_id`, `equipe_event_id`, `joueur_event_ref`, `code`, `souscode`, `chrono`, `noSequence`) 
	VALUES ('{$punMAJ->matchId}','{$punMAJ->equipeId}','{$punMAJ->joueurId}',4,'{$punMAJ->motifId}','{$punMAJ->chrono}','{$cRow}')");

} else {



	mysqli_query($conn,"UPDATE TableEvenement0 SET joueur_event_ref='{$punMAJ->joueurId}',souscode='{$punMAJ->motifId}' WHERE match_event_id='{$punMAJ->matchId}'
														AND code=4 AND chrono='{$punMAJ->chrono}'");

	
	if($punMAJ -> joueurId == null || $punMAJ -> joueurId == 0){
				mysqli_query($conn,"DELETE FROM TableEvenement0 WHERE match_event_id='{$punMAJ->matchId}'
														AND code=4
														AND chrono='{$punMAJ->chrono}'
														");
		
	}
	
	//echo "cpas: " . $cPas . "  sPas: " . $sPas . "  " . $butMAJ -> passeur1Id . "  " . $butMAJ -> passeur2Id;
	//echo " / rep insert Passeurs ".mysql_num_rows($retour);
}
echo 1;
//echo $tabButs[$butMAJ->noSeq]->chrono;
?>

