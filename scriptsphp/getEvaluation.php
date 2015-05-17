<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
$evalueId = $_POST['evalueId'];
$evaluateurId = $_POST['evaluateurId'];
$ligueId = $_POST['ligueId'];


//$ligueId = $_POST['ligueId'];

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	
//	mysql_query("SET NAMES 'utf8'");
//mysql_query("SET CHARACTER SET 'utf8'");
	mysql_set_charset('utf8');
	
if($ou==NULL)
{	
$retour = mysql_query("SELECT *
						FROM $table
						 WHERE 1")or die(mysql_error());	
}

else {
	$retour = mysql_query("SELECT *
						FROM $table
						 WHERE $ou")or die(mysql_error());
}

$vecMatch = array();
while($r = mysql_fetch_assoc($retour)) {
    $vecMatch[] = $r;
	}
$tmp=json_encode($vecMatch);
$adomper=$tmp;
//$adomper= stripslashes($tmp);
$adomper =str_replace( '"[' ,'[',$adomper );
$adomper =str_replace( ']"' ,']',$adomper );

echo $tmp;
//echo $adomper;
//echo json_encode($vecMatch)

	//		header("HTTP/1.1 200 OK");
?>

