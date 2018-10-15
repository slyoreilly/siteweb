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
$typeProprio = $_POST["typeProprio"];
$proprio = $_POST["proprio"];

$reqGal = "SELECT * 
			FROM Galerie
			WHERE proprio={$proprio} 
				AND typeProprio='{$typeProprio}'";
$rGal = mysql_query($reqGal)
or die(mysql_error());  

$IM=0;
$image = Array();
while ($rangIm = mysql_fetch_array($rGal))
{
	$image[$IM]=array();
	$image[$IM]['ficId']=$rangIm['ficId'];
	$image[$IM]['titre']=$rangIm['titre'];
	$image[$IM]['description']=$rangIm['description'];
			$IM++;
}


echo json_encode($image);
	


?>
