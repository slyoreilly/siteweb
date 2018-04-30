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

$pm = $_POST['premierMatch'];
$dm = $_POST['dernierMatch'];
$equipeId = $_POST['equipeId'];
$ligueId = $_POST['ligueId'];
$code = $_POST['code'];




if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}


//////////////////////////////////
//
//	Les queries
//



	if(1==$code)  // Code 1:  Cr�er une nouvelle equipe.
{
	$query_equipe = "INSERT INTO abonEquipeLigue (equipeId, ligueId, permission, debutAbon, finAbon) ".
"VALUES ($equipeId, $ligueId, 30, '$pm','$dm' )";
		
$retour = mysql_query($query_equipe)
or die('Error, query failed');

}

	if(2==$code)  // Code 1:  desabonne une equipe.
{
	$query_equipe = "UPDATE abonEquipeLigue 
						SET finAbon= DATE_SUB('$dm',INTERVAL 1 DAY)
						WHERE equipeId=$equipeId AND ligueId=$ligueId
						ORDER BY finAbon DESC
						LIMIT 1 ";
		
$retour = mysql_query($query_equipe)
or die('Error, query failed'.$query_equipe);

}

	
	if(10==$code)  // Code 10:  Modifie ligue existante.
	{
	
	}
	echo $retour;
?>
