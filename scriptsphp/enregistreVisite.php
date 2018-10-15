<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
$heure = $_POST['date'];
$usager = $_POST['userId'];
$fakeId = $_POST['fakeId'];
$ligueId = $_POST['ligueId'];
$location = $_POST['location'];
$referrer = $_POST['referrer'];




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

if($usager!=""){
$resultUser = mysql_query("SELECT noCompte FROM TableUser where username='$usager'")
or die(mysql_error());  
$rangUser=mysql_num_rows($resultUser);
if($rangUser>0)
{$tmpUsr=mysql_fetch_row($resultUser);
$userId=$tmpUsr[0];}
else {
	
$erreurExist = 0;
}

}
else{
$userId=0;	
	
}
if($fakeId==""){
	$qMaxFake = "SELECT MAX(fakeId) FROM Visites"; 
	 
$resMaxFake = mysql_query($qMaxFake) or die(mysql_error());
$tmpMax=mysql_fetch_row($resMaxFake);
$fakeId=$tmpMax[0]+1;
}
	
	
		$query = "INSERT INTO Visites (userId,fakeId,ligueId,location,referrer,date) ".
		"VALUES ('{$userId}','{$fakeId}','{$ligueId}','{$location}','{$referrer}','{$heure}')";
		mysql_query($query) or die("Erreur: "+$query+"\n"+mysql_error());
		
		echo $fakeId;
	mysql_close();
?>
