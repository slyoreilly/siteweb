<?php require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
$strMAJ = $_POST['strMAJ'];


$butMAJ = json_decode(stripslashes($strMAJ));
//echo stripslashes(json_encode($butMAJ));
//echo $butMAJ->matchId;

$resultBut = mysqli_query($conn,"SELECT * 
												FROM TableEvenement0 
													WHERE match_event_id='{$butMAJ->matchId}'
														AND code='0' ORDER BY chrono");
//	echo $_POST['strMAJ'];
if ($butMAJ -> nouveaubut) {

	$cRow = 0;
	while ($row = mysqli_fetch_assoc($resultBut)) {
		$cRow++;
	}
	$butMAJ -> noSeq = $cRow;
	$ajouteBut = mysqli_query($conn,"INSERT INTO TableEvenement0 (`match_event_id`, `equipe_event_id`, `joueur_event_ref`, `code`, `souscode`, `chrono`, `noSequence`) 
	VALUES ('{$butMAJ->matchId}','{$butMAJ->equipeId}','{$butMAJ->marqueurId}',0,0,'{$butMAJ->chrono}','{$cRow}')");
	


		if ($butMAJ -> passeur1Id != null) {
		$ajouteBut = mysqli_query($conn,"INSERT INTO TableEvenement0 (`match_event_id`, `equipe_event_id`, `joueur_event_ref`, `code`, `souscode`, `chrono`, `noSequence`) 
	VALUES ('{$butMAJ->matchId}','{$butMAJ->equipeId}','{$butMAJ->passeur1Id}',1,0,'{$butMAJ->chrono}','{$cRow}')");
	
		}
		if ($butMAJ -> passeur2Id != null) {
	$ajouteBut = mysqli_query($conn,"INSERT INTO TableEvenement0 (`match_event_id`, `equipe_event_id`, `joueur_event_ref`, `code`, `souscode`, `chrono`, `noSequence`) 
	VALUES ('{$butMAJ->matchId}','{$butMAJ->equipeId}','{$butMAJ->passeur2Id}',1,0,'{$butMAJ->chrono}','{$cRow}')");  	
		}



} else {
$AN = $butMAJ -> AN;
$DN = $butMAJ -> DN;
$FD = $butMAJ -> FD;
$TP = $butMAJ -> TP;
$sc=0;
if($AN){$sc =$sc +50;}
if($DN){$sc =$sc +40;}
if($FD){$sc =$sc +9;}
if($TP){$sc =$sc +3;}

	$cRow = 0;
	while ($row = mysqli_fetch_assoc($resultBut)) {
		if ($butMAJ -> noSeq == $cRow) {$tabButs = $row;
		}
		$cRow++;
	}

	echo stripslashes(json_encode($tabButs));

	mysqli_query($conn,"UPDATE TableEvenement0 SET joueur_event_ref='{$butMAJ->marqueurId}' ,souscode ='{$sc}' WHERE match_event_id='{$butMAJ->matchId}'
														AND code=0 AND chrono='{$tabButs['chrono']}'");

	$resultPasse = mysqli_query($conn,"SELECT * 
												FROM TableEvenement0 
													WHERE chrono='{$tabButs['chrono']}'
														AND match_event_id='{$butMAJ->matchId}'
														AND code='1'");

	$cPas = 0;
	
	

	if ($butMAJ -> passeur1Id != null && $butMAJ -> passeur1Id != "0") {$passeurs[$cPas] = $butMAJ -> passeur1Id;
		$cPas++;
	}
	if ($butMAJ -> passeur2Id != null && $butMAJ -> passeur2Id != "0") {$passeurs[$cPas] = $butMAJ -> passeur2Id;
		$cPas++;
	}
	$sPas = 0;
	while ($tabPasses = mysqli_fetch_array($resultPasse)) {
		if ($cPas != 0 && $sPas == 0) {
			mysqli_query($conn,"UPDATE TableEvenement0 SET joueur_event_ref='{$passeurs[$sPas]}' ,souscode ='{$sc}' WHERE match_event_id='{$butMAJ->matchId}'
														AND code=1 
														AND chrono='{$tabButs['chrono']}'
														AND joueur_event_ref='{$tabPasses['joueur_event_ref']}'
														LIMIT 1");

		}
		
		if ($cPas == 2 && $sPas == 1) {
			mysqli_query($conn,"UPDATE TableEvenement0 SET joueur_event_ref='{$passeurs[$sPas]}'  ,souscode ='{$sc}' WHERE match_event_id='{$butMAJ->matchId}'
														AND code=1 
														AND chrono='{$tabButs['chrono']}'
														AND joueur_event_ref='{$tabPasses['joueur_event_ref']}'
														LIMIT 1");
		}
		$sPas++;

	}
	echo "cpas: " . $cPas . "  sPas: " . $sPas . "  " . $butMAJ -> passeur1Id . "  " . $butMAJ -> passeur2Id;
	while ($sPas > $cPas) {			mysqli_query($conn,"DELETE FROM TableEvenement0 WHERE match_event_id='{$butMAJ->matchId}'
														AND code=1 
														AND chrono='{$tabButs['chrono']}'
														LIMIT 1");

		$sPas--;
	}
	//echo "cpas: " . $cPas . "  sPas: " . $sPas . "  " . $butMAJ -> passeur1Id . "  " . $butMAJ -> passeur2Id . " noSeq: " . $butMAJ -> noSeq;

	while ($sPas < $cPas) {
		$retour = mysqli_query($conn,"INSERT INTO TableEvenement0 (match_event_id,equipe_event_id,joueur_event_ref,code, souscode,chrono,noSequence) 
			VALUES ('{$butMAJ->matchId}', '{$butMAJ->equipeId}','{$passeurs[$sPas]}',1,'{$sc}','{$tabButs['chrono']}',{$butMAJ -> noSeq })") or die(mysqli_error($conn)." erreur Insert Passeurs");
		$sPas++;
	}
	
	if($butMAJ -> marqueurId == null || $butMAJ -> marqueurId == "0"){
		mysqli_query($conn,"UPDATE TableEvenement0 SET joueur_event_ref='{$butMAJ->marqueurId}' ,souscode ='{$sc}' WHERE match_event_id='{$butMAJ->matchId}'
		AND code=0 AND chrono='{$tabButs['chrono']}'");

//				mysqli_query($conn,"DELETE FROM TableEvenement0 WHERE match_event_id='{$butMAJ->matchId}'
//														AND (code=0 OR code=1)
//														AND chrono='{$tabButs['chrono']}'
//														");
		
	}
	
	//echo "cpas: " . $cPas . "  sPas: " . $sPas . "  " . $butMAJ -> passeur1Id . "  " . $butMAJ -> passeur2Id;
	//echo " / rep insert Passeurs ".mysql_num_rows($retour);
}
echo 1;
mysqli_close($conn);
//echo $tabButs[$butMAJ->noSeq]->chrono;
?>

