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


$fusMAJ = json_decode(stripslashes($strMAJ));
//echo stripslashes(json_encode($butMAJ));
//echo $butMAJ->matchId;

$resultFus = mysqli_query($conn, "SELECT * 
												FROM TableEvenement0 
													WHERE match_event_id='{$fusMAJ->matchId}'
														AND code='2' ORDER BY chrono");
//	echo $_POST['strMAJ'];
if ($fusMAJ -> nouveaufus) {
	$cRow = 0;
	while ($row = mysqli_fetch_assoc($resultFus)) {
		$cRow++;
	}
	$fusMAJ -> noSeq = $cRow;
	$ajouteFus = mysqli_query($conn, "INSERT INTO TableEvenement0 (`match_event_id`, `equipe_event_id`, `joueur_event_ref`, `code`, `souscode`, `chrono`, `noSequence`) 
	VALUES ('{$fusMAJ->matchId}','{$fusMAJ->equipeId}','{$fusMAJ->marqueurId}',2,'{$fusMAJ -> reussiManque}','{$fusMAJ->chrono}','{$cRow}')");
	



} else {

	$cRow = 0;
	while ($row = mysqli_fetch_assoc($resultFus)) {
		if ($fusMAJ -> noSeq == $cRow) {$tabButs = $row;
		}
		$cRow++;
	}

	echo stripslashes(json_encode($tabButs));

	mysqli_query($conn, "UPDATE TableEvenement0 SET joueur_event_ref='{$fusMAJ->marqueurId}', souscode = '{$fusMAJ}' -> reussiManque WHERE match_event_id='{$fusMAJ->matchId}'
														AND code=2 AND chrono='{$tabButs['chrono']}'");



	
}
echo 1;
//echo $tabButs[$butMAJ->noSeq]->chrono;
?>

