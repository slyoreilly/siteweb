<?php


/*****************************************************
 * 
 * 	profilDeJoueurId
 * 
 * 	Cr�� par Sylvain O'Reilly
 * 
 * Envoie �: entreprofil.html
 * 
 * Re�oit de:entreprofil.html
 * 
 */

 
 
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

$joueurId = $_POST['joueurId'];

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
mysqli_set_charset($conn, "utf8");

///////////////////////////////////////////////////////////
//
//	D�but du corps
//
///////////////////////////////////////////////////////////


	$strSelect = "joueur_id,NomJoueur,NumeroJoueur,position,equipe_id_ref,Ligue,nom,prenom,taille,poids,sexe,anneeNaissance,villeOrigine,ficIdJoueur,ficIdPortrait,proprio";
$rJoueur = mysqli_query($conn,"SELECT $strSelect FROM TableJoueur WHERE  joueur_id = '$joueurId'")
or die(mysqli_error($conn));  
$monJoueur=mysqli_fetch_row($rJoueur);




	//////////////////////////////////////////////////
	//
	// 	�crit JSON
	
	$JSONstring = "{\"joueurId\": \"". $joueurId."\",";
		$JSONstring .= "\"nomJoueur\": \"". $monJoueur[1]."\",";
		$JSONstring .= "\"numeroJoueur\": \"". $monJoueur[2]."\",";
		$JSONstring .= "\"position\": \"". $monJoueur[3]."\",";
		$JSONstring .= "\"equipeId\": \"". $monJoueur[4]."\",";
		$JSONstring .= "\"ligueId\": \"". $monJoueur[5]."\",";
		$JSONstring .= "\"nom\": \"". $monJoueur[6]."\",";
		$JSONstring .= "\"prenom\": \"". $monJoueur[7]."\",";
		$JSONstring .= "\"taille\": \"". $monJoueur[8]."\",";
		$JSONstring .= "\"poids\": \"". $monJoueur[9]."\",";
		$JSONstring .= "\"sexe\": \"". $monJoueur[10]."\",";
		$JSONstring .= "\"anneeNaissance\": \"". $monJoueur[11]."\",";
		$JSONstring .= "\"villeOrigine\": \"". $monJoueur[12]."\",";
//	$JSONstring .= "\"equipeId\": \"". $monMatch[3]."\",";
//	$JSONstring .= "\"ligueId\": \"". $monMatch[6]."\",";
//	$JSONstring = "{\"ligueNom\": \"". $ligue[1]."\",";
//	$JSONstring .= "\"equipeNom\": \"". $equipeVis[1]."\",";
	$JSONstring .= "\"ficIdJoueur\": \"". $monJoueur[13]."\",";
	$JSONstring .= "\"ficIdPortrait\": \"". $monJoueur[14]."\",";
	$JSONstring .= "\"proprio\": \"". $monJoueur[15]."\"}";

	
echo $JSONstring;
	
mysqli_close($conn);
?>
