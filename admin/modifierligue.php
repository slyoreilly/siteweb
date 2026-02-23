<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$nom = $_POST['nom'];
$horaire = $_POST['horaire'];
$lieu = $_POST['lieu'];
$userId = $_POST['userId'];
$code = $_POST['code'];
$ligueId = $_POST['ligueid'];
$ficId = $_POST['ficId'];




function trouveIDParNomUser($nomLi,$conn)
{
$resultUser = mysqli_query($conn,"SELECT * FROM TableUser")
or die(mysqli_error($conn));  
while($rangeeUser=mysqli_fetch_array($resultUser))
{
		if(!strcmp($rangeeUser['username'],$nomLi))
	{$UserID =$rangeeUser['noCompte'];// Ce sont de INT
	}
}
return $UserID;
}

	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////


//////////////////////////////////
//
//	Les queries
//



	if(1==$code)  // Code 1:  Cr�er une nouvelle ligue.
{
	
	if(!is_numeric($userId))
		{$userId=trouveIDParNomUser($userId,$conn);}
		$accolade = "{}";
		$accolade2=mysqli_real_escape_string($conn,$accolade);
	$query_ligue = "INSERT INTO Ligue (Nom_Ligue, Horaire, Lieu,ficId, dernierMAJ,cleValeur) ".
"VALUES ('$nom', '$horaire', '$lieu','$ficId', NOW(),'$accolade2')";
		
$retour = mysqli_query($conn,$query_ligue)or die('Error, query Ligue failed'.$query_ligue.": ".mysqli_error($conn));
$resultEvent = mysqli_query($conn,"SELECT * FROM Ligue WHERE Nom_Ligue = '{$nom}' ORDER BY ID_Ligue DESC")or die (mysqli_error($conn));
	
	
	
while($rang=mysqli_fetch_array($resultEvent))
{$ligueId= $rang['ID_Ligue'];}

$query_saison = "INSERT INTO TableSaison (typeSaison, saisonActive, premierMatch, dernierMatch, ligueRef	 ) ".
"VALUES (1, 1, NOW(), '2030-01-01','{$ligueId}')";
	mysqli_query($conn,$query_saison)	
or die('Error, query saison failed: '.mysqli_error($conn));

	$query_abon = "INSERT INTO AbonnementLigue (userid, type, ligueid,contexte) ".
"VALUES ('$userId', '10', '$ligueId','ligue')";
$retour = mysqli_query($conn,$query_abon)	
or die('Error, query abon failed: '.mysqli_error($conn));

echo $ligueId;

//	mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono, match_event_id) 
//VALUES ( 'test	Match2', 'testMatch2', 'testMatch2', 'testMatch2','testMatch2')");	
}
	
	if(10==$code)  // Code 10:  Modifie ligue existante.
	{
		//$resultFic = mysqli_query($conn,"SELECT ficId FROM Ligue WHERE ID_Ligue= '$ligueId'")or die (mysqli_error($conn));
		
		
		
	$query_update = "UPDATE Ligue SET Nom_Ligue='$nom', Horaire='$horaire',ficId='$ficId' , Lieu='$lieu' , dernierMAJ = NOW() WHERE ID_Ligue= '$ligueId'";	
	mysqli_query($conn,$query_update)or die (mysqli_error($conn));	
	
	
//mysqli_query($conn,$queryFic) or die('Error, query failed');
//include 'library/closedb.php';
	
	}

//mysqli_close($conn)

?>


