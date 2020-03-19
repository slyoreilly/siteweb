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
$refLigue = $_POST["refLigue"];

$reqGal = "SELECT * 
			FROM TableFichier
			WHERE idRef='{$refLigue}' 
				AND contexte=
				'document'
				";
$rGal = mysql_query($reqGal)
or die(mysql_error());  

$IM=0;
$image = Array();
while ($rangIm = mysql_fetch_assoc($rGal))
{
	$image[$IM]=array();
	$image[$IM]['ficId']=$rangIm['ficId'];
	$image[$IM]['nom']=$rangIm['name'];
	$image[$IM]['type']=$rangIm['type'];
			$IM++;
}

echo json_encode($image);
	


?>
