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

$ancienEqId=  $_POST['ancienEqId'];
$eqId=  $_POST['eqId'];
$matchId=  $_POST['matchId'];

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, "utf8");



	mysqli_query($conn,"UPDATE TableEvenement0 SET equipe_event_id='{$eqId}' WHERE match_event_id='{$matchId}' AND 
														equipe_event_id='{$ancienEqId}' ");


	mysqli_query($conn,"UPDATE TableMatch SET eq_vis='{$eqId}' WHERE matchIdRef='{$matchId}' AND 
														eq_vis='{$ancienEqId}'"); 
	mysqli_query($conn,"UPDATE TableMatch SET eq_dom='{$eqId}' WHERE matchIdRef='{$matchId}' AND 
														eq_dom='{$ancienEqId}'");

mysqli_close($conn);
//echo $tabButs[$butMAJ->noSeq]->chrono;
?>

