<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];

$telId = $_POST['telId'];
$retour=array();
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
	
	
	$qSel="SELECT * FROM Controle WHERE telId='{$telId}'";	
	$retSel=mysql_query($qSel) or die("Erreur: "+$qSel+"\n"+mysql_error());
	$cpt= 0;
	while($rangee = mysql_fetch_assoc($retSel))
		{
		
		$retour[$cpt] = $rangee;
		$cpt++;
		}
	
	echo json_encode($retour);
	
	$qUp="UPDATE  Controle SET etatSync=12 WHERE telId='{$telId}'";	
	$retUp=mysql_query($qUp) or die("Erreur: "+$qUp+"\n"+mysql_error());
	
	if(json_encode($retour)==False)
	{echo "erreur, count(syncOK:): ".count($retour)."- count($retour): ".count($retour);}
mysql_close();

?>
