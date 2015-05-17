<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

//$fichier = $_POST['fichier'];
$params = $_POST['params'];

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

$querySel = "SELECT content FROM TableFichier WHERE ficId=247 ORDER BY ficId DESC ";
$retSel = mysql_query($querySel) or die("Erreur: "+$querySel+"\n"+mysql_error());
$are = mysql_fetch_row($retSel);
echo stripslashes($are[0]);

//////////////////////////////////
//
//	Les queries
//


//include 'library/closedb.php';
	
?>
