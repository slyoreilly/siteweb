<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$usager = $_GET['usager'];
$joueurId = $_GET['joueurId'];
$code = $_POST['code'];
$noTel ="0";

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	


//////////////////////////////////
//
//	V�rifications
//
//////////////////////////////////




//////////////////////////////////
//
//	Mise � jour des bases de donn�es
//
//////////////////////////////////

	$query_update = "UPDATE TableJoueur SET proprio={$usager} WHERE joueur_id={$joueurId}";	
	mysql_query($query_update);	
	

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Un instant...</title>
<meta http-equiv="REFRESH" content="0;url=http://www.syncstats.com/zuser/monprofil.html"></HEAD>
<BODY>
Mise à jour terminée, redirection.
</BODY>
</HTML>