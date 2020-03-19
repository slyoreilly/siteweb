		
<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';
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

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");



function trouveIDParNomUser($nomUser)
{
$fResultUser = mysqli_query($conn, "SELECT noCompte 
								FROM TableUser 
								WHERE username='{$nomUser}'")
or die(mysqli_error());  
$rU = mysqli_fetch_row($fResultUser);
if (mysqli_num_rows($fResultUser)>0)
{
return $rU[0];
}
else{return -1;}

}	

$id = $_GET['id'];

if(!is_numeric($id))
{$id=trouveIDParNomUser($id);}


$resultEquipe = mysqli_query($conn, "SELECT * FROM TableUser WHERE noCompte='{$id}'")
or die(mysqli_error());  
$joueurs=Array();

	while($rangee=mysqli_fetch_array($resultEquipe))
{
	$uId=$rangee['noCompte'];

$joueurs=Array();
$joueurs['username']=$rangee['username'];
$joueurs['prenom']=$rangee['prenom'];
$joueurs['nom']=$rangee['nom'];
$joueurs['type']=$rangee['type'];
$joueurs['sexe']=$rangee['sexe'];
$joueurs['taille']=$rangee['taille'];
$joueurs['id']=$rangee['noCompte'];
$joueurs['poid']=$rangee['poid'];
$joueurs['codePostal']=$rangee['codePostal'];
$joueurs['courriel']=$rangee['courriel'];
$joueurs['noTel']=$rangee['noTel'];
$joueurs['ficIdPortrait']=$rangee['ficIdPortrait'];
$joueurs['dateInscription']=$rangee['dateInscription'];

}

if($uId!=null)
{
$resultJoueur = mysqli_query($conn, "SELECT * FROM TableJoueur WHERE proprio='{$uId}'")
or die(mysqli_error());  

$mJ=null;
while($rangJoueur=mysqli_fetch_array($resultJoueur))
{
	$mJ=$rangJoueur['joueur_id'];
}

}
	$joueurs['joueur_id']=$mJ;
	
if($uId!=null)
{
$resultJoueur = mysqli_query($conn, "SELECT * FROM TableArbitre WHERE userId='{$uId}'")
or die(mysqli_error());  

$mA=null;
while($rangJoueur=mysqli_fetch_array($resultJoueur))
{
	$mA=$rangJoueur['arbitreId'];
}

}
	$joueurs['arbitreId']=$mA;	

echo json_encode($joueurs);	


?> 

