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

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
$mavId = $_POST['arenaId'];
$ligueId = $_POST['ligueId'];

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
	
//$jDom = json_decode($jDomJSON, true);
//$jVis = json_decode($jVisJSON, true);
$strRetour.=$mavId;
if($ligueId!=0&&$ligueId!=undefined)
{
$retour = mysql_query("SELECT TableArena.*	
						FROM TableArena
						LEFT JOIN abonLigueArena
							ON (TableArena.arenaId=abonLigueArena.arenaId)
						 WHERE abonLigueArena.ligueId='{$ligueId}'
						 	")or die(mysql_error());
}
else {$retour = mysql_query("SELECT TableArena.*	
						FROM TableArena
						 	")or die(mysql_error());
	
}
$strRetour.= mysql_num_rows($retour);

$vecMatch = array();
while($r = mysql_fetch_assoc($retour)) {
    $vecMatch[] = $r;
}
$adomper= json_encode($vecMatch);
$adomper= str_replace('"[','[',$adomper);
$adomper= str_replace(']"',']',$adomper);
echo utf8_encode($adomper);


	//		header("HTTP/1.1 200 OK");
?>
<?php  ?>
