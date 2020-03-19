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

$joueurId = $_GET['joueurId'];

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



///////////////////////////////////////////////////////////
//
//	D�but du corps
//
///////////////////////////////////////////////////////////


	$strSelect = "joueur_id,NomJoueur,NumeroJoueur,position,equipe_id_ref,Ligue,nom,prenom,taille,poids,sexe,anneeNaissance,villeOrigine,ficIdJoueur,ficIdPortrait,proprio";
$rJoueur = mysql_query("SELECT $strSelect FROM TableJoueur WHERE  joueur_id = '$joueurId'")
or die(mysql_error());  
$monJoueur=mysql_fetch_row($rJoueur);



/////

	$rEquipeVis = mysql_query("SELECT * FROM {$tableEquipe} where equipe_id='{$eqVis}'")
or die(mysql_error()); 
	$equipeVis=mysql_fetch_row($rEquipeVis);

	$ligueId = $equipeDom[3];
	$rLigue = mysql_query("SELECT * FROM Ligue WHERE ID_Ligue= '{$ligueId}'")
or die(mysql_error());  
$ligue = mysql_fetch_row($rLigue);

	//////////////////////////////////////////////////
	//
	// 	�crit JSON
	
	$JSONstring = "{\"joueurId\": \"". $monJoueur[0]."\",";
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
	

?>
