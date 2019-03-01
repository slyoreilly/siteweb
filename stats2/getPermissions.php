		
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


function trouveIDParNomUser($user)
{
	 $noUser = -1;
$fResultUser = mysqli_query($conn,"SELECT * FROM TableUser")
or die(mysqli_error($conn));  
while($fRangeeUser=mysqli_fetch_array($fResultUser))
{
		if(!strcmp($fRangeeUser['username'],$user))
	{$noUser =$fRangeeUser['noCompte'];// Ce sont de INT
	}
}
if (mysqli_num_rows($fResultUser)>0)
{
return $noUser;
}
else{return -1;}

}


$ligueId = $_GET['ligueId'];
$inter=$_GET['userId'];
$userId = trouveIDParNomUser($inter);



$result = mysqli_query($conn,"SELECT type FROM AbonnementLigue WHERE ligueid='{$ligueId}' AND userid='$userId'")
or die(mysqli_error($conn));  


if (mysqli_num_rows($result)>0)
{
echo mysqli_result($result,0,0);	
}
else{echo "1000000";}
mysqli_close($conn);
?> 

