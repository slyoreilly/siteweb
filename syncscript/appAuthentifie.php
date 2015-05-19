<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";
$database = 'syncsta1_900';


$username = $_POST['username'];
$mdp =$_POST['password'];



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
	
	$userSelect=array();
	
	// Retrieve all the data from the "example" table
$resultUser = mysql_query("SELECT * FROM TableUser")
or die(mysql_error());  
while($rangeeUser=mysql_fetch_array($resultUser))
{
		if(!strcmp($rangeeUser['username'],$username)&&!strcmp($rangeeUser['password'],$mdp))
	{$userSelect =$rangeeUser;
	}
		// Prend le ID du user pour trouver les ligues abonn�es.
}


/*
	
 */
 header("HTTP/1.1 200 OK");
 header("Content-Type: application/json;charset=utf-8");
 header("Accept: application/json");
//echo " ".count($AbonSelect);
	
		echo json_encode($userSelect);



?>
