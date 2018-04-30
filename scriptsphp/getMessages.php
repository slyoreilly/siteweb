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


function trouveIDParNomUser($nomUser)
{
$fResultUser = mysql_query("SELECT noCompte 
								FROM TableUser 
								WHERE username='{$nomUser}'")
or die(mysql_error());  
$rU = mysql_fetch_row($fResultUser);
if (mysql_num_rows($fResultUser)>0)
{
return $rU[0];
}
else{return -1;}

}


//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////
	
//$receveur = $_POST["receveur"];
$nomUser = $_POST["userId"];
$userId=trouveIDParNomUser($nomUser);

$reqMes = "SELECT * 
			FROM ReceptionMessage
			JOIN TableMessage
				ON (ReceptionMessage.messageId = TableMessage.messageId)
			JOIN TableUser
				ON (TableMessage.recepteur=TableUser.noCompte)
			WHERE receveur={$userId}";
$rMes = mysql_query($reqMes)
or die(mysql_error());  

$IM=0;
$messages = Array();
while ($rangMes = mysql_fetch_array($rMes))
{
	$message[$IM]=array();
	$message[$IM]['corps']=$rangMes['corps'];
	$message[$IM]['titre']=$rangMes['titre'];
	$message[$IM]['expediteur']=$rangMes['username'];
	$message[$IM]['dateEmission']=$rangMes['dateEmission'];
	$message[$IM]['dateSuppression']=$rangMes['dateSuppression'];
			$IM++;
}


echo json_encode($message);
	


?>
