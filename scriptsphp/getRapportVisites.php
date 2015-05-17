<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
$dateInf = $_POST['dateInf'];
$dateSup = $_POST['dateSup'];
$ligueId = $_POST['ligueId'];


//$heure = $_POST['date'];
//$usager = $_POST['userId'];
//$fakeId = $_POST['fakeId'];

//$location = $_POST['location'];
//$referrer = $_POST['referrer'];




if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");


	
		$query = "SELECT * 
					FROM Visites 
					WHERE date > '{$dateInf}' AND date < '{$dateSup}'";
		$res=mysql_query($query) or die("Erreur: "+$query+"\n"+mysql_error());
		
		$rows = array();

while($r = mysql_fetch_array($res)) {
    $rows[] = $r;}
echo json_encode($rows);
		
?>
