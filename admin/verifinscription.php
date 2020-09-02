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




$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");


//////////////////////////////////
//
//	V�rifications
//
//////////////////////////////////

if($pass==$pass2)
$erreurPass =0;
else {$erreurPass =1;}

$resultUser = mysqli_query($conn,"SELECT * FROM TableUser where username='$usager'")
or die(mysqli_error($conn));  
$rangUser=mysqli_num_rows($resultUser);
if($rangUser>0)
$erreurExist = 1;
else {
$erreurExist = 0;
}

$succes=0;

//////////////////////////////////
//
//	Mise � jour des bases de donn�es
//
//////////////////////////////////

if($code==1)
	if($erreurPass==0&&$erreurExist==0)  // Pas d'erreur.
{
	$query_ligue = "INSERT INTO TableUser (username, nom, prenom, password, type, codePostal, courriel, noTel,ref_id,sexe,taille,poid,dateInscription) ".
"VALUES ('$usager','$nom','$prenom','$pass',30,'$codePostal', '$courriel', '$noTel',0,'M',0,0,NOW())";  //type 30: utilisateur non-payant
		
$retour = mysqli_query($conn,$query_ligue)or die(mysqli_error($conn));

$succes=1;

//	mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono, match_event_id) 
//VALUES ( 'test	Match2', 'testMatch2', 'testMatch2', 'testMatch2','testMatch2')");	
}
else {
	$succes=0;
}
	
	if($code==10||$code==40)  // Code 10:  Modifie ligue existante.
	{
		if($erreurPass==0)
	{	
		$query_update = "UPDATE TableUser SET username='$usager', password='$pass', codePostal='$codePostal', courriel='$courriel', noTel='$noTel' , prenom='$prenom' WHERE username= '$usager'";	
	mysqli_query($conn,$query_update)or die(mysqli_error($conn));
	$succes=1;
	}
	
	}
	
	if($code==12)  // Code 12:  changeUsername
	{
		if($erreurPass==0)
		{	
	$query_update = "UPDATE TableUser SET username='{$_POST['nouveauUsager']}' WHERE username= '$usager'";	
		mysqli_query($conn,$query_update)or die(mysqli_error($conn));
		$succes=1;
		}	
	}
	
	if($code==13)  // Code 13:  changePass
	{
		if($erreurPass==0)
		{	
		$query_update = "UPDATE TableUser SET password='$pass' WHERE username= '$usager' and password='{$_POST['ancienPass']}'";	
		$ret=mysqli_query($conn,$query_update)or die(mysqli_error($conn));
		if(mysqli_affected_rows($conn)>0)
			{$succes=1;}
		else {
			$succes=0;
		}
		}	
	}
	
	
	if($code==30)
	{
		$ret = mysqli_query($conn,"SELECT username
						FROM TableUser
						WHERE username='$usager'")or die(mysqli_error($conn));
		if(mysqli_num_rows($ret)>0)
			{
				$erreurExist=1;
				$succes=0;
			}
		else
			{
				$erreurExist=0;
				$succes=1;
			}
		
	}
	

$jsonErreur = "{\"succes\":$succes, \"erreurPass\":$erreurPass,\"erreurExist\":$erreurExist,\"usager\":\"$usager\",\"pass\":\"$pass\"}";
echo $jsonErreur;
mysqli_close($conn);
?>
