<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$usager = $_POST['usager'];
$pass = $_POST['pass'];
$pass2 = $_POST['pass2'];
$codePostal = $_POST['codePostal'];
$courriel = $_POST['courriel'];
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

if($pass==$pass2)
$erreurPass =0;
else {$erreurPass =1;}

$resultUser = mysql_query("SELECT * FROM TableUser where username='$usager'")
or die(mysql_error());  
$rangUser=mysql_num_rows($resultUser);
if($rangUser>0)
$erreurExist = 1;
else {
$erreurExist = 0;
}



//////////////////////////////////
//
//	Mise � jour des bases de donn�es
//
//////////////////////////////////

if($code==1)
	if($erreurPass==0&&$erreurExist==0)  // Pas d'erreur.
{
	$query_ligue = "INSERT INTO TableUser (username, nom, prenom, password, type, codePostal, courriel, noTel) ".
"VALUES ('$usager','$nom','$prenom','$pass',30,'$codePostal', '$courriel', '$noTel')";  //type 30: utilisateur non-payant
		
$retour = mysql_query($query_ligue);



//	mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono, match_event_id) 
//VALUES ( 'test	Match2', 'testMatch2', 'testMatch2', 'testMatch2','testMatch2')");	
}
	
	if($code==10)  // Code 10:  Modifie ligue existante.
	{
		if($erreurPass==0)
		
	$query_update = "UPDATE TableUser SET nom='$nom', username='$usager', password='$pass', codePostal='$codePostal', courriel='$courriel', noTel='$noTel' , prenom='$prenom' WHERE username= '$usager'";	
	mysql_query($query_update);	
	
	}

echo "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
<html>
<head>
<title>Un instant...</title>
<meta http-equiv=\"REFRESH\" content=\"0;url=http://www.syncstats.com/login.html\"</HEAD>
<BODY>
Mise � jour termin�e, redirection.
</BODY>
</HTML>
";

?>
