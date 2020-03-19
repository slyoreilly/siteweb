<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$username = $_POST['username'];
$password = $_POST['password'];




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
	
	
	
$matchjson = stripslashes(mysql_real_escape_string(stripslashes($_POST['matchjson'])));
$ligueId = $_POST['ligueId'];
//$json=json_decode("'".$matchjson."'");
$leMatch = json_decode($matchjson, true);

	$intEquipe = $leMatch['equipe_id'];
	$intJoueur = $leMatch['joueur_id'];
	$intEvent = $leMatch['event'];

	if($leMatch['etatSync']==12||$leMatch['etatSync']==10)
{	
$retInsEvent = mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono,souscode, match_event_id) 
VALUES ('{$intJoueur}', '{$intEquipe}', '{$intEvent}', '{$leMatch['chrono']}','{$leMatch['souscode']}','{$leMatch['match_id']}')")or die(mysql_error()." INSERT INTO".$leMatch['db_id']);	
//	mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono, match_event_id) 
//VALUES ( 'test	Match2', 'testMatch2', 'testMatch2', 'testMatch2','testMatch2')");	
}

if($leMatch['event']==10&&$leMatch['souscode']==10)
{
	$deSyncMatch=1;
	//$dumBuf = include('../stats2/listeMatchs2JSON.php');
	$qMatch = "SELECT cleValeur FROM TableMatch 
							WHERE matchIdRef = '{$leMatch['match_id']}'
							";
	
$testmatch = mysql_query($qMatch)or die(mysql_error()." Select ".$leMatch['db_id']);	
	$rMatch = mysql_fetch_row($testmatch);
	
	if(($rMatch[0]!=NULL)&&(strlen($rMatch[0])>2))
	{
	$condMatch=$rMatch[0];		
//	$jMatch = json_decode(stripslashes($rMatch[0]));
	//	$jMatch = json_decode($condMatch);
	$jMerge = json_encode(array_merge((array) json_decode($condMatch),(array) json_decode($leMatch['cleValeur'])));  

	}
	else {
		//	$condMatch="Apoil2";		
		
		$jMerge = $leMatch['cleValeur'];

	}
	
	mysql_query("UPDATE TableMatch SET cleValeur='{$jMerge}' WHERE matchIdRef = '{$leMatch['match_id']}'");
	
}
	
	//	if($retInsEvent)
	//		{
				//echo json_last_error();
				echo $leMatch['db_id'];//.$matchjson.json_encode($leMatch);
		/*/	echo "qMatch".$qMatch;
			echo "rmatch".$rMatch[0];
			echo "condMatch".$condMatch;
			echo "jMatch".json_encode($jMatch);
			echo "leMatch".json_encode($leMatch['cleValeur']);
			echo "jMerge".$jMerge;*/
						//		}
	//	else {
	//		echo $matchjson;
	//	}
		
		
			header("HTTP/1.1 200 OK");

?>
<?php  ?>
