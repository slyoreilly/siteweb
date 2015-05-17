<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

header ('Content-type: text/html; charset=utf-8'); 


$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$username = $_POST['username'];
//$password = $_POST['password'];
//$matchjson = stripslashes($_POST['matchjson']);
$matchjson = stripslashes($_POST['matchjson']);

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

		
//$json=json_decode("'".$matchjson."'");
$leMatch = json_decode($matchjson, true);
for($a=0;$a<count($leMatch);$a++)
{
	$qSel="SELECT equipe_id FROM TableEquipe WHERE equipe_id='{$leMatch[$a]['vieuId']}'";
	$retour = mysql_query($qSel)or die(mysql_error()." sel bug");	
	if(mysql_num_rows($retour)==0)
	{
		$retour = mysql_query("INSERT INTO TableEquipe (nom_equipe,logo,ficId, equipeActive,dernierMAJ) 
		VALUES ('{$leMatch[$a]['nomEquipe']}','{$leMatch[$a]['logo']}', 16, 1, NOW())") or die(mysql_error()."insert bug");			
	}
	else {
	$qUp="UPDATE TableEquipe SET nom_equipe='{$leMatch[$a]['nomEquipe']}',logo='{$leMatch[$a]['logo']}', ficId=16, equipeActive=1,dernierMAJ=NOW() WHERE equipe_id='{$leMatch[$a]['vieuId']}'";	
	$retour = mysql_query($qUp)or die(mysql_error()." sel bug");	
	}
	
}

			header("HTTP/1.1 200 OK");

?>
