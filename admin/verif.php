
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


$id = $_POST['id'];
$password = $_POST['password'];

$resultEquipe = mysql_query("SELECT * FROM TableUser WHERE username='{$id}' AND password='{$password}'")
or die(mysql_error());  
$boule=0;
while($rangeeEquipe=mysql_fetch_array($resultEquipe))
{
	if(strcmp($rangeeEquipe['password'], $password)==0)
	{
			$JSONstring = "{\"idCheck\": \"true\",";
			$JSONstring .= "\"pswdCheck\": \"true\",";
			$JSONstring .= "\"userId\": \"".$rangeeEquipe['noCompte']."\",";
			$JSONstring .="\"id\": \"".$id."\"}";
echo $JSONstring;
		
	}	
	else {
			$JSONstring = "{\"idCheck\": \"true\",";
			$JSONstring .= "\"pswdCheck\": \"false\",";
			$JSONstring .= "\"userId\": \"".$rangeeEquipe['noCompte']."\",";
	$JSONstring .="\"id\": \"".$id."\"}";
echo $JSONstring;


	}
	$boule=1;
	
}

if ($boule==0)
{
			$JSONstring = "{\"idCheck\": \"false\",";
			$JSONstring .= "\"pswdCheck\": \"false\",";
			$JSONstring .= "\"userId\": \"0\",";
	$JSONstring .="\"id\": \"".$id."\"}";
echo $JSONstring;
	}

?> 

