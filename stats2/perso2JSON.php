		
<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';
$tableUser = 'TableUser';

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");


function trouveIDParNomUser($nomUser,$conn)
{
$fResultUser = mysqli_query($conn,"SELECT noCompte 
								FROM TableUser 
								WHERE username='{$nomUser}'")
or die(mysqli_error($conn));  
$rU = mysqli_fetch_row($fResultUser);
if (mysqli_num_rows($fResultUser)>0)
{
return $rU[0];
}
else{return -1;}

}	

$id = $_POST['id'];
//echo  $id.", " ;
if(!is_numeric($id))
{$id=trouveIDParNomUser($id,$conn);}


$resultEquipe = mysqli_query($conn,"SELECT * FROM TableUser WHERE noCompte='{$id}'")
or die(mysqli_error($conn));  
$boule=0;
$JSONstring = "{";
	while($rangee=mysqli_fetch_array($resultEquipe))
{
	$uId=$rangee['noCompte'];
$JSONstring .= "\"username\": \"".$rangee['username']."\",";
$JSONstring .= "\"prenom\": \"".$rangee['prenom']."\",";
$JSONstring .="\"nom\": \"".$rangee['nom']."\",";
$JSONstring .="\"type\": \"".$rangee['type']."\",";
$JSONstring .="\"sexe\": \"".$rangee['sexe']."\",";	
$JSONstring .="\"taille\": \"".$rangee['taille']."\",";	
$JSONstring .="\"id\": \"".$rangee['noCompte']."\",";
$JSONstring .="\"poid\": \"".$rangee['poid']."\",";	
$JSONstring .="\"codePostal\": \"".$rangee['codePostal']."\",";	
$JSONstring .="\"courriel\": \"".$rangee['courriel']."\",";	
$JSONstring .="\"noTel\": \"".$rangee['noTel']."\",";	
$JSONstring .="\"ficIdPortrait\": \"".$rangee['ficIdPortrait']."\",";	
$JSONstring .="\"dateInscription\": \"".$rangee['dateInscription']."\",";	

}
$JSONstring .="\"joueurs\": [";
if($uId!=null)
{
$resultJoueur = mysqli_query($conn, "SELECT * FROM TableJoueur WHERE proprio='{$uId}'")
or die(mysqli_error($conn));  
$boule=0;
while($rangJoueur=mysqli_fetch_array($resultJoueur))
{
	$boule=1;
$JSONstring .= "\"".$rangJoueur['joueur_id']."\",";
}
		if($boule>0)
			$JSONstring = substr($JSONstring, 0,-1);
}
	$JSONstring .= "],";
	
$JSONstring .="\"arbitres\": [";
if($uId!=null)
{
$resultArbitre = mysqli_query($conn,"SELECT * FROM TableArbitre WHERE userId='{$uId}'")
or die(mysqli_error($conn));  
$boule=0;
while($rangArbitre=mysqli_fetch_array($resultArbitre))
{
	$boule=1;
$JSONstring .= "\"".$rangArbitre['arbitreId']."\",";
}
		if($boule>0)
			$JSONstring = substr($JSONstring, 0,-1);
}

	$JSONstring .= "]}";


echo $JSONstring;	
mysqli_close($conn);

?> 

