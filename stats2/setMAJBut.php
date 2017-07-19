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

$butMAJ = json_decode(stripslashes($strMAJ));
//echo stripslashes(json_encode($butMAJ));
//echo $butMAJ->matchId;

$resultBut = mysql_query("SELECT * 
												FROM TableEvenement0 
													WHERE match_event_id='{$butMAJ->matchId}'
														AND code='0' ORDER BY chrono");
//	echo $_POST['strMAJ'];
if ($butMAJ -> nouveaubut) {

	$cRow = 0;
	while ($row = mysql_fetch_assoc($resultBut)) {
		$cRow++;
	}
	$butMAJ -> noSeq = $cRow;
	$ajouteBut = mysql_query("INSERT INTO TableEvenement0 (`match_event_id`, `equipe_event_id`, `joueur_event_ref`, `code`, `souscode`, `chrono`, `noSequence`) 
	VALUES ('{$butMAJ->matchId}','{$butMAJ->equipeId}','{$butMAJ->marqueurId}',0,0,'{$butMAJ->chrono}','{$cRow}')");
	


		if ($butMAJ -> passeur1Id != null) {
		$ajouteBut = mysql_query("INSERT INTO TableEvenement0 (`match_event_id`, `equipe_event_id`, `joueur_event_ref`, `code`, `souscode`, `chrono`, `noSequence`) 
	VALUES ('{$butMAJ->matchId}','{$butMAJ->equipeId}','{$butMAJ->passeur1Id}',1,0,'{$butMAJ->chrono}','{$cRow}')");
	
		}
		if ($butMAJ -> passeur2Id != null) {
	$ajouteBut = mysql_query("INSERT INTO TableEvenement0 (`match_event_id`, `equipe_event_id`, `joueur_event_ref`, `code`, `souscode`, `chrono`, `noSequence`) 
	VALUES ('{$butMAJ->matchId}','{$butMAJ->equipeId}','{$butMAJ->passeur2Id}',1,0,'{$butMAJ->chrono}','{$cRow}')");
		}



} else {

	$cRow = 0;
	while ($row = mysql_fetch_assoc($resultBut)) {
		if ($butMAJ -> noSeq == $cRow) {$tabButs = $row;
		}
		$cRow++;
	}

	echo stripslashes(json_encode($tabButs));

	mysql_query("UPDATE TableEvenement0 SET joueur_event_ref='{$butMAJ->marqueurId}' WHERE match_event_id='{$butMAJ->matchId}'
														AND code=0 AND chrono='{$tabButs['chrono']}'");

	$resultPasse = mysql_query("SELECT * 
												FROM TableEvenement0 
													WHERE chrono='{$tabButs['chrono']}'
														AND match_event_id='{$butMAJ->matchId}'
														AND code='1'");

	$cPas = 0;

	if ($butMAJ -> passeur1Id != null) {$passeurs[$cPas] = $butMAJ -> passeur1Id;
		$cPas++;
	}
	if ($butMAJ -> passeur2Id != null) {$passeurs[$cPas] = $butMAJ -> passeur2Id;
		$cPas++;
	}
	$sPas = 0;
	while ($tabPasses = mysql_fetch_array($resultPasse)) {
		if ($cPas != 0 && $sPas == 0) {
			mysql_query("UPDATE TableEvenement0 SET joueur_event_ref='{$passeurs[$sPas]}' WHERE match_event_id='{$butMAJ->matchId}'
														AND code=1 
														AND chrono='{$tabButs['chrono']}'
														AND joueur_event_ref='{$tabPasses['joueur_event_ref']}'
														LIMIT 1");

		}
		if ($cPas == 2 && $sPas == 1) {
			mysql_query("UPDATE TableEvenement0 SET joueur_event_ref='{$passeurs[$sPas]}' WHERE match_event_id='{$butMAJ->matchId}'
														AND code=1 
														AND chrono='{$tabButs['chrono']}'
														AND joueur_event_ref='{$tabPasses['joueur_event_ref']}'
														LIMIT 1");
		}
		$sPas++;

	}
	echo "cpas: " . $cPas . "  sPas: " . $sPas . "  " . $butMAJ -> passeur1Id . "  " . $butMAJ -> passeur2Id;
	while ($sPas > $cPas) {			mysql_query("DELETE FROM TableEvenement0 WHERE match_event_id='{$butMAJ->matchId}'
														AND code=1 
														AND chrono='{$tabButs['chrono']}'
														LIMIT 1");

		$sPas--;
	}
	echo "cpas: " . $cPas . "  sPas: " . $sPas . "  " . $butMAJ -> passeur1Id . "  " . $butMAJ -> passeur2Id . " noSeq: " . $butMAJ -> noSeq;

	while ($sPas < $cPas) {
		$retour = mysql_query("INSERT INTO TableEvenement0 (match_event_id,equipe_event_id,joueur_event_ref,code, souscode,chrono,tempChrono) 
			VALUES ('{$butMAJ->matchId}', '{$tabButs['equipe_event_id']}','{$passeurs[$sPas]}',1,{$tabButs['souscode']},'{$tabButs['chrono']}',{$tabButs['chrono']})");
		$sPas++;
	}
	echo "cpas: " . $cPas . "  sPas: " . $sPas . "  " . $butMAJ -> passeur1Id . "  " . $butMAJ -> passeur2Id;
	echo mysql_num_rows($retour);
}
echo 1;
//echo $tabButs[$butMAJ->noSeq]->chrono;
?>

