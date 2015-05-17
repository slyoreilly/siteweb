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
$ligueId = $_POST["ligueId"];

$reqMes = "SELECT * 
			FROM TableMessage
			JOIN TableUser
				ON (TableMessage.expediteur=TableUser.noCompte)
			
			WHERE recepteur={$ligueId}";
$rMes = mysql_query($reqMes)
or die(mysql_error());  

$IM=0;
$cv = Array();
$message=Array();
while ($rangMes = mysql_fetch_array($rMes))
{
	$cv =(array) json_decode($rangMes['cleValeur']);
	if(is_array($cv))
	{
		if(strcmp($cv['contexteRecepteur'],"ligue")==0&&strcmp($cv['medium'],"babillard")==0) 
	{$message[$IM]=array();
	$message[$IM]['corps']=$rangMes['corps'];
	$message[$IM]['titre']=$rangMes['titre'];
	$message[$IM]['messageId']=$rangMes['messageId'];
	$message[$IM]['parent']=$cv['messageParent'];
	$message[$IM]['expediteur']=$rangMes['username'];
	$message[$IM]['dateEmission']=$rangMes['dateEmission'];
	$message[$IM]['dateSuppression']=$rangMes['dateSuppression'];
			$IM++;
			}
	}
}


echo json_encode($message);
	


?>
