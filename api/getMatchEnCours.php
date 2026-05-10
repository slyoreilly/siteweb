<?php

////////////////////////////////////////////////////////////
//
//	getMatchEnCours.php
//	Est appellé dans MatchRepository.kt
//
//
////////////////////////////////////////////////////////////


require '../scriptsphp/defenvvar.php';

header('Content-Type: application/json; charset=utf-8');

function repondreErreurJson($codeHttp, $codeErreur, $message)
{
	http_response_code($codeHttp);
	echo json_encode(array(
		'ok' => false,
		'error' => $codeErreur,
		'message' => $message
	));
	exit;
}

function executerRequeteJson($conn, $requete, $codeErreur, $message)
{
	$resultat = mysqli_query($conn, $requete);
	if($resultat === false)
	{
		error_log('[getMatchEnCours] ' . $codeErreur . ': ' . mysqli_error($conn));
		repondreErreurJson(500, $codeErreur, $message);
	}

	return $resultat;
}

//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////
	
//$matchID = stripslashes(mysql_real_escape_string(stripslashes($_POST["matchId"])));
$matchID = isset($_POST['matchId']) ? trim((string)$_POST['matchId']) : '';
if($matchID === '' || strtolower($matchID) === 'null')
{
	repondreErreurJson(400, 'matchId_manquant', 'matchId est requis.');
}
$matchIDSql = mysqli_real_escape_string($conn, $matchID);
//////////////////////////////////////////////

