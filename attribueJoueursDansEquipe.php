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

$username = $_POST['username'];
$password = $_POST['password'];
$joueursmaj = stripslashes($_POST['joueursmaj']);

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	
	
//$json=json_decode("'".$matchjson."'");
$lesJoueurs = json_decode($joueursmaj, true);

$Ij=0;
while($Ij<count($lesJoueurs))
{
$retour = mysql_query("UPDATE {$tableJoueur} SET equipe_id_ref='{$lesJoueurs[$Ij]['equipeId']}',dernierMAJ=NOW() WHERE joueur_id='{$lesJoueurs[$Ij]['joueurId']}'");	
$Ij++;
}
echo "Message: ".$joueursmaj;
	//		header("HTTP/1.1 200 OK");

?>
<?php  ?>
