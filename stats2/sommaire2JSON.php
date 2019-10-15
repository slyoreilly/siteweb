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
  	WHERE (nomMatch = '{$matchID}'  OR nomMatch = '{$matchPourVideos}') AND Video.type=5 ORDER BY clipId, angleOk DESC";
$resultVids = mysqli_query($conn, $qVids)
or die(mysqli_error($conn).$qVids);  	

$bufEvent=0;
$clips=array();
while($rangeeVids=mysqli_fetch_array($resultVids))
	{
		if($rangeeVids['reference']!=$bufEvent){
			$unClip=array();
			$unClip['chrono']=$rangeeVids['chrono'];
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
        LEFT JOIN
    (SELECT 
        TableEvenement0.joueur_event_ref,
            TableEvenement0.chrono,
            TableEvenement0.event_id,
            TableJoueur.NomJoueur,
            TableJoueur.NumeroJoueur,
            Passeur2.NomJoueur AS P2nomJoueur,
            Passeur2.NumeroJoueur AS P2noJoueur,
            Passeur2.joueur_event_ref AS P2jId
    FROM
        TableEvenement0
    INNER JOIN TableJoueur ON (TableEvenement0.joueur_event_ref = TableJoueur.joueur_id)
    INNER JOIN TableMatch ON (TableMatch.matchIdRef = TableEvenement0.match_event_id)
    LEFT JOIN (SELECT 
        joueur_event_ref,
            chrono,
            event_id,
            TableJoueur.NomJoueur,
            TableJoueur.NumeroJoueur
    FROM
        TableEvenement0
    INNER JOIN TableJoueur ON (TableEvenement0.joueur_event_ref = TableJoueur.joueur_id)
    INNER JOIN TableMatch ON (TableMatch.matchIdRef = TableEvenement0.match_event_id)
    WHERE
        TableEvenement0.code = 1
            AND match_id = '{$matchPourVideos}'
    ORDER BY TableEvenement0.event_id DESC) AS Passeur2 ON (Passeur2.chrono = TableEvenement0.chrono)
        AND (Passeur2.event_id <> TableEvenement0.event_id)
    WHERE
        TableEvenement0.code = 1
            AND match_id = '{$matchPourVideos}'
    GROUP BY chrono) AS Passeur1 ON (Passeur1.chrono = TableEvenement0.chrono)
WHERE
    (match_id = '{$matchPourVideos}')
        AND (TableEvenement0.code = 0)
        AND (Video.type = 0 OR Video.type IS NULL)
ORDER BY TableEvenement0.chrono , angleOk DESC";


$setupMySql = mysqli_query($conn,"SET SQL_BIG_SELECTS=1" ) or die('Cannot complete SETUP BIG SELECTS because: ' . mysqli_error($conn));
$resultEvent = mysqli_query($conn,$qEv) or die(mysqli_error($conn));  	
$buts=array();
$Sommaire['qEv']=$qEv;

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
			$unBut['passeur1Id']=$rangeeEv['passeur1Id'];
			$unBut['passeur1']=$rangeeEv['passeur1'];
			$unBut['noPasseur1']=$rangeeEv['noPasseur1'];
			$unBut['passeur2Id']=$rangeeEv['passeur2Id'];
			$unBut['passeur2']=$rangeeEv['passeur2'];
			$unBut['noPasseur2']=$rangeeEv['noPasseur2'];


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
$resultPun = mysqli_query($conn,
"SELECT TableEvenement0.*, TableJoueur.NomJoueur, TableEquipe.nom_equipe 
 FROM TableEvenement0
 LEFT JOIN TableJoueur
 	ON (TableJoueur.joueur_id=TableEvenement0.joueur_event_ref)
 LEFT JOIN TableEquipe
 	ON (TableEquipe.equipe_id=TableEvenement0.equipe_event_id)

 	WHERE match_event_id = '{$matchID}' AND code = 4 ORDER BY chrono")
or die(mysqli_error($conn));  	

$punitions=array();

while($rangeePun=mysqli_fetch_array($resultPun))
	{
		$unePunition=array();
		$unePunition['equipe']=$rangeePun['nom_equipe'];
		$unePunition['chrono']=$rangeePun['chrono'];
	

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

$Sommaire['periodes']=$periode;



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
		mysqli_query($conn, $reqIns)or die(mysqli_error($conn));
		}
	}
}
	mysqli_close($conn);
//include('../scriptsphp/vidsInfos.php');

?>
