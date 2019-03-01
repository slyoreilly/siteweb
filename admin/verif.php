
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

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, "utf8");




$id = $_POST['id'];
$password = $_POST['password'];

$resultEquipe = mysqli_query($conn,"SELECT * FROM TableUser WHERE username='{$id}' AND password='{$password}'")
or die(mysqli_error($conn));  
$boule=0;
while($rangeeEquipe=mysqli_fetch_array($resultEquipe))
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

mysqli_close($conn);

?> 

