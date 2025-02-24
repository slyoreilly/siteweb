<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$username = $_POST['username'];
$vielledate = $_POST['vielledate'];

	
	
	// Retrieve all the data from the "example" table
$resultUser = mysqli_query($conn,"SELECT * FROM TableUser")
or die(mysqli_error($conn));  
while($rangeeUser=mysqli_fetch_array($resultUser))
{
		if(!strcmp($rangeeUser['username'],$username))
	{$userSelect =$rangeeUser['noCompte'];
	}
		// Prend le ID du user pour trouver les ligues abonn�es.
}

$resultAbon = mysqli_query($conn,"SELECT * FROM AbonnementLigue ORDER BY ligueid")
or die(mysqli_error($conn));  

$AbonSelect = array();
while($rangeeAbon=mysqli_fetch_array($resultAbon))
	{
		if($rangeeAbon['userid']==$userSelect)
			array_push($AbonSelect, $rangeeAbon['ligueid']);
	}
	
	$qAbonArb="SELECT * FROM abonArbitreLigue 
								JOIN TableArbitre
									ON (TableArbitre.arbitreId=abonArbitreLigue.arbitreId)
								WHERE TableArbitre.userId='{$userSelect}'
								ORDER BY ligueId";
$resultAbonArb = mysqli_query($conn, $qAbonArb)
or die(mysqli_error($conn).$qAbonArb);  

while($rangeeAbonArb=mysqli_fetch_array($resultAbonArb))
	{
		if(!in_array($rangeeAbonArb['ligueId'],$AbonSelect))
			{array_push($AbonSelect, $rangeeAbonArb['ligueId']);}
	}
	
	// On obtient un array de ligueID auquel userSelect est abonn�.
	

$ligueSelect = array();
$equipeSelect = array();
$joueurSelect = array();

$noLigue =0;
$Iligue = 0;
$Iequipe = 0;
/*
	
 */header("HTTP/1.1 200 OK");
//echo " ".count($AbonSelect);
	while($Iligue<count($AbonSelect))
	{
	$resultLigue = mysqli_query($conn,"SELECT * FROM {$tableLigue} ORDER BY ID_Ligue")
	or die(mysqli_error($conn));  
		
		

		$ligue= Array();

		while($rangeeLigue=mysqli_fetch_array($resultLigue))
			{if($rangeeLigue['ID_Ligue']==$AbonSelect[$Iligue])
				{
					
					$resultArb= mysqli_query($conn,"SELECT * FROM abonArbitreLigue WHERE ligueId={$rangeeLigue['ID_Ligue']}")
					or die(mysqli_error($conn));  
					$Iarb=0;
					$arbitres=Array();
					$arbitres['arbitreId']=Array();
					while($rangArb=mysqli_fetch_array($resultArb))
						{$arbitres['arbitreId'][$Iarb]=$rangArb['arbitreId'];
						$Iarb++;}
						
					$jMerge = json_encode(array_merge((array) $arbitres, (array) json_decode($rangeeLigue['cleValeur'])));
					
					$ligue[$Iligue]['nomLigue']=$rangeeLigue['Nom_Ligue'];
					$ligue[$Iligue]['ligueId']=$rangeeLigue['ID_Ligue'];
					$ligue[$Iligue]['lieu']=$rangeeLigue['Lieu'];
					$ligue[$Iligue]['horaire']=$rangeeLigue['Horaire'];
					$ligue[$Iligue]['dernierMAJ']=$rangeeLigue['dernierMAJ'];
					$ligue[$Iligue]['cleValeur']=$jMerge;
						

				$Iligue++;
				}//Fin d'une ligue valide
			}//Fin des scans de ligues.

			
			
		$noLigue++;
	}//Fin du scan des ligues auquel l'utilisateur est abbonn�.
	
$JSONstring = 	"{\"ligue\": ".json_encode($ligue);
	$JSONstring .= ", \"heure\":\"".time()."\"}";
	
//echo json_encode($Sommaire);
echo $JSONstring;
	

	
  
 //echo "Bidon "	
	
	
	
//$json=json_encode($ligueSelect);
//echo "[".$ligueSelect[0]."]";
//return $JSONobjet;

////////////////////  Reste � faire le mapping des ID de ligue vers des noms de ligues.	

mysqli_close($conn);


?>
