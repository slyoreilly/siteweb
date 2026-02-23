
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

//mysqli_close($conn);

?> 

