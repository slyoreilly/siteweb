		
<?php

/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////


require '../scriptsphp/defenvvar.php';

$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';
$tableUser = 'TableUser';



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
//mysqli_close($conn);
?> 

