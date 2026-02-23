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


mysqli_set_charset($conn, "utf8");

	




$nom = mysqli_real_escape_string($conn,$_POST['nom']);
$prenom = mysqli_real_escape_string($conn,$_POST['prenom']);
$numero = $_POST['numero'];
$position = $_POST['position'];
$pseudo = $_POST['pseudo'];
$taille = $_POST['taille'];
$poids = $_POST['poids'];
$anneeNaissance = $_POST['anneeNaissance'];
$villeOrigine = mysqli_real_escape_string($conn,$_POST['villeOrigine']);
$code = $_POST['code'];
$sexe =$_POST['sexe'];
$joueurId =$_POST['joueurId'];
$ligueId = $_POST['ligueId'];
$ficId = $_POST['ficId'];

//////////////////////////////////
//
//	Mise � jour des bases de donn�es
//
//////////////////////////////////


	
	if($code==1||$code==41)  // Code 10:  Modifie ligue existante.
	{
	$query_update = "UPDATE TableJoueur SET dernierMAJ=NOW(), nom='$nom', prenom='$prenom', NomJoueur='$pseudo',NumeroJoueur='$numero',position='$position', taille='$taille', sexe='$sexe', poids='$poids', anneeNaissance='$anneeNaissance' , villeOrigine='$villeOrigine' , ficIdPortrait='$ficId' WHERE joueur_id= '$joueurId'";	
	mysqli_query($conn,$query_update)or die('TableJoueur Update '.mysqli_error($conn));	
	}

	if($code==40)  // Code 10:  Modifie ligue existante.
	{
				
		
		
	$query_insert = "INSERT INTO `TableJoueur`(`NomJoueur`, `NumeroJoueur`, `position`, `Ligue`,`Equipe`,`equipe_id_ref`, `nom`, `prenom`, `taille`, `poids`, `sexe`, `anneeNaissance`, `villeOrigine`, `dernierMAJ`,`ficIdJoueur`,`ficIdPortrait`,`proprio`) 
	VALUES ('$pseudo','$numero','$position','$ligueId','aucune',NULL,'$nom','$prenom', '$taille','$poids','$sexe','$anneeNaissance','$villeOrigine',
			NOW(),0,0,0)";	
	//echo $query_insert;
			$ret =mysqli_query($conn,$query_insert) or die('TableJoueur Insert '+mysqli_error($conn)); 
	$mes1='TableJoueur Insert '.mysqli_error($conn);
	//echo " que je cherche";
	
	if($ret)
	{$reqSel="SELECT joueur_id FROM TableJoueur WHERE NomJoueur='$pseudo' ORDER BY joueur_id  DESC";
	$rJID = mysqli_query($conn,$reqSel)or die('TableJoueur Select '+mysqli_error($conn)); 
	$jId=mysqli_fetch_array($rJID);
	$requeteInsertAbon = "INSERT INTO `abonJoueurLigue`(`joueurId`, `ligueId`, `permission`, `debutAbon`, `finAbon`) 
	VALUES ({$jId['joueur_id']},'$ligueId',30,NOW(),'2050-01-01')";
		mysqli_query($conn,$requeteInsertAbon)or die('TableJoueur Abon '+mysqli_error($conn)); 
			echo $jId['joueur_id'];
	
	}
	else{
		echo "Erreur!";
	}	

	
	
	//echo "test";
	}

	//mysqli_close($conn);
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
