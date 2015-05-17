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
$mdp = $_POST['mdp'];
$courriel = $_POST['courriel'];



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
	

$erreur=false;
$qVerif ="SELECT * from TableUser WHERE username='{$nom}'";
$retVerif= 	 mysql_query($qVerif)or die(mysql_error().$qVerif);
if(mysql_num_rows($retVerif)>0)
	{$erreur=true;}
	
if(!$erreur)
{
	$qIns = "INSERT INTO TableUser (username, password, type, sexe,courriel) 
VALUES ('{$nom}', '{$mdp}', 10, 'M','{$$courriel}')";
$retIns= 	 mysql_query($qIns)or die(mysql_error().$qIns);
	
}	
	
			if($retIns)
				echo "1";//.$matchjson.json_encode($leMatch);
			else {
				echo "0";
			}
		
			header("HTTP/1.1 200 OK");

?>
<?php  ?>
