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
$type = $_POST['typeRecherche'];

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
	
	
	if(!strcmp($type, $match))
$match = array();
	$datedeb = $search." 00:00:00.000";
	$datefin = $search." 23:59:59.999";
		$rMatch = mysql_query("SELECT  TableMatch.*, Ligue.Nom_Ligue, TEdom.nom_equipe As eqDom, TEvis.nom_equipe As eqVis
								FROM TableMatch
								JOIN Ligue
									ON (TableMatch.ligueRef=Ligue.ID_Ligue)
								JOIN TableEquipe TEdom
									ON (TEdom.equipe_id=TableMatch.eq_dom)
								JOIN TableEquipe TEvis
									ON (TEvis.equipe_id=TableMatch.eq_vis)
								WHERE date >='{$datedeb}'
								AND date <='{$datefin}'")
						

								
or die(mysql_error()); 
$IM=0;
	while($rangMatch=mysql_fetch_assoc($rMatch)){
		$match[$IM]['date']=$rangMatch['date'];
		$match[$IM]['nom']=$rangMatch['Nom_Ligue'];
		$match[$IM]['matchId']=$rangMatch['matchIdRef'];
		$match[$IM]['eqDom']=$rangMatch['eqDom'];
		$match[$IM]['eqVis']=$rangMatch['eqVis'];
				$IM++;
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
$resultat['match']=$match;
//$resultat['match']=$match;
	
echo json_encode($resultat);
	

?>
