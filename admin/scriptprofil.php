<?php

/*******************************************
 * 
 * 	SCRIPTPROFIL.PHP
 * 
 * 	Auteur: Sylvain O'Reilly
 * 	9 mars 2012
 * 
 * Gestion des donn�es relatives � l'entr�e de profil de joueur.
 * 
 * Appel�e de: statsjoueur.html
 * 
 * Redirige vers: login.html  (pageperso.php) 
 * 
 * 
 */

require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$nom = mysql_real_escape_string($_POST['nom']);
$prenom = mysql_real_escape_string($_POST['prenom']);
$numero = $_POST['numero'];
$position = $_POST['position'];
$pseudo = $_POST['pseudo'];
$taille = mysql_real_escape_string($_POST['taille']);
$poids = $_POST['poids'];
$anneeNaissance = $_POST['anneeNaissance'];
$villeOrigine = mysql_real_escape_string($_POST['villeOrigine']);
$code = $_POST['code'];
$sexe =$_POST['sexe'];
$joueurId =$_POST['joueurId'];
$ligueId = $_POST['ligueId'];

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

//////////////////////////////////
//
//	Mise � jour des bases de donn�es
//
//////////////////////////////////


echo "que je cherche";
	
	if($code==1||$code==41)  // Code 10:  Modifie ligue existante.
	{
	$query_update = "UPDATE TableJoueur SET dernierMAJ=NOW(), nom='$nom', prenom='$prenom', NomJoueur='$pseudo',NumeroJoueur='$numero',position='$position', taille='$taille', sexe='$sexe', poids='$poids', anneeNaissance='$anneeNaissance' , villeOrigine='$villeOrigine' WHERE joueur_id= '$joueurId'";	
	mysql_query($query_update)or die('TableJoueur Update '+mysql_error());	
	}

	if($code==40)  // Code 10:  Modifie ligue existante.
	{
echo " que je cherche";

				
		
		
	$query_insert = "INSERT INTO `TableJoueur`(`NomJoueur`, `NumeroJoueur`, `position`, `Ligue`,`Equipe`,`equipe_id_ref`, `nom`, `prenom`, `taille`, `poids`, `sexe`, `anneeNaissance`, `villeOrigine`, `dernierMAJ`,`ficIdJoueur`,`ficIdPortrait`,`proprio`) 
	VALUES ('$pseudo','$numero','$position','$ligueId','aucune',NULL,'$nom','$prenom', '$taille','$poids','$sexe','$anneeNaissance','$villeOrigine',
			NOW(),0,0,0)";	
	echo $query_insert;
			$ret =mysql_query($query_insert) or die('TableJoueur Insert '+mysql_error()); 
	$mes1='TableJoueur Insert '+mysql_error();
	echo " que je cherche";
	
	if($ret)
	{$reqSel="SELECT joueur_id FROM TableJoueur WHERE NomJoueur='$pseudo' ORDER BY joueur_id  DESC";
	$rJID = mysql_query($reqSel)or die('TableJoueur Select '+mysql_error()); 
	$jId=mysql_fetch_array($rJID);
	$requeteInsertAbon = "INSERT INTO `abonJoueurLigue`(`joueurId`, `ligueId`, `permission`, `debutAbon`, `finAbon`) 
	VALUES ({$jId['joueur_id']},'$ligueId',30,NOW(),'2050-01-01')";
		mysql_query($requeteInsertAbon)or die('TableJoueur Abon '+mysql_error()); 
	}	

	
	
		echo $mes1;
	//echo "test";
	}

	
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////
$mes=$_FILES['ficIdPortrait']['size'];
echo $mes;
if($_FILES['ficIdPortrait']['size'] > 0)
{
$fileName = $_FILES['ficIdPortrait']['name'];
$tmpName  = $_FILES['ficIdPortrait']['tmp_name'];
$fileSize = $_FILES['ficIdPortrait']['size'];
$fileType = $_FILES['ficIdPortrait']['type'];

$fp      = fopen($tmpName, 'r');
$content = fread($fp, filesize($tmpName));
$content = addslashes($content);
fclose($fp);

if(!get_magic_quotes_gpc())
{
    $fileName = addslashes($fileName);
}

//include 'library/config.php';
//include 'library/opendb.php';


	$rFic = mysql_query("SELECT ficIdPortrait FROM TableJoueur  WHERE joueur_id= '$joueurId'");	
	$leFic = mysql_fetch_row($rFic);
	
	if($leFic[0]!=0)
	{$queryFic = "UPDATE TableFichier SET contexte='portrait', name='$filename' , size='$filesize', type='$type', content='$content' WHERE ficId='{$leFic[0]}'";
	mysql_query($queryFic) or die('Error, query failed');
	$mes =  "IN!";}
	
	else {
		$query = "INSERT INTO TableFichier (contexte, name, size, type, content ) ".
		"VALUES ('portrait', '$fileName', '$fileSize', '$fileType', '$content')";

		mysql_query($query) or die('Error, query failed');
		$ficIdDernier = mysql_insert_id();
		$query_updateFic = "UPDATE TableJoueur SET  ficIdPortrait='$ficIdDernier' WHERE joueur_id= '{$joueurId}'";	
		mysql_query($query_updateFic);	
	$mes =  "OUT!";	
		
		}





}

echo "que je cherche";
/*

if($code<40)
{
echo "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
<html>
<head>
<title>Un instant...</title>
<meta http-equiv=\"REFRESH\" content=\"0;url=http://www.syncstats.com/login.html;	charset=utf-8\"/></HEAD>
<BODY>
";
echo mysql_error();

echo "
Mise à jour terminée, redirection.
</BODY>
</HTML>";}


else if(isset($ligueId)){
echo "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
<html>
<head>
<title>Un instant...</title>
<meta http-equiv=\"REFRESH\" content=\"0;url=http://www.syncstats.com/gestionjoueursligue.html?ligueId={$ligueId}\"</HEAD>
<BODY>
";
echo $jId['joueur_id'];
echo mysql_error();
echo "
Mise à jour terminée, redirection.
</BODY>
</HTML>";
	
}*/
?>
