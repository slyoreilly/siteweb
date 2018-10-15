<?php

////////////////////////////////////////////////////////////
//
//	sommaire2JSON.php
//	Est appellé dans http://www.syncstats.com/zstats/match.html
//
//
////////////////////////////////////////////////////////////



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

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");


/////////////////////////////////////////////////////////////
//
//

function trouveNomJoueurParID($ID,$maConn){ 

$resultJoueur = mysqli_query($maConn,"SELECT * FROM TableJoueur WHERE joueur_id = '{$ID}'")
or die(mysqli_error($maConn));  
if($rangeeJoueur=mysqli_fetch_array($resultJoueur))
		  return ($rangeeJoueur['NomJoueur']); 
else { return ("Anonyme"); }
} 



/////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////
	//
//   Trouve Nom de l'equipe � partir du ID.
//
////////////////////////////////////////////////////

function trouveNomParIDEquipe($IEq,$maConn)
{
$resultEquipe2 = mysqli_query($maConn,"SELECT * FROM TableEquipe WHERE equipe_id='{$IEq}'")
or die(mysqli_error($maConn));  
while($rangeeEquipe2=mysqli_fetch_array($resultEquipe2))
{
			
		if($rangeeEquipe2['equipe_id']==$IEq)
	{
	$NomEquipe =$rangeeEquipe2['nom_equipe'];// Ce sont de INT
	}
}
//$NomEquipe ="1";
return $NomEquipe;
}

//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////
	
//$matchID = stripslashes(mysql_real_escape_string(stripslashes($_POST["matchId"])));
$matchID = $_POST['matchId'];	
//////////////////////////////////////////////

