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
$matchjson = stripslashes($_POST['matchjson']);

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	
	
//$json=json_decode("'".$matchjson."'");
$leMatch = json_decode($matchjson, true);
	$intEquipe = $leMatch['equipe_id'];
	$intJoueur = $leMatch['joueur_id'];
	$intEvent = $leMatch['event'];

	
$retour = mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono,souscode, match_event_id) 
VALUES ('{$intJoueur}', '{$intEquipe}', '{$intEvent}', '{$leMatch['chrono']}','{$leMatch['souscode']}','{$leMatch['match_id']}')");	
//	mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono, match_event_id) 
//VALUES ( 'test	Match2', 'testMatch2', 'testMatch2', 'testMatch2','testMatch2')");	
	
		echo "Event dernier match:" .$retour;
			header("HTTP/1.1 200 OK");

?>
<?php  ?>
