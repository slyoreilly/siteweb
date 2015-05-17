<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'intrasyncstats';

$valeur = json_decode($_POST['valeur']);
$table = $_POST['table'];
$mode = $_POST['mode'];
$critere = str_replace("'","",$_POST['critere']);




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
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

//////////////////////////////////
//
//	Les queries
//

if(strcmp($mode,'creer')==0)
{

	$query_set = "INSERT INTO $table (";
	
	foreach ($valeur as $key=>$value)
	{
		$query_set .=$key.",";
	}	
	$query_set=substr($query_set,0,-1);
	$query_set.=") VALUES (";
	foreach ($valeur as $key => $value)
	{
		if(strcmp($value,'NULL')==0)
		$query_set .="NULL,";
		else
		$query_set .="'".$value."',";
	}
	$query_set=substr($query_set,0,-1);
	$query_set.=")";
		
		$retour = mysql_query($query_set) or die("Erreur: ".$query_set.mysql_error().json_encode($valeur));
}

if(strcmp($mode,'modif')==0)
{

	$query_set = "UPDATE $table SET ";
	
	foreach ($valeur as $key=>$value)
	{
		if(strcmp($value,'NULL')==0)
		$query_set .=$key."=NULL,";
		else
		$query_set .=$key."='".$value."',";
	}	
	$query_set=substr($query_set,0,-1);
	$query_set.=" WHERE ";
	$query_set.=$critere;
		
		$retour = mysql_query($query_set) or die("Erreur: ".$query_set.mysql_error().json_encode($valeur));
}



	
?>
