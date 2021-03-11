<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
$arbitreId = $_POST['arbitreId'];
$dispo = $_POST['dispo'];
$region = $_POST['region'];

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
	
if($arbitreId!=undefined&&$arbitreId!=null)
{
	if($dispo!=undefined&&$dispo!=null)
			{$retour = mysql_query("UPDATE TableArbitre
						SET dispo= '{$dispo}'
						 WHERE TableArbitre.arbitreId='{$arbitreId}'")or die(mysql_error());	
			}
		if($region!=undefined&&$region!=null)
			{$retour = mysql_query("UPDATE TableArbitre
						SET region= '{$region}'
						 WHERE TableArbitre.arbitreId='{$arbitreId}'")or die(mysql_error());	
			}

$adomper= true;
	
}
else{
$adomper= false;
}


echo $adomper;


	//		header("HTTP/1.1 200 OK");
?>

