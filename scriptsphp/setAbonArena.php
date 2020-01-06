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

$dateDeb = $_POST['dateDeb'];
$dateFin = $_POST['dateFin'];
$ligueId = $_POST['ligueId'];
$arenaId = $_POST['arenaId'];
$abonId = $_POST['abonId'];

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	if($dateDeb=='')
	{$dateDeb='2011-01-01';}
	if($dateFin=='')
	{$dateFin='2050-01-01';}

if($abonId!='undefined'&&$abonId!=0)
{
$retour = mysql_query("SELECT * 
						FROM abonLigueArena 
						WHERE abonLiArId='{$abonId}'")or die(mysql_error());	

if(mysql_num_rows($retour)>0)
{
	$retour = mysql_query("UPDATE MatchAVenir SET arenaId={$arenaId},ligueId={$ligueId} ,debutAbon='{$dateDeb}',finAbon='{$dateFin}',ligueId='{$ligueId}' WHERE abonLiArId='{$abonId}'")or die(mysql_error()." UPDATE ");	
$retour=$abonId;
}
}


else {
	$retour = mysql_query("INSERT INTO abonLigueArena (debutAbon, finAbon, ligueId, arenaId, permission) 
VALUES ('{$dateDeb}','{$dateFin}','{$ligueId}','{$arenaId}',30)")or die(mysql_error()." INSERT INTO");

$ret = mysql_query("SELECT *
						FROM abonLigueArena 
						WHERE 1 
						ORDER BY abonLiArId DESC")or die(mysql_error());	
$tmp= mysql_fetch_row($ret);
$retour=$tmp[0];	
}
echo $retour;
?>
<?php  ?>
