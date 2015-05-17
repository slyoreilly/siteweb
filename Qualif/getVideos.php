<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");



//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////
	
//$receveur = $_POST["receveur"];
$ligueId = $_POST["ligueId"];

$reqChrono = "SELECT * 
			FROM Video
			JOIN TableMatch
				ON (Video.nomMatch = TableMatch.matchIdRef)
			WHERE ligueRef={$ligueId} ORDER BY chrono DESC";
$rChrono = mysql_query($reqChrono)
or die(mysql_error());  

$IM=0;
$recentsVideos = Array();
while ($rangChrono = mysql_fetch_array($rChrono))
{
	$maLigne = Array();
	
	$maLigne['eval']=$rangChrono['eval'];
	$maLigne['nbVues']=$rangChrono['nbVues'];
	$maLigne['chrono']=$rangChrono['chrono'];
	$maLigne['nomMatch']=$rangChrono['nomMatch'];
	$maLigne['nomFichier']=$rangChrono['nomFichier'];
	$maLigne['videoId']=$rangChrono['videoId'];
	
	array_push($recentsVideos,$maLigne);
}

$reqPop = "SELECT * 
			FROM Video
			JOIN TableMatch
				ON (Video.nomMatch = TableMatch.matchIdRef)
			WHERE ligueRef={$ligueId} ORDER BY nbVues DESC";
$rPop = mysql_query($reqPop)
or die(mysql_error());  

$plusVuesVideos = Array();
while ($rangPop = mysql_fetch_array($rPop))
{
	$maLigne = Array();
	
	
	$maLigne['eval']=$rangPop['eval'];
	$maLigne['nbVues']=$rangPop['nbVues'];
	$maLigne['chrono']=$rangPop['chrono'];
	$maLigne['nomMatch']=$rangPop['nomMatch'];
	$maLigne['nomFichier']=$rangPop['nomFichier'];
	$maLigne['videoId']=$rangPop['videoId'];
	
	array_push($plusVuesVideos,$maLigne);
}
$reqTop = "SELECT * 
			FROM Video
			JOIN TableMatch
				ON (Video.nomMatch = TableMatch.matchIdRef)
			WHERE ligueRef={$ligueId} ORDER BY eval DESC";
$rTop = mysql_query($reqTop)
or die(mysql_error());  

$IM=0;
$topVideos = Array();
while ($rangTop = mysql_fetch_array($rTop))
{
	$maLigne = Array();
	
	
	$maLigne['eval']=$rangTop['eval'];
	$maLigne['nbVues']=$rangTop['nbVues'];
	$maLigne['chrono']=$rangTop['chrono'];
	$maLigne['nomMatch']=$rangTop['nomMatch'];
	$maLigne['nomFichier']=$rangTop['nomFichier'];
	$maLigne['videoId']=$rangTop['videoId'];
	
	array_push($topVideos,$maLigne);
}

$retour=array();

$retour['topVideos']=$topVideos;
$retour['recentsVideos']=$recentsVideos;
$retour['popVideos']=$plusVuesVideos;

echo json_encode($retour);
	


?>
