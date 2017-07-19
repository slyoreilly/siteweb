<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$nom = $_POST['nom'];
$horaire = $_POST['horaire'];
$lieu = $_POST['lieu'];
$userId = $_POST['userId'];
$code = $_POST['code'];
$ligueId = $_POST['ligueid'];
$ficId = $_POST['ficId'];




function trouveIDParNomUser($nomLi)
{
$resultUser = mysql_query("SELECT * FROM TableUser")
or die(mysql_error());  
while($rangeeUser=mysql_fetch_array($resultUser))
{
		if(!strcmp($rangeeUser['username'],$nomLi))
	{$UserID =$rangeeUser['noCompte'];// Ce sont de INT
	}
}
return $UserID;
}


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
	
	
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////


//////////////////////////////////
//
//	Les queries
//



	if(1==$code)  // Code 1:  Cr�er une nouvelle ligue.
{
	
	if(!is_numeric($userId))
		{$userId=trouveIDParNomUser($userId);}
		$accolade = "{}";
		$accolade2=mysql_real_escape_string($accolade);
	$query_ligue = "INSERT INTO Ligue (Nom_Ligue, Horaire, Lieu,ficId, dernierMAJ,cleValeur) ".
"VALUES ('$nom', '$horaire', '$lieu','$ficId', NOW(),'$accolade2')";
		
$retour = mysql_query($query_ligue)or die('Error, query Ligue failed'.$query_ligue.": ".mysql_error());
$resultEvent = mysql_query("SELECT * FROM Ligue WHERE Nom_Ligue = '{$nom}' ORDER BY ID_Ligue DESC")or die (mysql_error());
	
	
	
while($rang=mysql_fetch_array($resultEvent))
{$ligueId= $rang['ID_Ligue'];}

$query_saison = "INSERT INTO TableSaison (typeSaison, saisonActive, premierMatch, dernierMatch, ligueRef	 ) ".
"VALUES (1, 1, NOW(), '2020-01-01','{$ligueId}')";
	mysql_query($query_saison)	
or die('Error, query saison failed: '.mysql_error());

	$query_abon = "INSERT INTO AbonnementLigue (userid, type, ligueid,contexte) ".
"VALUES ('$userId', '10', '$ligueId','ligue')";
$retour = mysql_query($query_abon)	
or die('Error, query abon failed: '.mysql_error());

echo $ligueId;

//	mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono, match_event_id) 
//VALUES ( 'test	Match2', 'testMatch2', 'testMatch2', 'testMatch2','testMatch2')");	
}
	
	if(10==$code)  // Code 10:  Modifie ligue existante.
	{
		$resultFic = mysql_query("SELECT ficId FROM Ligue WHERE ID_Ligue= '$ligueId'")or die (mysql_error());
		
		
		
	$query_update = "UPDATE Ligue SET Nom_Ligue='$nom', Horaire='$horaire',ficId='$ficId' , Lieu='$lieu' WHERE ID_Ligue= '$ligueId'";	
	mysql_query($query_update)or die (mysql_error());	
	
	
mysql_query($queryFic) or die('Error, query failed');
//include 'library/closedb.php';
	
	}

?>


