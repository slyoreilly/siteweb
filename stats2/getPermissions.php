		
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

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

$ligueId = $_GET['ligueId'];
$inter=$_GET['userId'];


$result = mysqli_query($conn,"SELECT AbonnementLigue.type FROM AbonnementLigue 
	JOIN TableUser
		ON (TableUser.noCompte = AbonnementLigue.userid)
	WHERE ligueid='$ligueId' AND username='$inter'")
or die(mysqli_error($conn));  

if (mysqli_num_rows($result)>0)
{
$type= mysqli_data_seek($result,0);	
echo $type;
}
else{echo "1000000";}
mysqli_close($conn);
?> 

