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
$jDom = $_POST['jDom'];
$jVis = $_POST['jVis'];
$gDom = $_POST['gDom'];
$gVis = $_POST['gVis'];
$eqDom = $_POST['eqDom'];
$eqVis = $_POST['eqVis'];
$dateDeb = $_POST['dateDeb'];
$dateFin = $_POST['dateFin'];
$ligueId = $_POST['ligueId'];
$mavId = $_POST['mavId'];
$arenaId = $_POST['arenaId'];
$arbitreId = $_POST['arbitreId'];
$matchId= substr($dateDeb,0,4)."/".substr($dateDeb,5,2)."/".substr($dateDeb,8,2)."_".$eqDom."_".$eqVis;


if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	
	$strEqDom="";
	$strEqVis="";
	$strGDom="";
	$strGVis="";
	$strJDom="";
	$strJVis="";
	$strArb=0;
	
//	echo "gDom: ".$gDom."   ";	
		
	if($eqDom!='undefined')
	{$strEqDom = "eqDom='{$eqDom}', ";}
	else{$strEqDom="";
	$eqDom=0;}
	if($eqVis!='undefined')
	{$strEqVis = "eqVis='{$eqVis}', ";}
	else{$strEqVis="";
	$eqVis=0;}
	if($gDom!='undefined')
	{$strGDom="gardienDom='{$gDom}', ";}
	else{$strGDom="";
	$gDom=0;}
	if($gVis!='undefined')
	{$strGVis="gardienVis='{$gVis}', ";}
	else{$strGVis="";
	$gVis=0;}
	if($jDom!='undefined')
	{$strJDom="alignementDom='{$jDom}', ";}
	if($jVis!='undefined')
	{$strJVis="alignementVis='{$jVis}', ";}
	if($arbitreId!='undefined')
	{$strArb="arbitreId='{$arbitreId}', ";}
	else {
		$arbitreId=0;
	}
		if($dateDeb=='AAAA/MM/JJ 23:59')
	{$dateDeb="2000/01/01 00:00";}
		
	
//$jDom = json_decode($jDomJSON, true);
//$jVis = json_decode($jVisJSON, true);

//mysql_query("SET time_zone='-4:00'")or die(mysql_error());

$retour = mysql_query("SELECT * 
						FROM MatchAVenir 
						WHERE mavId='{$mavId}'")or die(mysql_error());	

if(mysql_num_rows($retour)>0)
{
	$retour = mysql_query("UPDATE MatchAVenir SET matchId='{$matchId}',arenaId={$arenaId}, ".$strEqDom.$strEqVis.$strGDom.$strGVis.$strJDom.$strJVis.$strArb."
	date='{$dateDeb}',dateFin='{$dateFin}',ligueId='{$ligueId}', dernierMAJ=NOW() WHERE mavId='{$mavId}'")or die(mysql_error()." UPDATE MatchAVenir");	
	$retour= $mavId;
}



else {
	$retour = mysql_query("INSERT INTO MatchAVenir (matchId, alignementDom, alignementVis, gardienDom, gardienVis, eqDom, eqVis, date, dateFin, ligueId,dernierMAJ,arenaId,arbitreId) 
VALUES ('{$matchId}','{$jDom}', '{$jVis}','{$gDom}','{$gVis}','{$eqDom}','{$eqVis}','{$dateDeb}','{$dateFin}','{$ligueId}',NOW(),'{$arenaId}','{$arbitreId}')")or die(mysql_error()." INSERT INTO MatchAVenir");

$ret = mysql_query("SELECT mavId 
						FROM MatchAVenir 
						WHERE 1 
						ORDER BY mavId DESC")or die(mysql_error());	
$tmp= mysql_fetch_row($ret);
$retour=$tmp[0];	
}
echo $retour;
?>
<?php  ?>
