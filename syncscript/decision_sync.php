<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$username = $_POST['username'];



	
	// Retrieve all the data from the "example" table
$resultUser = mysqli_query($conn, "SELECT * FROM TableUser")
or die(mysqli_error($conn));  
while($rangeeUser=mysqli_fetch_array($resultUser))
{
		if(!strcmp($rangeeUser['username'],$username))
	{$userSelect =$rangeeUser['ref_id'];
	}
		// Prend le ID du user pour trouver les ligues abonn�es.
}

$resultAbon = mysqli_query($conn, "SELECT * FROM AbonnementLigue ORDER BY ligueid")
or die(mysqli_error($conn));  

$AbonSelect = array();
while($rangeeAbon=mysqli_fetch_array($resultAbon))
	{
		if($rangeeAbon['userid']==$userSelect)
			array_push($AbonSelect, $rangeeAbon['ligueid']);
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
	$resultLigue = mysqli_query($conn,"SELECT Ligue.ID_Ligue, TableEquipe.equipe_id, TableJoueur.joueur_id 
									FROM {$tableLigue} 
										JOIN {$tableEq},{$tableJoueur} 
											ON dernierMAJ 
												ORDER BY dernierMAJ 
													SORT DESC")
	or die(mysqli_error($conn));  
	//$resultJoueur = mysql_query("SELECT * FROM {$tableJoueur}")
	//or die(mysql_error());  
		
		/////////////////////////////////
		//
		//	Structure JSON:
		//	Ligue[i]
		//		nomLigue
		//		ligueId
		//		equipe[j]
		//			nomEquipe
		//			equipeId
		//			logo
		//			joueur[k]
		//				nomJoueur
		//				joueurId
		//				numeroJoueur
		//
		//////////////////////////////////

	$JSONstring = 	"{\"ligue\": [";
		while($rangeeLigue=mysqli_fetch_array($resultLigue))
			{if($rangeeLigue['ID_Ligue']==$AbonSelect[$Iligue])
				{$JSONstring .=  "{\"nomLigue\": \"".$rangeeLigue['Nom_Ligue']."\", ";
				$JSONstring .=  "\"ligueId\": \"".$rangeeLigue['ID_Ligue']."\", ";
				$JSONstring .=  "\"equipe\": [";
				$c=0;
				$rangeeEquipe=null;
				$resultEquipe = NULL;
				$resultEquipe = mysqli_query($conn, "SELECT * 
												FROM {$tableEq} 
													WHERE ligue_equipe_ref={$rangeeLigue['ID_Ligue']}")
				or die(mysqli_error($conn));  
				while($rangeeEquipe=mysqli_fetch_array($resultEquipe))
					{
//					if($rangeeEquipe['ligue_equipe_ref']==$rangeeLigue['ID_Ligue'])
//						{
						$JSONstring .=  "{\"nomEquipe\": \"".$rangeeEquipe['nom_equipe']."\", ";
						$JSONstring .=  "\"equipeId\": \"".$rangeeEquipe['equipe_id']."\", ";
						$JSONstring .=  "\"logo\": \"".$rangeeEquipe['logo']."\", ";
						$JSONstring .=  "\"joueur\": [";
						$resultJoueur = mysqli_query($conn,"SELECT * 
													FROM {$tableJoueur}
														WHERE equipe_id_ref={$rangeeEquipe['equipe_id']}")
						or die(mysqli_error($conn));  
						$rangeeJoueur=0;
						while($rangeeJoueur=mysqli_fetch_array($resultJoueur))
							{if($rangeeJoueur['equipe_id_ref']==$rangeeEquipe['equipe_id'])
								{$JSONstring .=  "{\"nomJoueur\": \"".$rangeeJoueur['NomJoueur']."\", ";
								$JSONstring .=  "\"joueurId\": \"".$rangeeJoueur['joueur_id']."\", ";
								$JSONstring .=  "\"maj\": \"".$rangeeJoueur['dernierMAJ']."\", ";
								$JSONstring .=  "\"noJoueur\": \"".$rangeeJoueur['NumeroJoueur']."\"},";}
							}//Fin du scan des joueurs
							if(!strcmp(",", substr($JSONstring,-1)))// Pour �viter les vides;
							{$JSONstring = substr($JSONstring, 0,-1);}
						$JSONstring .= "]},"; //fin des joueurs d'une �quipe
//						}// Fin d'une �quipe valide
				
					}// Fin des scans d'�quipes
					
				
				////////////////////////////
				//	Section agents libres
						$JSONstring .=  "{\"nomEquipe\": \"".$rangeeLigue['Nom_Ligue']." - Libres\", ";
						$JSONstring .=  "\"equipeId\": \"". 0 ."\", ";
						$JSONstring .=  "\"logo\": \"". "rien\", ";
						$JSONstring .=  "\"joueur\": [";
						$resultJoueur = mysql_query("SELECT * 
													FROM {$tableJoueur}
														WHERE equipe_id_ref=0
															AND Ligue={$rangeeLigue['ID_Ligue']}")
						or die(mysqli_error($conn));  
						$rangeeJoueur=0;
						while($rangeeJoueur=mysqli_fetch_array($resultJoueur))
								{$JSONstring .=  "{\"nomJoueur\": \"".$rangeeJoueur['NomJoueur']."\", ";
								$JSONstring .=  "\"joueurId\": \"".$rangeeJoueur['joueur_id']."\", ";
								$JSONstring .=  "\"maj\": \"".$rangeeJoueur['dernierMAJ']."\", ";
								$JSONstring .=  "\"noJoueur\": \"".$rangeeJoueur['NumeroJoueur']."\"},";}//Fin du scan des joueurs
							if(!strcmp(",", substr($JSONstring,-1)))// Pour �viter les vides;
							{$JSONstring = substr($JSONstring, 0,-1);}
						$JSONstring .= "]},"; //fin des joueurs d'une �quipe
//				
				////////////////////////////////////////////////////////////
					
							if(!strcmp(",", substr($JSONstring,-1)))// Pour �viter les vides;
							{$JSONstring = substr($JSONstring, 0,-1);}
				$JSONstring .= "]},"; //fin des �quipes d'une ligue
				$Iligue++;
				}//Fin d'une ligue valide
			}//Fin des scans de ligues.
							if(!strcmp(",", substr($JSONstring,-1)))// Pour �viter les vides;
							{$JSONstring = substr($JSONstring, 0,-1);}
		$JSONstring .= "],"; //fin des ligues
		$noLigue++;
	}//Fin du scan des ligues auquel l'utilisateur est abbonn�.
	
		$JSONstring = substr($JSONstring, 0,-1);
	$JSONstring .= "}";
	
//echo json_encode($Sommaire);
echo $JSONstring;
	

	
  
 //echo "Bidon "	
	
	
	
//$json=json_encode($ligueSelect);
//echo "[".$ligueSelect[0]."]";
//return $JSONobjet;

////////////////////  Reste � faire le mapping des ID de ligue vers des noms de ligues.	


mysqli_close($conn);

?>

