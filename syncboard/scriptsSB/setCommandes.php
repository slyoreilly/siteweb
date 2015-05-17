<?php
$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_910';

if (!mysql_connect($db_host, $db_user, $db_pwd))
	die("Can't connect to database");

if (!mysql_select_db($database)) {
	echo "<h1>Database: {$database}</h1>";
	echo "<h1>Table: {$table}</h1>";
	die("Can't select database");
}

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

$valeur = $_POST['valeur'];
$cle = $_POST['cle'];
$matchId = $_POST['matchId'];
//////////////////////////////////
//
//	Les queries
//


	$query_set = "UPDATE commandeboard SET valeur='{$valeur}'  WHERE matchId='{$matchId}' AND cle='{$cle}'";
	$isMAJ =mysql_query($query_set) or die("Erreur ecrase: " . $query_set . mysql_error());
	echo $isMAJ;
	if (mysql_affected_rows()<1)
	{
		$query_set = "INSERT commandeBoard ( matchId, cle, valeur ) VALUES ('{$matchId}','{$cle}','{$valeur}')";

		$retour = mysql_query($query_set) or die("Erreur ecrase: " . $query_set . mysql_error() . json_encode($valeur) . $_POST['valeur']);

	}

?>
