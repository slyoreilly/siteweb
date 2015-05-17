<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

$table = $_POST['table'];
if(strcmp($table, "EvaluationJoueurs")==0)
{$ou = stripslashes(str_replace("'","",$_POST['ou']));}
else
{$ou = str_replace("'","",$_POST['ou']);}
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
	$qSelOu ="SELECT *
						FROM $table
						 WHERE $ou"; 
	$retour = mysql_query($qSelOu)or die(mysql_error()."   /  ".$qSelOu);
}

$vecMatch = array();
while($r = mysql_fetch_assoc($retour)) {
    $vecMatch[] = $r;
	}
$tmp=json_encode($vecMatch);
$adomper=$tmp;

if(strcmp($table, "EvaluationJoueurs")==0)
{$adomper= stripslashes($tmp);}


//
$adomper =str_replace( '"[' ,'[',$adomper );
$adomper =str_replace( ']"' ,']',$adomper );

echo $tmp;
//echo $adomper;
//echo json_encode($vecMatch)

	//		header("HTTP/1.1 200 OK");
?>

