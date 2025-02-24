<?php

require '../scriptsphp/defenvvar.php';

$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

if(isset($_POST['ligueId'])){
$ligueId = $_POST['ligueId'];
}if(isset($_POST['equipeId'])){
$equipeId = $_POST['equipeId'];
}

$ligueSelect = array();
$equipeSelect = array();
$joueurSelect = array();

$noLigue =0;
$Iligue = 0;
$Iequipe = 0;


				$resultLigue = mysqli_query($conn,"SELECT * 
												FROM Ligue 
													WHERE ID_Ligue={$ligueId}")
				or die(mysqli_error($conn));  
				while($rangeeLigue=mysqli_fetch_array($resultLigue))
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
				$JSONstring =  "{\"equipe\": [";
				$c=0;
				$Ieq=0;
				$rangeeEquipe=null;
				$resultEquipe = NULL;
				$joueurs = array();
				$resultEquipe = mysqli_query($conn,"SELECT * 
												FROM {$tableEq} 
												JOIN abonEquipeLigue
													ON (TableEquipe.equipe_id=abonEquipeLigue.equipeId)
													WHERE ligueId={$ligueId} 
													AND abonEquipeLigue.debutAbon<=NOW()
													AND abonEquipeLigue.finAbon>=NOW()")// Inclure durée de l'abonnement
				or die(mysqli_error($conn));  
				while($rangeeEquipe=mysqli_fetch_array($resultEquipe))
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
						
						$resultJoueur = mysqli_query($conn,"SELECT * 
													FROM {$tableJoueur}
													JOIN abonJoueurEquipe
														ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId)
														WHERE equipeId={$rangeeEquipe['equipe_id'] }
														AND debutAbon<=NOW()
														AND finAbon>NOW()")
						or die(mysqli_error($conn));  
						$rangeeJoueur=0;
						$Ij=0;
						while($rangeeJoueur=mysqli_fetch_array($resultJoueur))
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
						$resultJoueur3 = mysqli_query($conn,"SELECT * 
													FROM abonJoueurLigue
														JOIN TableJoueur
															ON (TableJoueur.joueur_id=abonJoueurLigue.joueurId)
																WHERE ligueId={$ligueId}
																AND debutAbon<=NOW()
														AND finAbon>NOW()")
						or die(mysqli_error($conn));  
						$rangeeJoueur=0;
						$Ij=0;
						while($rangeeJoueur=mysqli_fetch_array($resultJoueur3))
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
								
								/*
								$resultJoueur2 = mysqli_query($conn,"SELECT * 
													FROM TableJoueur
																WHERE Ligue={$ligueId}")
						or die(mysqli_error($conn));  
						$rangeeJoueur=0;
						while($rangeeJoueur=mysqli_fetch_array($resultJoueur2))
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
								}*///Fin du scan des joueurs
				$Iligue++;


$qIndJDom =	"SELECT evalue,AVG(valeur)
						FROM EvaluationJoueurs
						 WHERE ligueId=$ligueId
						 GROUP BY evalue";
$mEval = mysqli_query($conn,$qIndJDom)or die(mysqli_error($conn).$qIndJDom);	

$b=0;
$vecEval=Array();
while($r = mysqli_fetch_array($mEval)) {
	$vecEval[$b]=Array();
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

mysqli_close($conn);


?>