$resultEvent = mysqli_query($conn,"SELECT TableMatch.*, TEdom.nom_equipe AS NEdom,TEvis.nom_equipe AS NEvis, TEdom.equipe_id AS eqDomId,TEvis.equipe_id AS eqVisId
									 FROM TableMatch 
									JOIN TableEquipe AS TEdom 
										ON (TableMatch.eq_dom=TEdom.equipe_id)
									JOIN TableEquipe AS TEvis
										ON (TableMatch.eq_vis=TEvis.equipe_id)
									WHERE matchIdRef = '{$matchIDSql}'");
if($resultEvent === false)
{
	error_log('[getMatchEnCours] erreur SQL chargement match: ' . mysqli_error($conn));
	repondreErreurJson(500, 'erreur_sql_match', 'Erreur lors du chargement du match.');
}

$matchTrouve = false;
$mDate = null;
$mEqDom = null;
$mEqVis = null;
$mEqDomId = null;
$mEqVisId = null;
$mStatut = null;
$mLigueId = null;
$mArenaId = null;

while($rangeeEv=mysqli_fetch_array($resultEvent))
{
	$matchTrouve = true;
	$mDate=$rangeeEv['date'];
	$mEqDom=$rangeeEv['NEdom'];
	$mEqVis=$rangeeEv['NEvis'];
	$mEqDomId=$rangeeEv['eqDomId'];
	$mEqVisId=$rangeeEv['eqVisId'];
	$mStatut=$rangeeEv['statut'];
	$mLigueId = $rangeeEv['ligueRef'];
	$mArenaId = $rangeeEv['arenaId'];
}

if(!$matchTrouve)
{
	repondreErreurJson(404, 'match_introuvable', 'Aucun match ne correspond au matchId fourni.');
}


		$resultAssoc = mysqli_query($conn,"SELECT match_id
									 FROM TableMatch 
									WHERE matchIdRef = '{$matchIDSql}'");
	if($resultAssoc === false)
	{
		error_log('[getMatchEnCours] erreur SQL association match: ' . mysqli_error($conn));
		repondreErreurJson(500, 'erreur_sql_match', 'Erreur lors du chargement de l association du match.');
	}
	$rangeeAssoc= mysqli_fetch_row($resultAssoc);
	if($rangeeAssoc === null || $rangeeAssoc === false || !isset($rangeeAssoc[0]))
	{
		repondreErreurJson(404, 'match_introuvable', 'Aucun identifiant interne ne correspond au matchId fourni.');
	}
	$matchPourVideos=$rangeeAssoc[0];	



	////////////////////////////////////////
	//
	//		Partie des Clips
	//
	//		NB: Les clips remplacent les évèenements de TableEvenement0.
	//		En principe, ils vont déclencher un nouvel élément de la table Video.
	//
	////////////////////////////////////////////////

	
//{
$qVids = "
	SELECT nomFichier,camId,Clips.chrono,eval,nbVues,etat,videoId,emplacement, nomThumbnail,reference , TypeClips.labelFrench, TypeClips.labelEnglish
		 FROM Video 
		 INNER JOIN Clips
			 ON (Clips.clipId=Video.reference)
		LEFT JOIN TypeClips 
			ON (TypeClips.typeClipId = Clips.type)
	WHERE (nomMatch = '{$matchIDSql}'  OR nomMatch = '{$matchPourVideos}') AND Video.type=5 ORDER BY clipId, angleOk DESC";
$resultVids = executerRequeteJson($conn, $qVids, 'erreur_sql_clips', 'Erreur lors du chargement des clips.');

$bufEvent=0;
$clips=array();
while($rangeeVids=mysqli_fetch_array($resultVids))
	{
		if($rangeeVids['reference']!=$bufEvent){
			$unClip=array();
			$unClip['chrono']=$rangeeVids['chrono'];
			$unClip['clipId']=$rangeeVids['reference'];
			$unClip['labelFrench']=$rangeeVids['labelFrench'];
			$unClip['labelEnglish']=$rangeeVids['labelEnglish'];
			$unClip['video']=array();
			
			array_push($clips,$unClip);
			
		}
		$unVideo=array();

		$unVideo['fic']=$rangeeVids['nomFichier'];
		$unVideo['cam']=$rangeeVids['camId'];
		$unVideo['eval']=$rangeeVids['eval'];
		$unVideo['nbVues']=$rangeeVids['nbVues'];
		$unVideo['etat']=$rangeeVids['etat'];
		$unVideo['videoId']=$rangeeVids['videoId'];
		$unVideo['emplacement']=$rangeeVids['emplacement'];
		$unVideo['thumbnail']=$rangeeVids['nomThumbnail'];
		array_push($unClip['video'],$unVideo);
//		array_push($clips,$unClip);
		$bufEvent=$rangeeVids['reference'];
//		array_push($unClip['video'],$unVideo);
		end($clips);
		$key = key($clips);
		$clips[$key]=$unClip;
		reset($clips);

		}


$I0=0;
$Sommaire = array();
$Sommaire['clips']=$clips;
$Ibuts = 0;
$Sommaire['matchID']=$matchID;
$Sommaire['matchId']=$matchPourVideos;
$Sommaire['date']=$mDate;
$Sommaire['eqDom']=$mEqDom;
$Sommaire['eqVis']=$mEqVis;
$Sommaire['eqDomId']=$mEqDomId;
$Sommaire['eqVisId']=$mEqVisId;
$Sommaire['etat']=$mStatut;
$Sommaire['ligueId']=$mLigueId;
if(!is_null($mArenaId)){$Sommaire['arenaId']=$mArenaId;}

	$qEv="SELECT 
	TableEvenement0.event_id,
	TableEvenement0.souscode,
	TableEvenement0.chrono as mChrono,
	TableEvenement0.joueur_event_ref,
	TableEquipe.nom_equipe,
	TableEquipe.equipe_id,
	Passeur1.joueur_event_ref AS passeur1Id,
	Passeur1.NomJoueur AS passeur1,
	Passeur1.NumeroJoueur AS noPasseur1,
	Passeur1.P2jId AS passeur2Id,
	Passeur1.P2nomJoueur AS passeur2,
	Passeur1.P2noJoueur AS noPasseur2,
	Passeur1.premier_passeur AS passeur1_eventId,
	Passeur1.P2eId AS passeur2_eventId,
	TableJoueur.NomJoueur,
	TableJoueur.NumeroJoueur,
	Video.*,
	TableMatch.match_id
	FROM
	TableEvenement0
		INNER JOIN
	TableMatch ON (TableMatch.matchIdRef = TableEvenement0.match_event_id)
		INNER JOIN
	TableJoueur ON (TableEvenement0.joueur_event_ref = TableJoueur.joueur_id)
		INNER JOIN
	TableEquipe ON (TableEquipe.equipe_id = TableEvenement0.equipe_event_id)
		LEFT JOIN
	Video ON (Video.reference = TableEvenement0.event_id)
		AND nomMatch = '{$matchPourVideos}'
	LEFT JOIN(
        SELECT 
	   		TableEvenement0.joueur_event_ref,
			TableEvenement0.chrono,
			TableEvenement0.event_id as premier_passeur,
			TableJoueur.NomJoueur,
		   	TableJoueur.NumeroJoueur,
			Passeur2.NomJoueur AS P2nomJoueur,
		  	Passeur2.NumeroJoueur AS P2noJoueur,
		   	Passeur2.joueur_event_ref AS P2jId,
			Passeur2.event_id AS P2eId
		FROM
			TableEvenement0
		INNER JOIN TableJoueur ON (TableEvenement0.joueur_event_ref = TableJoueur.joueur_id)
		INNER JOIN TableMatch ON (TableMatch.matchIdRef = TableEvenement0.match_event_id)
		LEFT JOIN (
        	SELECT 
              	event_id,
		    	joueur_event_ref,
				chrono,
				TableJoueur.NomJoueur,
				TableJoueur.NumeroJoueur
			FROM
				TableEvenement0
               	INNER JOIN TableJoueur ON (TableEvenement0.joueur_event_ref = TableJoueur.joueur_id)
               	INNER JOIN TableMatch ON (TableMatch.matchIdRef = TableEvenement0.match_event_id)    
				WHERE event_id IN (
    				SELECT if(COUNT(*) >1, MAX(event_id),NULL) as max_event_id
                 	FROM TableEvenement0 
        			INNER JOIN TableMatch ON (TableMatch.matchIdRef = TableEvenement0.match_event_id)  
        		 	WHERE TableEvenement0.code = 1
						AND match_id = '{$matchPourVideos}' 
                	GROUP BY chrono
    			)
    	) AS Passeur2 ON (
                (Passeur2.chrono = TableEvenement0.chrono)
            	)
		
		WHERE
			TableEvenement0.code = 1
			AND match_id = '{$matchPourVideos}' AND ((TableEvenement0.event_id <> Passeur2.event_id) OR Passeur2.event_id IS NULL )
    ) AS Passeur1 ON (Passeur1.chrono = TableEvenement0.chrono) 
	WHERE
		(match_id = '{$matchPourVideos}')
		AND (TableEvenement0.code = 0)
		AND (Video.type = 0 OR Video.type IS NULL)
	ORDER BY TableEvenement0.chrono , angleOk DESC";

	

$setupMySql = executerRequeteJson($conn, "SET SQL_BIG_SELECTS=1", 'erreur_sql_setup', 'Erreur lors de la preparation de la requete.');
$resultEvent = executerRequeteJson($conn, $qEv, 'erreur_sql_buts', 'Erreur lors du chargement des buts.');
$buts=array();
//$Sommaire['qEv']=$qEv;

while($rangeeEv=mysqli_fetch_array($resultEvent))
	{
		if($rangeeEv['event_id']!=$bufEvent){
			$unBut=array();
			$unBut['chrono']=$rangeeEv['mChrono'];
			$unBut['equipe']=$rangeeEv['nom_equipe'];
			$unBut['equipeId']=$rangeeEv['equipe_id'];
			
			$unBut['marqueur']=$rangeeEv['NomJoueur'];
			$unBut['noMarqueur']=$rangeeEv['NumeroJoueur'];
			$unBut['marqueurId']=$rangeeEv['joueur_event_ref'];
			$unBut['but_event_id']=$rangeeEv['event_id'];

			if(!is_null($rangeeEv['passeur1Id'])){			
				$unBut['passeur1Id']=$rangeeEv['passeur1Id'];
				$unBut['passeur1']=$rangeeEv['passeur1'];
				$unBut['noPasseur1']=$rangeeEv['noPasseur1'];
				$unBut['passe1_event_id']=$rangeeEv['passeur1_eventId'];
			}

			if(!is_null($rangeeEv['passeur2Id'])){			
				$unBut['passeur2Id']=$rangeeEv['passeur2Id'];
				$unBut['passeur2']=$rangeeEv['passeur2'];
				$unBut['noPasseur2']=$rangeeEv['noPasseur2'];
				$unBut['passe2_event_id']=$rangeeEv['passeur2_eventId'];
			}




			$qualif=0;
			$unBut['qualif']=null;
			if($rangeeEv['souscode']%10==3)
			{$unBut['qualif'][$qualif] = "TP";
			$qualif=$qualif+1;}
			if($rangeeEv['souscode']%10==9)
			{$unBut['qualif'][$qualif] = "FD";
			$qualif=$qualif+1;}
			if(floor($rangeeEv['souscode']/10)==4)
			{$unBut['qualif'][$qualif] = "DN";
			$qualif=$qualif+1;}
			if(floor($rangeeEv['souscode']/10)==5)
			{$unBut['qualif'][$qualif] = "AN";
			$qualif=$qualif+1;}


			//if(!is_null($rangeeEv['videoId'])){
			$unBut['video']=array();
			//}
			array_push($buts,$unBut);
			
			$bufEvent= $rangeeEv['event_id'];
		}
		$unVideo=array();

		if(!is_null($rangeeEv['videoId'])){
		$unVideo['fic']=$rangeeEv['nomFichier'];
		$unVideo['cam']=$rangeeEv['camId'];
		$unVideo['eval']=$rangeeEv['eval'];
		$unVideo['nbVues']=$rangeeEv['nbVues'];
		$unVideo['etat']=$rangeeEv['etat'];
		$unVideo['videoId']=$rangeeEv['videoId'];
		$unVideo['emplacement']=$rangeeEv['emplacement'];
		$unVideo['thumbnail']=$rangeeEv['nomThumbnail'];

		array_push($unBut['video'],$unVideo);}
		end($buts);
		$key = key($buts);
		$buts[$key]=$unBut;
		reset($buts);
		

	}



	$Sommaire['buts']=$buts;

//////////////////////////////////////////////
// Section Pun.
$SomPun = array();
$IPun=0;
$resultPun = executerRequeteJson($conn,
"SELECT TableEvenement0.*, TableJoueur.NomJoueur, TableEquipe.nom_equipe 
 FROM TableEvenement0
 LEFT JOIN TableJoueur
 	ON (TableJoueur.joueur_id=TableEvenement0.joueur_event_ref)
 LEFT JOIN TableEquipe
 	ON (TableEquipe.equipe_id=TableEvenement0.equipe_event_id)

	WHERE match_event_id = '{$matchIDSql}' AND code = 4 ORDER BY chrono"),
	'erreur_sql_punitions',
	'Erreur lors du chargement des punitions.'
);

$punitions=array();

while($rangeePun=mysqli_fetch_array($resultPun))
	{
		$unePunition=array();
		$unePunition['equipe']=$rangeePun['nom_equipe'];
		$unePunition['equipeId']=$rangeePun['equipe_event_id'];
		$unePunition['chrono']=$rangeePun['chrono'];
		$unePunition['motifId']=$rangeePun['souscode'];
	

				switch($rangeePun['souscode'])
				{
					case 1: $motif='Accrocher';
					break;
					case 2: $motif='Cingler';
					break;
					case 3: $motif='Conduite anti-sportive';
					break;
					case 4: $motif='Donner de la bande';
					break;
					case 5: $motif='Obstruction';
					break;
					case 6: $motif='Retarder le match';
					break;
					case 7: $motif='Retenu';
					break;
					case 8: $motif='Rudesse';
					break;
					case 9: $motif='S\'être battu';
					break;
					case 10: $motif='Trébucher';
					break;
					case 11: $motif='Assaut';
					break;
										case 12: $motif='Bâton élevé';
					break;
										case 13: $motif='Coup de coude';
					break;
										case 14: $motif='Cracher';
					break;
										case 15: $motif='Darder';
					break;
										case 16: $motif='Donner du genou';
					break;
										case 17: $motif='Double-échec';
					break;
										case 18: $motif='Instigateur';
					break;
										case 19: $motif='Mise en échec';
					break;
										case 20: $motif='Pousser un joueur';
					break;
										case 21: $motif='Six pouces';
					break;
										case 22: $motif='Tentative de blessure';
					break;
										case 23: $motif='Alignement en retard';
					break;
										case 24: $motif='Balle extérieure';
					break;
										case 25: $motif='Bâton brisé';
					break;
										case 26: $motif='Bâton haut (sans contact)';
					break;
										case 27: $motif='Changement illégal';
					break;
										case 28: $motif='Chute obstrusive';
					break;
										case 29: $motif='Geler la balle';
					break;
										case 30: $motif='Lancer le bâton';
					break;
										case 31: $motif='Pied sur la balle';
					break;
										case 32: $motif='Quitter banc des punitions';
					break;
										case 33: $motif='Pénalité majeure';
					break;
										case 34: $motif='Grossière inconduite';
					break;
										case 35: $motif='Pénalité de match';
					break;
										case 36: $motif='Mauvaise conduite';
					break;
										case 37: $motif='Inconduite de partie';
					break;
					default: $motif='Inconnu';
					break;
									}
				$unePunition['joueur']=$rangeePun['NomJoueur'];
				$unePunition['joueurId']=$rangeePun['joueur_event_ref'];
				$unePunition['motif']=$motif;

				array_push($punitions, $unePunition);
	}
$Sommaire['punitions']=$punitions;

////////////////////////////////////////////


$resultPeriode = executerRequeteJson($conn, "SELECT * FROM TableEvenement0 WHERE match_event_id = '{$matchIDSql}' AND code = 11 ORDER BY souscode ASC", 'erreur_sql_periodes', 'Erreur lors du chargement des periodes.');


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

$Sommaire['periodes']=$periode;



$rFus = executerRequeteJson($conn, "SELECT TableEvenement0.*, Video.*, TableJoueur.*,TableEquipe.* FROM TableEvenement0
										JOIN TableJoueur
											ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id)
										JOIN TableEquipe
											ON (TableEvenement0.equipe_event_id=TableEquipe.equipe_id)
										LEFT JOIN
											Video ON (Video.reference = TableEvenement0.event_id)
										WHERE match_event_id = '{$matchIDSql}' AND code = 2 ORDER BY TableEvenement0.chrono"),
	'erreur_sql_fusillade',
	'Erreur lors du chargement de la fusillade.'
);

$fusillade =Array();
	$IF=0;

$bufEvent=0;
while($rangFus=mysqli_fetch_array($rFus))
	{
		$uneFus=array();
		if($rangFus['event_id']!=$bufEvent){

		if($rangFus['souscode']==1)
		{
			$uneFus['nom']=$rangFus['NomJoueur'];
			$uneFus['equipe']=$rangFus['nom_equipe'];
			$uneFus['but']=true;
		}
		if($rangFus['souscode']==5)
		{
			$uneFus['nom']=$rangFus['NomJoueur'];
			$uneFus['equipe']=$rangFus['nom_equipe'];
			$uneFus['but']=false;
		}



			//if(!is_null($rangeeEv['videoId'])){
			$uneFus['video']=array();
			//}
			array_push($fusillade,$uneFus);
			
			$bufEvent= $rangFus['event_id'];
		}

		$unVideo=array();
		if(!is_null($rangFus['videoId'])){
		$unVideo['fic']=$rangFus['nomFichier'];
		$unVideo['cam']=$rangFus['camId'];
		$unVideo['eval']=$rangFus['eval'];
		$unVideo['nbVues']=$rangFus['nbVues'];
		$unVideo['etat']=$rangFus['etat'];
		$unVideo['videoId']=$rangFus['videoId'];
		$unVideo['emplacement']=$rangFus['emplacement'];
		$unVideo['thumbnail']=$rangFus['nomThumbnail'];

		array_push($uneFus['video'],$unVideo);}
		end($fusillade);
		$key = key($fusillade);
		$fusillade[$key]=$uneFus;
		reset($fusillade);
		




	}


//}
	
//echo json_encode($Sommaire);
$Sommaire['Fusillade']=$fusillade;
echo json_encode($Sommaire);	


	
//$sommaire= json_decode($JSONstring);

foreach($Sommaire['buts'] as $buts )
{
	if(count($buts['video'])>0)
	{
		for($a=0; $a<count($buts['video']);$a++)
		{
		$reqIns = "UPDATE Video SET tagPrincipal='{$buts['marqueurId']}' WHERE videoId='{$buts['video'][$a]['videoId']}'";
		if(!mysqli_query($conn, $reqIns))
		{
			error_log('[getMatchEnCours] erreur SQL tag video: ' . mysqli_error($conn));
		}
		}
	}
}
	//mysqli_close($conn);
//include('../scriptsphp/vidsInfos.php');

?>
