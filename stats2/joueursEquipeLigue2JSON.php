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

$ligueId = $_POST['ligueId'];
$equipeId = $_POST['equipeId'];

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");

}
	
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

$ligueSelect = array();
$equipeSelect = array();
$joueurSelect = array();

$noLigue =0;
$Iligue = 0;
$Iequipe = 0;


				$resultLigue = mysql_query("SELECT * 
												FROM Ligue 
													WHERE ID_Ligue={$ligueId}")
				or die(mysql_error());  
				while($rangeeLigue=mysql_fetch_array($resultLigue))
					{$nomLigue = $rangeeLigue['Nom_Ligue'];}

	
		/////////////////////////////////
		//
		//	Structure JSON:
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
		$maRep=array();
		$maRep['equipe']=array();
				$JSONstring .=  "{\"equipe\": [";
				$c=0;
				$Ieq=0;
				$rangeeEquipe=null;
				$resultEquipe = NULL;
				$joueurs = array();
				$resultEquipe = mysql_query("SELECT * 
												FROM {$tableEq} 
												JOIN abonEquipeLigue
													ON (TableEquipe.equipe_id=abonEquipeLigue.equipeId)
													WHERE ligueId={$ligueId} 
													AND abonEquipeLigue.debutAbon<=NOW()
													AND abonEquipeLigue.finAbon>=NOW()")// Inclure durée de l'abonnement
				or die(mysql_error());  
				while($rangeeEquipe=mysql_fetch_array($resultEquipe))
					{
//					if($rangeeEquipe['ligue_equipe_ref']==$rangeeLigue['ID_Ligue'])
//						{
							$maRep['equipe'][$Ieq]=array();
						$maRep['equipe'][$Ieq]['nomEquipe']=$rangeeEquipe['nom_equipe'];
						$maRep['equipe'][$Ieq]['equipeId']=$rangeeEquipe['equipe_id'];
						$maRep['equipe'][$Ieq]['ficId']=$rangeeEquipe['ficId'];
						$maRep['equipe'][$Ieq]['logo']=$rangeeEquipe['logo'];
						$maRep['equipe'][$Ieq]['couleur1']=$rangeeEquipe['couleur1'];
						$maRep['equipe'][$Ieq]['joueur']=array();
						
						$resultJoueur = mysql_query("SELECT * 
													FROM {$tableJoueur}
													JOIN abonJoueurEquipe
														ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId)
														WHERE equipeId={$rangeeEquipe['equipe_id'] }
														AND debutAbon<=NOW()
														AND finAbon>NOW()")
						or die(mysql_error());  
						$rangeeJoueur=0;
						$Ij=0;
						while($rangeeJoueur=mysql_fetch_array($resultJoueur))
							{if($rangeeJoueur['equipeId']==$rangeeEquipe['equipe_id'])
								{
								$maRep['equipe'][$Ieq]['joueur'][$Ij]=array();
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['nomJoueur']=$rangeeJoueur['NomJoueur'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['joueurId']=$rangeeJoueur['joueur_id'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['maj']=$rangeeJoueur['dernierMAJ'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['position']=$rangeeJoueur['position'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['noJoueur']=$rangeeJoueur['NumeroJoueur'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['eval']=null;
										
									$JSONstring .=  "{\"nomJoueur\": \"".$rangeeJoueur['NomJoueur']."\", ";
								$JSONstring .=  "\"joueurId\": \"".$rangeeJoueur['joueur_id']."\", ";
								$JSONstring .=  "\"maj\": \"".$rangeeJoueur['dernierMAJ']."\", ";
								$JSONstring .=  "\"position\": \"".$rangeeJoueur['position']."\", ";
								$JSONstring .=  "\"noJoueur\": \"".$rangeeJoueur['NumeroJoueur']."\"},";
								array_push($joueurs,$rangeeJoueur['joueur_id']);
								$Ij++;	
								
								}
								
							}//Fin du scan des joueurs
							if(!strcmp(",", substr($JSONstring,-1)))// Pour �viter les vides;
							{$JSONstring = substr($JSONstring, 0,-1);}
						$JSONstring .= "]},"; //fin des joueurs d'une �quipe
//						}// Fin d'une �quipe valide
					$Ieq++;
					}// Fin des scans d'�quipes
					
				
				////////////////////////////
				//	Section agents libres
				
										$maRep['equipe'][$Ieq]=array();
						$maRep['equipe'][$Ieq]['nomEquipe']=$nomLigue." - Libres";
						$maRep['equipe'][$Ieq]['equipeId']=0;
						$maRep['equipe'][$Ieq]['ficId']="1";
						$maRep['equipe'][$Ieq]['logo']="rien";
						$maRep['equipe'][$Ieq]['couleur1']="CCCCCC";
						$maRep['equipe'][$Ieq]['joueur']=array();
						$resultJoueur3 = mysql_query("SELECT * 
													FROM abonJoueurLigue
														JOIN TableJoueur
															ON (TableJoueur.joueur_id=abonJoueurLigue.joueurId)
																WHERE ligueId={$ligueId}
																AND debutAbon<=NOW()
														AND finAbon>NOW()")
						or die(mysql_error());  
						$rangeeJoueur=0;
						$Ij=0;
						while($rangeeJoueur=mysql_fetch_array($resultJoueur3))
								{
										if(in_array($rangeeJoueur['joueur_id'],$joueurs)==false)
										{
								$maRep['equipe'][$Ieq]['joueur'][$Ij]=array();
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['nomJoueur']=$rangeeJoueur['NomJoueur'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['joueurId']=$rangeeJoueur['joueur_id'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['maj']=$rangeeJoueur['dernierMAJ'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['position']=$rangeeJoueur['position'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['noJoueur']=$rangeeJoueur['NumeroJoueur'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['eval']=null;
								array_push($joueurs,$rangeeJoueur['joueur_id']);
								$Ij++;
										}
								}//Fin du scan des joueurs
								
								
								$resultJoueur2 = mysql_query("SELECT * 
													FROM TableJoueur
																WHERE Ligue={$ligueId}")
						or die(mysql_error());  
						$rangeeJoueur=0;
						while($rangeeJoueur=mysql_fetch_array($resultJoueur2))
								{
										if(in_array($rangeeJoueur['joueur_id'],$joueurs)==false)
										{
								$maRep['equipe'][$Ieq]['joueur'][$Ij]=array();
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['nomJoueur']=$rangeeJoueur['NomJoueur'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['joueurId']=$rangeeJoueur['joueur_id'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['maj']=$rangeeJoueur['dernierMAJ'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['position']=$rangeeJoueur['position'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['noJoueur']=$rangeeJoueur['NumeroJoueur'];
								$maRep['equipe'][$Ieq]['joueur'][$Ij]['eval']=null;
								array_push($joueurs,$rangeeJoueur['joueur_id']);
								$Ij++;
										}
								}//Fin du scan des joueurs
				$Iligue++;


$qIndJDom =	"SELECT evalue,AVG(valeur)
						FROM EvaluationJoueurs
						 WHERE ligueId=$ligueId
						 GROUP BY evalue";
$mEval = mysql_query($qIndJDom)or die(mysql_error().$qIndJDom);	

$b=0;
while($r = mysql_fetch_array($mEval)) {
    $vecEval[$b][0] = $r[0];
	    $vecEval[$b][1] = $r[1];
	
$b++;	
}
					

function plugEval(&$valeur,$cle)
{
	global $jId;
	global $lEval;
	if(!strcmp($cle,"joueur"))
	{
		foreach($valeur as $val2)
			{
			if(!strcmp($val2['joueurId'],$jId))
			{$val2['eval']=$lEval;
			}
			}
			unset($val2);
	}
}
					
foreach($vecEval as $vec)
{$jId=$vec[0];
$lEval=$vec[1];

foreach($maRep['equipe'] as &$val1)
{
	foreach($val1['joueur'] as &$val2)
	{//echo $val2['joueurId']."-";
	//echo $lEval."-";
	
		if($val2['joueurId']==$jId)
			{$val2['eval']=$lEval;
			}
		
	}
	unset($val2);
	
}					
unset($val1);


//array_walk_recursive($maRep, 'plugEval');
}
unset($vec);
					
					


							 	

	


	echo json_encode($maRep,true);

	
  
 //echo "Bidon "	
	
	
	
//$json=json_encode($ligueSelect);
//echo "[".$ligueSelect[0]."]";
//return $JSONobjet;

////////////////////  Reste � faire le mapping des ID de ligue vers des noms0 de ligues.	




?>
<?php  ?>
