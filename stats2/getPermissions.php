		
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
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';
$tableUser = 'TableUser';

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


function trouveIDParNomUser($ligue)
{
$fResultUser = mysql_query("SELECT * FROM TableUser")
or die(mysql_error());  
while($fRangeeUser=mysql_fetch_array($fResultUser))
{
		if(!strcmp($fRangeeUser['username'],$ligue))
	{$equipeID =$fRangeeUser['noCompte'];// Ce sont de INT
	}
}
if (mysql_num_rows($fResultUser)>0)
{
return $equipeID;
}
else{return -1;}

}


$ligueId = $_GET['ligueId'];
$inter=$_GET['userId'];
$userId = trouveIDParNomUser($inter);



$result = mysql_query("SELECT type FROM AbonnementLigue WHERE ligueid='{$ligueId}' AND userid='$userId'")
or die(mysql_error());  


if (mysql_num_rows($result)>0)
{
echo mysql_result($result,0);	
}
else{echo 1000000;}

?> 

