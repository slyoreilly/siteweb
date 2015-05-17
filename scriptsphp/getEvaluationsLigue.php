<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
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
	
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");
//	mysql_set_charset('utf8');
	
	
$qIndJDom =	"SELECT evalue,AVG(valeur)
						FROM EvaluationJoueurs
						 WHERE ligueId=$ligueId
						 GROUP BY evalue";
$mEval = mysql_query($qIndJDom)or die(mysql_error().$qIndJDom);	

$b=0;
while($r = mysql_fetch_array($mEval)) {
    $vecEval[$b][0] = $r[0];
	    $vecEval[$b][1] = $r[1];
	
$b++;	
}
							 	

echo json_encode($vecEval);


		header("HTTP/1.1 200 OK");
?>

