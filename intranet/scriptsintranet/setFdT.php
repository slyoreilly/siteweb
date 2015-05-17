<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'intrasyncstats';

$employeId = $_POST['employeId'];
$date = $_POST['date'];
$nbHeures = $_POST['nbHeures'];
$codeTache = $_POST['codeTache'];
$description = $_POST['description'];




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
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

//////////////////////////////////
//
//	Les queries
//

	$query_equipe = "INSERT INTO feuilledetemps (employeId,date, nbHeures, codeTache,description) ".
"VALUES ('$employeId', '$date', '$nbHeures','$codeTache','$description')";
		
		$retour = mysql_query($query_equipe) or die("Erreur: ".$query_equipe.mysql_error);
echo "et là \n";


	
?>
