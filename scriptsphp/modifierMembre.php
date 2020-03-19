<?php
require '../scriptsphp/defenvvar.php';nt0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';




function trouveIDParNomUser($nomLi)
{
$resultUser = mysql_query("SELECT * FROM TableUser")
or die(mysql_error());  
while($rangeeUser=mysql_fetch_array($resultUser))
{
		if(!strcmp($rangeeUser['username'],$nomLi))
	{$UserID =$rangeeUser['noCompte'];// Ce sont de INT
	}
}
return $UserID;
}


if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	
$nom =  mysql_real_escape_string($_POST['nom']);
$prenom =  mysql_real_escape_string($_POST['prenom']);
$codePostal = $_POST['codePostal'];
$courriel = $_POST['courriel'];
$usager = $_POST['usager'];
	
	
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////



//		$resultFic = mysql_query("SELECT ficId FROM Ligue WHERE ID_Ligue= '$ligueId'")or die (mysql_error());
		
		
		
	$query_update = "UPDATE TableUser SET nom='$nom', prenom='$prenom',codePostal='$codePostal' , courriel='$courriel' WHERE username= '$usager'";	
	mysql_query($query_update)or die (mysql_error());	
	
	
//include 'library/closedb.php';
	

?>


