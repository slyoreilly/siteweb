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

/////////////////////////////////////////////////////
	//
//   Trouve Nom de l'equipe � partir du ID.
//
////////////////////////////////////////////////////

function trouveNomParIDEquipe($IEq)
{
$resultEquipe2 = mysqli_query($conn,"SELECT * FROM TableEquipe WHERE equipe_id='{$IEq}'")
or die(mysqli_error($conn));  
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
$videoId = $_POST['videoId'];	
//////////////////////////////////////////////

$resultEvent = mysqli_query($conn,"SELECT TableMatch.*, TEdom.nom_equipe AS NEdom,TEvis.nom_equipe AS NEvis, TEdom.equipe_id AS eqDomId,TEvis.equipe_id AS eqVisId
									 FROM TableMatch 
									JOIN TableEquipe AS TEdom 
										ON (TableMatch.eq_dom=TEdom.equipe_id)
									JOIN TableEquipe AS TEvis
										ON (TableMatch.eq_vis=TEvis.equipe_id)
									WHERE matchIdRef = '{$matchID}'")
or die(mysqli_error($conn));  


while($rangeeEv=mysqli_fetch_array($conn,$resultEvent))
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
$JSONstring .="\"qVids\": \"".$qVids."\",";
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

	$resultClips = mysqli_query($conn, "SELECT * FROM Clips 
											 WHERE matchId = '{$matchID}'  OR matchId = '{$matchPourVideos}' ORDER BY chrono")
or die(mysqli_error($conn));  	
$IC=0;
$clips=array();

while($rangeeClips=mysqli_fetch_array($conn,$resultClips))
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
		
$resultEvent = mysqli_query($conn,"SELECT TableEvenement0.*,TableJoueur.* FROM TableEvenement0 
										JOIN TableJoueur
											ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id)
											 WHERE match_event_id = '{$matchID}' AND code = 0 ORDER BY chrono")
or die(mysqli_error($conn));  	


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
		
		
				$Sommaire[$Ibuts]['equipe']=trouveNomParIDEquipe($rangeeEv['equipe_event_id']);
	
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



$JSONstring.="}";
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
