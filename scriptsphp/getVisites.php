<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';

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
$joueurId = $_POST["joueurId"];

if(strcmp($joueurId,""))
{$reqChrono = "SELECT * 
			FROM Video
			WHERE tagPrincipal={$joueurId} ORDER BY chrono DESC";}
if(strcmp($ligueId,""))
{$reqChrono = "SELECT * 
			FROM Visites
			WHERE ligueId={$ligueId} ORDER BY date DESC";}

$rChrono = mysql_query($reqChrono)
or die(mysql_error());  

$IM=0;
$lesVisites = Array();
while ($rangChrono = mysql_fetch_array($rChrono))
{
		if($rangChrono['angleOk']>=0){
	
	$maLigne = Array();
	
	$maLigne['userId']=$rangChrono['userId'];
	$maLigne['fakeId']=$rangChrono['fakeId'];
	$maLigne['date']=$rangChrono['date'];	
	
	array_push($lesVisites,$maLigne);
		}
}

$retour=array();


echo json_encode($lesVisites);
	


?>