$resultEvent = mysqli_query($conn,"SELECT TableMatch.*, TEdom.nom_equipe AS NEdom,TEvis.nom_equipe AS NEvis, TEdom.equipe_id AS eqDomId,TEvis.equipe_id AS eqVisId
									 FROM TableMatch 
									JOIN TableEquipe AS TEdom 
										ON (TableMatch.eq_dom=TEdom.equipe_id)
									JOIN TableEquipe AS TEvis
										ON (TableMatch.eq_vis=TEvis.equipe_id)
									WHERE matchIdRef = '{$matchID}'")
or die(mysqli_error($conn));  


while($rangeeEv=mysqli_fetch_array($resultEvent))
{
	$mDate=$rangeeEv['date'];
	$mEqDom=$rangeeEv['NEdom'];
	$mEqVis=$rangeeEv['NEvis'];
	$mEqDomId=$rangeeEv['eqDomId'];
	$mEqVisId=$rangeeEv['eqVisId'];
}


		$resultAssoc = mysqli_query($conn,"SELECT match_id
									 FROM TableMatch 
									WHERE matchIdRef = '{$matchID}'") or die(mysqli_error($conn));  
	$rangeeAssoc= mysqli_fetch_row($resultAssoc);
	$matchPourVideos=$rangeeAssoc[0];	



//{
$qVids = 	"SELECT nomFichier,camId,chrono,eval,nbVues,etat,videoId,emplacement, nomThumbnail  FROM Video  WHERE nomMatch = '{$matchID}'  OR nomMatch = '{$matchPourVideos}' ORDER BY nomMatch, angleOk DESC";
$resultVids = mysqli_query($conn, $qVids)
or die(mysqli_error($conn).$qVids);  	
$mesVids=array();
$mesCam=array();
$mesChrono=array();
$mesEval=array();
$mesNbVues=array();
$mesEtat=array();
$mesVidId=array();
$mesEmplacement=array();
$mesThumbnails=array();
while($rangeeVids=mysqli_fetch_array($resultVids))
	{
		array_push($mesVids,$rangeeVids[0]);
		array_push($mesCam,$rangeeVids[1]);
		array_push($mesChrono,$rangeeVids[2]);
		array_push($mesEval,$rangeeVids[3]);
		array_push($mesNbVues,$rangeeVids[4]);
		array_push($mesEtat,$rangeeVids[5]);
		array_push($mesVidId,$rangeeVids[6]);
		array_push($mesEmplacement,$rangeeVids[7]);
		array_push($mesThumbnails,$rangeeVids[8]);
		}


$I0=0;
$Sommaire = array();
$Ibuts = 0;
$JSONstring = "{\"matchID\": \"".$matchID."\",";
$JSONstring .="\"date\": \"".$mDate."\",";
$JSONstring .="\"nbVids\": \"".count($mesVids)."\",";
$JSONstring .="\"eqDom\": \"".$mEqDom."\",";
$JSONstring .="\"eqVis\": \"".$mEqVis."\",";
$JSONstring .="\"eqDomId\": \"".$mEqDomId."\",";
$JSONstring .="\"eqVisId\": \"".$mEqVisId."\",";
//$JSONstring .="\"qVids\": \"".$qVids."\",";
//$JSONstring .="\"buts\": [";
$buts=array();
//foreach($equipe as $Ieq)
//{
	////////////////////////////////////////
	//
	//		Partie des Clips
	//
	//		NB: Les clips remplacent les évèenements de TableEvenement0.
	//		En principe, ils vont déclencher un nouvel élément de la table Video.
	//
	////////////////////////////////////////////////
$qClips="SELECT * FROM Clips WHERE matchId = '{$matchID}'  OR matchId = '{$matchPourVideos}' ORDER BY chrono";
	$resultClips = mysqli_query($conn,$qClips )
or die(mysqli_error($conn));  	
$IC=0;
$clips=array();
//$JSONstring .="\"qClips\": \"".$qClips."\",";
while($rangeeClips=mysqli_fetch_array($resultClips))
			{
				
				$clips[$IC]=array();
				$clips[$IC]['video']=array();
				$clips[$IC]['chrono']=$rangeeClips['chrono'];
					for($b=0;$b<count($mesVids);$b++)
					{
						if(abs($rangeeClips['chrono']-$mesChrono[$b])<20000)
						{
							$clips[$IC]['video'][count($clips[$IC]['video'])]['fic']=$mesVids[$b];
							$clips[$IC]['video'][count($clips[$IC]['video'])-1]['cam']=$mesCam[$b];
							$clips[$IC]['video'][count($clips[$IC]['video'])-1]['eval']=$mesEval[$b];
							$clips[$IC]['video'][count($clips[$IC]['video'])-1]['nbVues']=$mesNbVues[$b];
							$clips[$IC]['video'][count($clips[$IC]['video'])-1]['etat']=$mesEtat[$b];
							$clips[$IC]['video'][count($clips[$IC]['video'])-1]['videoId']=$mesVidId[$b];
							$clips[$IC]['video'][count($clips[$IC]['video'])-1]['chrono']=$rangeeClips['chrono'];
							$clips[$IC]['video'][count($clips[$IC]['video'])-1]['emplacement']=$mesEmplacement[$b];
							$clips[$IC]['video'][count($clips[$IC]['video'])-1]['thumbnail']=$mesThumbnails[$b];
				
				
						}
					}
				$IC++;
			}
		
$JSONstring .= "\"clips\": ".json_encode($clips).",";
$qEv="SELECT TableEvenement0.*,TableJoueur.* FROM TableEvenement0 JOIN TableJoueur ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id) WHERE match_event_id = '{$matchID}' AND code = 0 ORDER BY chrono";
$JSONstring .="\"qEv\": \"".$qEv."\",";
$resultEvent = mysqli_query($conn,$qEv) or die(mysqli_error($conn));  	


while($rangeeEv=mysqli_fetch_array($resultEvent))
	{
				$Sommaire[$Ibuts]['video']=array();
		for($b=0;$b<count($mesVids);$b++)
		{
			if(abs($rangeeEv['chrono']-$mesChrono[$b])<10000)
			{
				
				$Sommaire[$Ibuts]['video'][count($Sommaire[$Ibuts]['video'])]['fic']=$mesVids[$b];
				$Sommaire[$Ibuts]['video'][count($Sommaire[$Ibuts]['video'])-1]['cam']=$mesCam[$b];
				$Sommaire[$Ibuts]['video'][count($Sommaire[$Ibuts]['video'])-1]['eval']=$mesEval[$b];
				$Sommaire[$Ibuts]['video'][count($Sommaire[$Ibuts]['video'])-1]['nbVues']=$mesNbVues[$b];
				$Sommaire[$Ibuts]['video'][count($Sommaire[$Ibuts]['video'])-1]['etat']=$mesEtat[$b];
				$Sommaire[$Ibuts]['video'][count($Sommaire[$Ibuts]['video'])-1]['videoId']=$mesVidId[$b];
				$Sommaire[$Ibuts]['video'][count($Sommaire[$Ibuts]['video'])-1]['emplacement']=$mesEmplacement[$b];
				$Sommaire[$Ibuts]['video'][count($Sommaire[$Ibuts]['video'])-1]['thumbnail']=$mesThumbnails[$b];
							}
		}
		
//		foreach($mesVids as $val)
//		{
//			if(abs($rangeeEv['chrono']-substr($val,0,stripos($val,'.')))<20000)
//			{
//				$Sommaire[$Ibuts]['video']=$val;
//			}
//		}
		
		
				$Sommaire[$Ibuts]['equipe']=trouveNomParIDEquipe($rangeeEv['equipe_event_id'],$conn);
				$Sommaire[$Ibuts]['equipeId']=$rangeeEv['equipe_event_id'];
				$Sommaire[$Ibuts]['chrono']=$rangeeEv['chrono'];

				$qualif=0;
				$Sommaire[$Ibuts]['qualif']=null;
				if($rangeeEv['souscode']%10==3)
				{$Sommaire[$Ibuts]['qualif'][$qualif] = "TP";
				$qualif=$qualif+1;}
				if($rangeeEv['souscode']%10==9)
				{$Sommaire[$Ibuts]['qualif'][$qualif] = "FD";
				$qualif=$qualif+1;}
				if(floor($rangeeEv['souscode']/10)==4)
				{$Sommaire[$Ibuts]['qualif'][$qualif] = "DN";
				$qualif=$qualif+1;}
				if(floor($rangeeEv['souscode']/10)==5)
				{$Sommaire[$Ibuts]['qualif'][$qualif] = "AN";
				$qualif=$qualif+1;}
				
	
	
				$Sommaire[$Ibuts]['marqueur']=$rangeeEv['NomJoueur'];
				$Sommaire[$Ibuts]['noMarqueur']=$rangeeEv['NumeroJoueur'];
								$Sommaire[$Ibuts]['marqueurId']=$rangeeEv['joueur_event_ref'];
				$sql_passeurs = mysqli_query($conn,"SELECT TableEvenement0.joueur_event_ref,TableJoueur.* FROM TableEvenement0 
														JOIN TableJoueur
															ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id)
														WHERE match_event_id = '{$matchID}'  AND chrono = '{$rangeeEv['chrono']}' AND code = '1'")
				or die(mysqli_error($conn)); 
				$Ipas = 0;
				while($rangeeEv=mysqli_fetch_array($sql_passeurs))
				{
							if($Ipas==0)
							{
//								$Sommaire[$Ibuts]['passeur1']=trouveNomJoueurParID($rangeeEv['joueur_event_ref']);				 	
								$Sommaire[$Ibuts]['passeur1Id']=$rangeeEv['joueur_event_ref'];
				$Sommaire[$Ibuts]['passeur1']=$rangeeEv['NomJoueur'];
				$Sommaire[$Ibuts]['noPasseur1']=$rangeeEv['NumeroJoueur'];
							}else
							{
								//	$Sommaire[$Ibuts]['passeur2']=trouveNomJoueurParID($rangeeEv['joueur_event_ref']);				 	
								$Sommaire[$Ibuts]['passeur2Id']=$rangeeEv['joueur_event_ref'];
								$Sommaire[$Ibuts]['passeur2']=$rangeeEv['NomJoueur'];
								$Sommaire[$Ibuts]['noPasseur2']=$rangeeEv['NumeroJoueur'];
							}
						$Ipas++;	
				}
				//$JSONstring .= json_encode($Sommaire[$Ibuts]).",";
				$Ibuts++;
				
	}
	$buts['buts']=$Sommaire;
	//$JSONstring = substr($JSONstring, 0,-1);
	//$JSONstring .= "],";
	$JSONstring .= "\"buts\": ".json_encode($buts['buts']).",";
//////////////////////////////////////////////
// Section Pun.
$SomPun = array();
$JSONstring .="\"punitions\": ";
$IPun=0;
$resultPun = mysqli_query($conn,"SELECT * FROM TableEvenement0 WHERE match_event_id = '{$matchID}' AND code = 4 ORDER BY chrono")
or die(mysqli_error($conn));  	


while($rangeePun=mysqli_fetch_array($resultPun))
	{
				$SomPun[$IPun]['equipe']=trouveNomParIDEquipe($rangeePun['equipe_event_id'],$conn);
	
				$SomPun[$IPun]['chrono']=$rangeePun['chrono'];

				switch($rangeePun['souscode'])
				{
					case 1: $SomPun[$IPun]['motif']='Accrocher';
					break;
					case 2: $SomPun[$IPun]['motif']='Cingler';
					break;
					case 3: $SomPun[$IPun]['motif']='Conduite anti-sportive';
					break;
					case 4: $SomPun[$IPun]['motif']='Donner de la bande';
					break;
					case 5: $SomPun[$IPun]['motif']='Obstruction';
					break;
					case 6: $SomPun[$IPun]['motif']='Retarder le match';
					break;
					case 7: $SomPun[$IPun]['motif']='Retenu';
					break;
					case 8: $SomPun[$IPun]['motif']='Rudesse';
					break;
					case 9: $SomPun[$IPun]['motif']='S\'être battu';
					break;
					case 10: $SomPun[$IPun]['motif']='Trébucher';
					break;
					case 11: $SomPun[$IPun]['motif']='Assaut';
					break;
										case 12: $SomPun[$IPun]['motif']='Bâton élevé';
					break;
										case 13: $SomPun[$IPun]['motif']='Coup de coude';
					break;
										case 14: $SomPun[$IPun]['motif']='Cracher';
					break;
										case 15: $SomPun[$IPun]['motif']='Darder';
					break;
										case 16: $SomPun[$IPun]['motif']='Donner du genou';
					break;
										case 17: $SomPun[$IPun]['motif']='Double-échec';
					break;
										case 18: $SomPun[$IPun]['motif']='Instigateur';
					break;
										case 19: $SomPun[$IPun]['motif']='Mise en échec';
					break;
										case 20: $SomPun[$IPun]['motif']='Pousser un joueur';
					break;
										case 21: $SomPun[$IPun]['motif']='Six pouces';
					break;
										case 22: $SomPun[$IPun]['motif']='Tentative de blessure';
					break;
										case 23: $SomPun[$IPun]['motif']='Alignement en retard';
					break;
										case 24: $SomPun[$IPun]['motif']='Balle extérieure';
					break;
										case 25: $SomPun[$IPun]['motif']='Bâton brisé';
					break;
										case 26: $SomPun[$IPun]['motif']='Bâton haut (sans contact)';
					break;
										case 27: $SomPun[$IPun]['motif']='Changement illégal';
					break;
										case 28: $SomPun[$IPun]['motif']='Chute obstrusive';
					break;
										case 29: $SomPun[$IPun]['motif']='Geler la balle';
					break;
										case 30: $SomPun[$IPun]['motif']='Lancer le bâton';
					break;
										case 31: $SomPun[$IPun]['motif']='Pied sur la balle';
					break;
										case 32: $SomPun[$IPun]['motif']='Quitter banc des punitions';
					break;
										case 33: $SomPun[$IPun]['motif']='Pénalité majeure';
					break;
										case 34: $SomPun[$IPun]['motif']='Grossière inconduite';
					break;
										case 35: $SomPun[$IPun]['motif']='Pénalité de match';
					break;
										case 36: $SomPun[$IPun]['motif']='Mauvaise conduite';
					break;
										case 37: $SomPun[$IPun]['motif']='Inconduite de partie';
					break;
									}
				$SomPun[$IPun]['joueur']=trouveNomJoueurParID($rangeePun['joueur_event_ref'],$conn);
				$SomPun[$IPun]['joueurId']=$rangeePun['joueur_event_ref'];
//				$JSONstring .= json_encode($SomPun[$IPun]).",";
				$IPun++;
				
	}
//	if(!strcmp(substr($JSONstring, 0,-1),','))	
//		{$JSONstring = substr($JSONstring, 0,-1);}
//	$JSONstring .= "],";
				$JSONstring .= json_encode($SomPun).",";


////////////////////////////////////////////





$resultPeriode = mysqli_query($conn,"SELECT * FROM TableEvenement0 WHERE match_event_id = '{$matchID}' AND code = 11 ORDER BY souscode ASC")
or die(mysqli_error($conn));  	


	$periode=Array();
	$IP=0;
	
while($rangeePer=mysqli_fetch_array($resultPeriode))
	{
		if($rangeePer['souscode']<10)
		{
			$periode[$IP]['numero']=$rangeePer['souscode'];
			$periode[$IP]['type']="R";
			$periode[$IP]['chrono']=$rangeePer['chrono'];
		}
else
{if ($rangeePer['souscode']<100) {

	$valPer = $rangeePer['souscode']-10;
				$periode[$IP]['numero']=$valPer;
			$periode[$IP]['type']="P";
			$periode[$IP]['chrono']=$rangeePer['chrono'];
	

		}
		else
		{
				$valPer = $rangeePer['souscode']-100;
				$periode[$IP]['numero']=$valPer;
			$periode[$IP]['type']="F";
			$periode[$IP]['chrono']=$rangeePer['chrono'];
		}
		
	}
$IP++;
	}

	$JSONstring .= "\"periodes\": ".json_encode($periode).",";
	


$rFus = mysqli_query($conn,"SELECT TableEvenement0.*,TableJoueur.*,TableEquipe.* FROM TableEvenement0 
										JOIN TableJoueur
											ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id)
										JOIN TableEquipe
											ON (TableEvenement0.equipe_event_id=TableEquipe.equipe_id)
											 WHERE match_event_id = '{$matchID}' AND code = 2 ORDER BY chrono")
or die(mysqli_error($conn));  	

$fusillade =Array();
	$IF=0;
while($rangFus=mysqli_fetch_array($rFus))
	{
		$fusillade[$IF]=array();
		if($rangFus['souscode']==1)
		{
			$fusillade[$IF]['nom']=$rangFus['NomJoueur'];
			$fusillade[$IF]['equipe']=$rangFus['nom_equipe'];
			$fusillade[$IF]['but']=true;
		}
		if($rangFus['souscode']==5)
		{
			$fusillade[$IF]['nom']=$rangFus['NomJoueur'];
			$fusillade[$IF]['equipe']=$rangFus['nom_equipe'];
			$fusillade[$IF]['but']=false;
		}
		$IF++;
	}


//}
	
//echo json_encode($Sommaire);

$JSONstring.= "\"Fusillade\":".json_encode($fusillade)."}";
echo  $JSONstring;	
	
	
//$sommaire= json_decode($JSONstring);

foreach($Sommaire as $buts )
{
	if(count($buts['video'])>0)
	{
		for($a=0; $a<count($buts['video']);$a++)
		{
		$reqIns = "UPDATE Video SET tagPrincipal='{$buts['marqueurId']}' WHERE videoId='{$buts['video'][$a]['videoId']}'";
		mysqli_query($conn, $reqIns)or die(mysqli_error($conn));
		}
	}
}
	mysqli_close($conn);
//include('../scriptsphp/vidsInfos.php');

?>
