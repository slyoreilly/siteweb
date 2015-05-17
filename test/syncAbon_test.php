<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$username = $_GET['username'];
$vielledate = date("Y/m/d H:i:s",$_GET['vielledate']);


$ligueId = $_GET['ligueId'];

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");

}
	
	
	// Retrieve all the data from the "example" table
$resultUser = mysql_query("SELECT * FROM TableUser")
or die(mysql_error());  
while($rangeeUser=mysql_fetch_array($resultUser))
{
		if(!strcmp($rangeeUser['username'],$username))
	{$userSelect =$rangeeUser['ref_id'];
	}
		// Prend le ID du user pour trouver les ligues abonn�es.
}


$forceSync=false;
	
$resultAbon = mysql_query("SELECT * FROM AbonnementLigue 
								JOIN TableUser
									ON (AbonnementLigue.userid=TableUser.noCompte)
										WHERE ligueid={$ligueId}")
or die(mysql_error()." SELECT * FROM AbonnementLigue 
								JOIN TableUser");  

$AbonSelect = array();
while($rangeeAbon=mysql_fetch_array($resultAbon))
	{
	$resultLigue = mysql_query("SELECT * FROM {$tableLigue} 
									WHERE ID_Ligue={$ligueId}")
	or die(mysql_error());  
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
		while($rangeeLigue=mysql_fetch_array($resultLigue))
			{
				$JSONstring .=  "{\"nomLigue\": \"".$rangeeLigue['Nom_Ligue']."\", ";
				$JSONstring .=  "\"ligueId\": \"".$rangeeLigue['ID_Ligue']."\", ";
				$JSONstring .=  "\"equipe\": [";
				$c=0;
				$rangeeEquipe=null;
				$resultEquipe = NULL;
				unset($joueurs);
				$joueurs=array();
				$resultEquipe = mysql_query("SELECT TableEquipe.*,abonEquipeLigue.* 
												FROM abonEquipeLigue
												JOIN {$tableEq} 
													ON(TableEquipe.equipe_id=abonEquipeLigue.equipeId)
													WHERE ligueId={$rangeeLigue['ID_Ligue']}
														AND debutAbon <= NOW() 
														AND finAbon >= NOW()")
				or die(mysql_error());  
				while($rangeeEquipe=mysql_fetch_array($resultEquipe))
					{
//					if($rangeeEquipe['ligue_equipe_ref']==$rangeeLigue['ID_Ligue'])
//						{
						unset($resultJoueur);
						//echo $rangeeEquipe['nom_equipe']." ".$rangeeEquipe['dernierMAJ']."  ".$vielledate."\//////  ";
						if(strtotime($rangeeEquipe['dernierMAJ'])>strtotime($vielledate))
							$forceSync=true;
	
						$JSONstring .=  "{\"nomEquipe\": \"".$rangeeEquipe['nom_equipe']."\", ";
						$JSONstring .=  "\"equipeId\": \"".$rangeeEquipe['equipe_id']."\", ";
						$JSONstring .=  "\"logo\": \"".$rangeeEquipe['logo']."\", ";
						$JSONstring .=  "\"joueur\": [";
						$resultJoueur = mysql_query("SELECT TableJoueur.*, abonJoueurEquipe.* 
													FROM abonJoueurEquipe
													JOIN {$tableJoueur}
														ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId)
														WHERE equipeId={$rangeeEquipe['equipe_id'] }
														AND debutAbon <= NOW() 
														AND finAbon >= NOW()
															AND dernierMAJ>'{$vielledate}'")
						or die(mysql_error());  
						$rangeeJoueur=0;
						while($rangeeJoueur=mysql_fetch_array($resultJoueur))
							{if($rangeeJoueur['equipeId']==$rangeeEquipe['equipe_id'])
								{$JSONstring .=  "{\"nomJoueur\": \"".$rangeeJoueur['NomJoueur']."\", ";
								$JSONstring .=  "\"joueurId\": \"".$rangeeJoueur['joueur_id']."\", ";
								$JSONstring .=  "\"maj\": \"".$rangeeJoueur['dernierMAJ']."\", ";
								$JSONstring .=  "\"position\": \"".$rangeeJoueur['position']."\", ";
								$JSONstring .=  "\"noJoueur\": \"".$rangeeJoueur['NumeroJoueur']."\"},";
								array_push($joueurs,$rangeeJoueur['joueur_id']);
								}
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
						$resultJoueur2 = mysql_query("SELECT * 
													FROM abonJoueurLigue
														JOIN TableJoueur
															ON (TableJoueur.joueur_id=abonJoueurLigue.joueurId)
																WHERE ligueId={$rangeeLigue['ID_Ligue']}
																AND dernierMAJ>'{$vielledate}'
																AND debutAbon <= NOW() 
																AND finAbon >= NOW()")
						
						or die(mysql_error());  
						$rangeeJoueur=0;
						while($rangeeJoueur=mysql_fetch_array($resultJoueur2))
								{
									if(in_array($rangeeJoueur['joueur_id'],$joueurs)==false)
										{$JSONstring .=  "{\"nomJoueur\": \"".$rangeeJoueur['NomJoueur']."\", ";
										$JSONstring .=  "\"joueurId\": \"".$rangeeJoueur['joueur_id']."\", ";
										$JSONstring .=  "\"position\": \"".$rangeeJoueur['position']."\", ";
										$JSONstring .=  "\"maj\": \"".$rangeeJoueur['dernierMAJ']."\", ";
										$JSONstring .=  "\"noJoueur\": \"".$rangeeJoueur['NumeroJoueur']."\"},";
										array_push($joueurs,$rangeeJoueur['joueur_id']);
										}
								}//Fin du scan des joueurs
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
							if(!strcmp(",", substr($JSONstring,-1)))// Pour �viter les vides;
							{$JSONstring = substr($JSONstring, 0,-1);}
		$JSONstring .= "],"; //fin des ligues
		$noLigue++;
	}
	
/*
	
 */header("HTTP/1.1 200 OK");
//echo " ".count($AbonSelect);
	
	$JSONstring = substr($JSONstring, 0,-1);
	$JSONstring .= "}";
	
//echo json_encode($Sommaire);
if (count($joueurs)!=0)
	echo $JSONstring;
else
	{
	if($forceSync==true)
		echo $JSONstring;
	else
		echo "";
//			echo $_GET['vielledate']."  ".strtotime($vielledate)." ".$JSONstring;
	
	}



?>
<?php  ?>
