<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'intrasyncstats';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
//$arbitreId = $_POST['arbitreId'];
//$ligueId = $_POST['ligueId'];

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
	
$retour = mysql_query("SELECT feuilledetemps.*
						FROM feuilledetemps
						 WHERE 1")or die(mysql_error());	
$vecMatch = array();
while($r = mysql_fetch_assoc($retour)) {
    $vecMatch[] = $r;
	}
$adomper= stripslashes(json_encode($vecMatch));

$adomper =str_replace ( '"[' ,'[',$adomper );
$adomper =str_replace ( ']"' ,']',$adomper );
echo $adomper;


	//		header("HTTP/1.1 200 OK");
?>

