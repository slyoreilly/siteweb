<?php require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

if (!mysql_connect($db_host, $db_user, $db_pwd))
	die("Can't connect to database");

if (!mysql_select_db($database)) {
	echo "<h1>Database: {$database}</h1>";
	echo "<h1>Table: {$table}</h1>";
	die("Can't select database");

}
$strMAJ = $_POST['strMAJ'];

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

$fusMAJ = json_decode(stripslashes($strMAJ));
//echo stripslashes(json_encode($butMAJ));
//echo $butMAJ->matchId;

$resultFus = mysql_query("SELECT * 
												FROM TableEvenement0 
													WHERE match_event_id='{$fusMAJ->matchId}'
														AND code='2' ORDER BY chrono");
//	echo $_POST['strMAJ'];
if ($fusMAJ -> nouveaufus) {
	$cRow = 0;
	while ($row = mysql_fetch_assoc($resultFus)) {
		$cRow++;
	}
	$fusMAJ -> noSeq = $cRow;
	$ajouteFus = mysql_query("INSERT INTO TableEvenement0 (`match_event_id`, `equipe_event_id`, `joueur_event_ref`, `code`, `souscode`, `chrono`, `noSequence`) 
	VALUES ('{$fusMAJ->matchId}','{$fusMAJ->equipeId}','{$fusMAJ->marqueurId}',2,'{$fusMAJ -> reussiManque}','{$fusMAJ->chrono}','{$cRow}')");
	



} else {

	$cRow = 0;
	while ($row = mysql_fetch_assoc($resultFus)) {
		if ($fusMAJ -> noSeq == $cRow) {$tabButs = $row;
		}
		$cRow++;
	}

	echo stripslashes(json_encode($tabButs));

	mysql_query("UPDATE TableEvenement0 SET joueur_event_ref='{$fusMAJ->marqueurId}', souscode = '{$fusMAJ}' -> reussiManque WHERE match_event_id='{$fusMAJ->matchId}'
														AND code=2 AND chrono='{$tabButs['chrono']}'");



	
}
echo 1;
//echo $tabButs[$butMAJ->noSeq]->chrono;
?>

