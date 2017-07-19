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

$videoId= $_POST['videoId'];
$eval= $_POST['evalBut'];

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

//////////////////////////////////
//
//	Les queries
//


$qVielleSaison="SELECT eval,nbEval FROM Video WHERE videoId='{$videoId}' limit 0,1";
$resVS=mysql_query($qVielleSaison) or die(mysql_error().'Error, query failed'.$qVielleSaison);
		while ($rVS = mysql_fetch_array($resVS)) {
			$evalAMettre = ($rVS[0]*$rVS[1]+$eval)/($rVS[1]+1);
			$nbEvalAMettre = $rVS[1]+1;
			
	$qSUp = "UPDATE Video 
							SET nbEval='{$nbEvalAMettre}',eval='{$evalAMettre}'
							WHERE videoId='{$videoId}' ";
		mysql_query($qSUp) or die(mysql_error().' Error, query failed'.$qSUp);

		}
		
	
?>
