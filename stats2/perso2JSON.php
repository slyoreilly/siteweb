		
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

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}
	
	mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");


function trouveIDParNomUser($nomUser)
{
$fResultUser = mysql_query("SELECT noCompte 
								FROM TableUser 
								WHERE username='{$nomUser}'")
or die(mysql_error());  
$rU = mysql_fetch_row($fResultUser);
if (mysql_num_rows($fResultUser)>0)
{
return $rU[0];
}
else{return -1;}

}	

$id = $_POST['id'];

if(!is_numeric($id))
{$id=trouveIDParNomUser($id);}


$resultEquipe = mysql_query("SELECT * FROM TableUser WHERE noCompte='{$id}'")
or die(mysql_error());  
$boule=0;
$JSONstring = "{";
	while($rangee=mysql_fetch_array($resultEquipe))
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
$resultJoueur = mysql_query("SELECT * FROM TableJoueur WHERE proprio='{$uId}'")
or die(mysql_error());  
$boule=0;
while($rangJoueur=mysql_fetch_array($resultJoueur))
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
$resultArbitre = mysql_query("SELECT * FROM TableArbitre WHERE userId='{$uId}'")
or die(mysql_error());  
$boule=0;
while($rangArbitre=mysql_fetch_array($resultArbitre))
{
	$boule=1;
$JSONstring .= "\"".$rangArbitre['arbitreId']."\",";
}
		if($boule>0)
			$JSONstring = substr($JSONstring, 0,-1);
}

	$JSONstring .= "]}";


echo $JSONstring;	


?> 

