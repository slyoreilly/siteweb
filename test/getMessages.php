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
	
$receveur = $_POST["receveur"];

echo "IN!";
$reqMes = "SELECT * 
			FROM ReceptionMessage
			JOIN ExpeditionMessage
				ON (ReceptionMessage.messageId = ExpeditionMessage.messageId)
			WHERE receveur={$receveur}";
$rMes = mysql_query($reqMes)
or die(mysql_error());  

$IM=0;
$messages = Array();
while ($rangMes = mysql_fetch_array($rMes))
{
	$message[$IM]="message";
	$message[$IM]['corps']=$rangMes['corps'];
	$message[$IM]['expediteur']=$rangMes['expediteur'];
	$IM++;
}


echo json_encode($message);
	


?>
