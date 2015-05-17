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
	$qSel="SELECT joueur_id FROM TableJoueur WHERE joueur_id='{$leMatch[$a][vieuId]}'";
	$retour = mysql_query($qSel)or die(mysql_error()." sel bug");	
	if(mysql_num_rows($retour)==0)
	{
		$retour = mysql_query("INSERT INTO {$tableJoueur} (joueur_id,NomJoueur, NumeroJoueur, equipe_id_ref, Ligue, ficIdPortrait) 
		VALUES ('{$leMatch[$a][vieuId]}','{$leMatch[$a]['nomJoueur']}', '{$leMatch[$a]['noJoueur']}', NULL, NULL,95)")or die(mysql_error()."insert bug");			
	}
	else {
	$qUp="UPDATE TableJoueur SET nomJoueur='{$leMatch[$a]['nomJoueur']}', noJoueur='{$leMatch[$a]['noJoueur']}' WHERE joueur_id='{$leMatch[$a][vieuId]}'";	
	$retour = mysql_query($qUp)or die(mysql_error()." sel bug");	
	}
	
}
		
			header("HTTP/1.1 200 OK");
?>
