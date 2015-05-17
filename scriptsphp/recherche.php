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

$search = $_POST['searchString'];

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

$ligue = array();
	$rLigue = mysql_query("SELECT  Ligue.*
								FROM Ligue
								WHERE Nom_Ligue LIKE '%".$search."%'")
or die(mysql_error()); 
$IL=0;
	while($rangLigue=mysql_fetch_assoc($rLigue)){
		$ligue[$IL]['id']=$rangLigue['ID_Ligue'];
		$ligue[$IL]['nom']=$rangLigue['Nom_Ligue'];
		$IL++;
	}
	
$joueur = array();
	$rJoueur = mysql_query("SELECT  TableJoueur.*
								FROM TableJoueur
								WHERE NomJoueur LIKE '%".$search."%'")
or die(mysql_error()); 
$IJ=0;
	while($rangLigue=mysql_fetch_assoc($rJoueur)){
		$joueur[$IJ]['id']=$rangLigue['joueur_id'];
		$joueur[$IJ]['nom']=$rangLigue['NomJoueur'];
		$IJ++;
	}
	/*
$match = array();
	$rMatch = mysql_query("SELECT  TableMatch.*
								FROM TableMatch
								WHERE date LIKE '%".$search."%'")
or die(mysql_error()); 
$IM=0;
	while($rangLigue=mysql_fetch_assoc($rMatch)){
		$match[$IM]['id']=$rangLigue['match_id'];
		$match[$IM]['nom']=$rangLigue['matchIdRef'];
		$IM++;
	}*/
	
	
	
	
	

$resultat =array();
$resultat['ligue']=$ligue;
$resultat['joueur']=$joueur;
//$resultat['match']=$match;
	
echo json_encode($resultat);
	

?>
