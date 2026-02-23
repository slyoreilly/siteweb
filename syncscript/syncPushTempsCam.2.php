<?php
require '../scriptsphp/defenvvar.php';

$username = $_POST['username'];
$recentSync = $_POST['recentSync'];
if (isset($_POST['nomMatch'])) {$dernierMatch = $_POST['nomMatch'];
}
$arena = 0;
$addArenaDependance='';
if (isset( $_POST['arenaId'])) {$arena = $_POST['arenaId'];
	$addArenaDependance=" AND TableMatch.arenaId='{$arena}' ";
}

$heure = $_POST['heure'];
$maxSec = 0;
if (isset( $_POST['timeout'])) {$maxSec = $_POST['timeout'];
}
$avanceServeur = time() * 1000 - $heure;
$rSServ = $recentSync + $avanceServeur;
//rrs2: Plus recent sync du telephone corrigé àa l'heure serveur et boosté de 1 s.


unset($chronoRetour);
unset($resultChrono);
unset($rangeeChrono);

$rrs2 = $rSServ+1000;
$cpt=0;


function traiteDemandesAjoutVideo($conn, $rrs2) {
    $qDemandes = "SELECT demandeId, eventId, typeEvenement, chronoDemande, cameraId
"
        . "FROM DemandeAjoutVideo WHERE progression=1 ORDER BY demandeId ASC LIMIT 0,50";

    $resDemandes = mysqli_query($conn, $qDemandes);
    if (!$resDemandes) {
        error_log("syncPushTempsCam.2 - DemandeAjoutVideo: " . mysqli_error($conn));
        return;
    }

    while ($rangeeDemande = mysqli_fetch_array($resDemandes)) {
        $eventId = intval($rangeeDemande['eventId']);
        $typeEvenement = intval($rangeeDemande['typeEvenement']);
        $demandeId = intval($rangeeDemande['demandeId']);

        if ($typeEvenement <= 0) {
            $typeEvenement = 5;
        }

        $qSelEvent = "SELECT TableEvenement0.match_event_id, TableEvenement0.equipe_event_id, TableMatch.arenaId, TableMatch.match_id, TableMatch.eq_dom, TableMatch.eq_vis
"
            . "FROM TableEvenement0
"
            . "INNER JOIN TableMatch ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)
"
            . "WHERE TableEvenement0.event_id='{$eventId}' LIMIT 0,1";
        $resEvent = mysqli_query($conn, $qSelEvent);
        if (!$resEvent || mysqli_num_rows($resEvent) <= 0) {
            continue;
        }

        $rangeeEvent = mysqli_fetch_array($resEvent);
        $matchEventIdSysteme = $rangeeEvent['match_event_id'];
        $equipeEventId = intval($rangeeEvent['equipe_event_id']);

        $qSelMatchSysteme = "SELECT matchIdRef FROM TableMatch
"
            . "WHERE ligueRef='5'
"
            . "AND arenaId='" . intval($rangeeEvent['arenaId']) . "'
"
            . "ORDER BY date DESC LIMIT 0,1";
        $resMatchSysteme = mysqli_query($conn, $qSelMatchSysteme);
        if ($resMatchSysteme && mysqli_num_rows($resMatchSysteme) > 0) {
            $rMS = mysqli_fetch_array($resMatchSysteme);
            $matchEventIdSysteme = $rMS['matchIdRef'];
        }

        $chronoVideo = intval($rrs2);

        $qInsEvt = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id, joueur_event_ref, chrono, souscode, code) VALUES ("
            . "'{$matchEventIdSysteme}', '{$equipeEventId}', '{$eventId}', '{$chronoVideo}', 0, '{$typeEvenement}')";
        $okInsEvt = mysqli_query($conn, $qInsEvt);

        if ($okInsEvt) {
            $qMajDemande = "UPDATE DemandeAjoutVideo SET progression=2, chronoVideo='{$chronoVideo}', updatedAt=NOW() WHERE demandeId='{$demandeId}'";
            mysqli_query($conn, $qMajDemande);
        }
    }
}



////////////////////  Sortir tous les matchs récents de l'utilisateur.
//
///					Pour les évènements, voir plus bas.

$vecLigues = array();

traiteDemandesAjoutVideo($conn, $rrs2);

do {


$chronoRetour = array();
$matchRetour = array();



$qMatch="SELECT e.event_id, e.chrono,e.matchIdRef,e.eq_dom,e.eq_vis,e.ligueRef,e.match_id,
e.arenaId,e.date, '0' as 'type', e.scoringEnd, e.code as 'code', '0' as 'souscode' , 
L1.LeagueId, L1.CamActionTemplateId, L1.defaultDuration, L1.ActivationFlags, L1.ActivationArgs,EventType.Code as 'CATcode' FROM(

    SELECT TableEvenement0.event_id, MAX(chrono) as chrono,MAX(TableMatch.matchIdRef) as matchIdRef,MAX(TableMatch.eq_dom) as eq_dom, MAX(TableMatch.eq_vis) as eq_vis,
    MAX(TableMatch.ligueRef) as ligueRef,MAX(TableMatch.match_id) as match_id,
    MAX(TableMatch.arenaId) as arenaId,MAX(TableMatch.date) as date, '0' as 'type', TableEvenement0.equipe_event_id as scoringEnd , TableEvenement0.code,TableEvenement0.souscode
    
    FROM TableEvenement0 
	    
    INNER JOIN TableMatch 
		ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)
	INNER JOIN AbonnementLigue
		ON (TableMatch.ligueRef=AbonnementLigue.ligueid)
	INNER JOIN TableUser
		ON (AbonnementLigue.userid=TableUser.noCompte)
	

	WHERE TableEvenement0.chrono>$rrs2 
			" . $addArenaDependance ."
			AND TableUser.username='{$username}'
GROUP BY event_id
) e INNER JOIN EventType
 on (EventType.Code=e.code)    
 INNER JOIN  CamActionTemplate L1 on ( L1.EventTypeId = (
		SELECT EventTypeId FROM CamActionTemplate 
        WHERE LeagueId = e.ligueRef OR LeagueId=0 
        ORDER BY LeagueId DESC LIMIT 1 
	 
)   AND (
    L1.LeagueId = (
		SELECT LeagueId FROM CamActionTemplate 
        WHERE (LeagueId = e.ligueRef OR LeagueId=0) and EventTypeId=EventType.EventTypeId
        ORDER BY LeagueId DESC LIMIT 1 
))

)

UNION


(
SELECT Clips.clipId, chrono,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,
TableMatch.arenaId,TableMatch.date, '5' as 'type', Clips.scoringEnd, '5' as 'code', '0' as 'souscode' , 
L2.LeagueId,  L2.CamActionTemplateId, L2.defaultDuration, L2.ActivationFlags,L2.ActivationArgs, '5' as 'CATcode'
FROM Clips 
	INNER JOIN TableMatch
		ON (Clips.matchId=TableMatch.matchIdRef) 
	INNER JOIN AbonnementLigue
		ON (TableMatch.ligueRef=AbonnementLigue.ligueid) 
	INNER JOIN TableUser
		ON (AbonnementLigue.userid=TableUser.noCompte) 
    INNER JOIN EventType 
        on (EventType.Code='5')
        INNER JOIN  CamActionTemplate L2 on ( L2.EventTypeId = (
            SELECT CamActionTemplate.EventTypeId FROM CamActionTemplate 
            WHERE CamActionTemplate.LeagueId = TableMatch.ligueRef OR CamActionTemplate.LeagueId=0 
            ORDER BY CamActionTemplate.LeagueId DESC LIMIT 1 
         
    )  AND (
        L2.LeagueId = (
            SELECT CamActionTemplate.LeagueId FROM CamActionTemplate 
            WHERE (CamActionTemplate.LeagueId = TableMatch.ligueRef OR LeagueId=0)   and EventTypeId=EventType.EventTypeId
            ORDER BY LeagueId DESC LIMIT 1 
        )
    )
    ) 
    
	WHERE Clips.chrono>$rrs2 
	" . $addArenaDependance ." AND TableUser.username='{$username}') 


ORDER BY  matchIdRef, chrono";

/*

$qMatch="SELECT e.event_id, e.chrono,e.matchIdRef,e.eq_dom,e.eq_vis,e.ligueRef,e.match_id,
e.arenaId,e.date, '0' as 'type', e.scoringEnd, e.code as 'code', '0' as 'souscode' , L1.LeagueId,L1.defaultDuration, L1.ActivationFlags,L1.ActivationArgs,EventType.Code as 'CATcode' FROM(

SELECT TableEvenement0.event_id, MAX(chrono) as chrono,MAX(TableMatch.matchIdRef) as matchIdRef,MAX(TableMatch.eq_dom) as eq_dom, MAX(TableMatch.eq_vis) as eq_vis,MAX(TableMatch.ligueRef) as ligueRef,MAX(TableMatch.match_id) as match_id,
MAX(TableMatch.arenaId) as arenaId,MAX(TableMatch.date) as date, '0' as 'type', TableEvenement0.equipe_event_id as scoringEnd , TableEvenement0.code,TableEvenement0.souscode
FROM TableEvenement0 
	INNER JOIN TableMatch 
		ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)
	INNER JOIN AbonnementLigue
		ON (TableMatch.ligueRef=AbonnementLigue.ligueid)
	INNER JOIN TableUser
		ON (AbonnementLigue.userid=TableUser.noCompte)
	

	WHERE TableEvenement0.chrono>$rrs2 
			" . $addArenaDependance ."
			AND TableUser.username='{$username}'
GROUP BY event_id
) e INNER JOIN EventType
 on (EventType.Code=e.code)    
INNER JOIN  (
		SELECT CamActionTemplate.EventTypeId, CamActionTemplate.defaultDuration, CamActionTemplate.LeagueId, CamActionTemplate.defaultDelay, CamActionTemplate.CamActionTemplateId,CamActionTemplate.ActivationArgs, CamActionTemplate.ActivationFlags FROM CamActionTemplate

	) as L1 on (
		if(
			(EventType.EventTypeId=L1.EventTypeId AND (L1.LeagueId=e.ligueRef)
		),'TRUE', (EventType.EventTypeId=L1.EventTypeId AND L1.LeagueId=0)
	))  
UNION
(
SELECT Clips.clipId, chrono,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,
TableMatch.arenaId,TableMatch.date, '5' as 'type', Clips.scoringEnd, '5' as 'code', '0' as 'souscode' , L2.LeagueId,L2.defaultDuration, L2.ActivationFlags,L2.ActivationArgs,L2.Code as 'CATcode'
FROM Clips 
	INNER JOIN TableMatch
		ON (Clips.matchId=TableMatch.matchIdRef)
	INNER JOIN AbonnementLigue
		ON (TableMatch.ligueRef=AbonnementLigue.ligueid)
	INNER JOIN TableUser
		ON (AbonnementLigue.userid=TableUser.noCompte)
			INNER JOIN (
		SELECT EventType.EventTypeId,CamActionTemplate.defaultDuration,EventType.Code, CamActionTemplate.LeagueId, CamActionTemplate.defaultDelay, CamActionTemplate.CamActionTemplateId,CamActionTemplate.ActivationArgs, CamActionTemplate.ActivationFlags FROM EventType
		LEFT JOIN CamActionTemplate
			ON (EventType.EventTypeId=CamActionTemplate.EventTypeId)
	) as L2 on ((L2.LeagueId=TableMatch.ligueRef OR L2.LeagueId=0) AND L2.Code='5' )
	WHERE Clips.chrono>$rrs2 
	" . $addArenaDependance ." AND TableUser.username='{$username}')  


ORDER BY  matchIdRef, chrono";*/
/*
$qMatch_old = "SELECT * FROM (
    SELECT TableEvenement0.event_id, chrono,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,
		TableMatch.arenaId,TableMatch.date, '0' as 'type', TableEvenement0.equipe_event_Id as scoringEnd , TableEvenement0.code,TableEvenement0.souscode, L1.LeagueId,L1.defaultDuration,L1.ActivationFlags,L1.ActivationArgs, L1.Code as 'CATcode'
	FROM TableEvenement0 
			INNER JOIN TableMatch
				ON (TableEvenement0.match_event_id=TableMatch.matchIdRef)
			INNER JOIN AbonnementLigue
				ON (TableMatch.ligueRef=AbonnementLigue.ligueid)
			INNER JOIN TableUser
				ON (AbonnementLigue.userid=TableUser.noCompte)
			INNER JOIN (
				SELECT EventType.EventTypeId,CamActionTemplate.defaultDuration,EventType.Code, CamActionTemplate.LeagueId, CamActionTemplate.defaultDelay, CamActionTemplate.CamActionTemplateId,CamActionTemplate.ActivationArgs, CamActionTemplate.ActivationFlags FROM EventType
				INNER JOIN CamActionTemplate
					ON (EventType.EventTypeId=CamActionTemplate.EventTypeId)
			) as L1 on ((L1.LeagueId=TableMatch.ligueRef OR L1.LeagueId=0) AND L1.Code=TableEvenement0.code)

			WHERE TableEvenement0.chrono>$rrs2 
					AND TableMatch.arenaId='{$arena}' 
					AND TableUser.username='{$username}'
					UNION
	SELECT Clips.clipId, chrono,TableMatch.matchIdRef,TableMatch.eq_dom,TableMatch.eq_vis,TableMatch.ligueRef,TableMatch.match_id,
		TableMatch.arenaId,TableMatch.date, '5' as 'type', Clips.scoringEnd, '5' as 'code', '0' as 'souscode' , L2.LeagueId,L2.defaultDuration, L2.ActivationFlags,L2.ActivationArgs, L2.Code as 'CATcode'
	FROM Clips 
			INNER JOIN TableMatch
				ON (Clips.matchId=TableMatch.matchIdRef)
			INNER JOIN AbonnementLigue
				ON (TableMatch.ligueRef=AbonnementLigue.ligueid)
			INNER JOIN TableUser
				ON (AbonnementLigue.userid=TableUser.noCompte)
    				INNER JOIN (
				SELECT EventType.EventTypeId,CamActionTemplate.defaultDuration,EventType.Code, CamActionTemplate.LeagueId, CamActionTemplate.defaultDelay, CamActionTemplate.CamActionTemplateId,CamActionTemplate.ActivationArgs, CamActionTemplate.ActivationFlags FROM EventType
				INNER JOIN CamActionTemplate
					ON (EventType.EventTypeId=CamActionTemplate.EventTypeId)
			) as L2 on ((L2.LeagueId=TableMatch.ligueRef OR L2.LeagueId=0) AND L2.Code='5' )
			WHERE Clips.chrono>$rrs2 
				AND TableMatch.arenaId='{$arena}' AND TableUser.username='{$username}') t 


ORDER BY  matchIdRef, chrono,  t.LeagueId DESC ";*/
  

					
						mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
					$resultMatchs = mysqli_query($conn,$qMatch) or die(mysqli_error($conn) . $qMatch);					



					
										
$IM = 0;
$IC = 0;
$IE =0;// conpteur équipe
$IM2 =0;// compteur match
$trouve = false;
$matchs = array();

$matchPeriode = array();
			$video=array();

while ($rangeeMatch=mysqli_fetch_array($resultMatchs)){// && !$trouve) {

				$mGameIndex = 0;
					$trouveMatch=false;
				foreach($matchPeriode as $key=>$unMatch){
					if($unMatch['match_id']==$rangeeMatch['match_id']){
						$mGameIndex=$key;
						$trouveMatch=true;
					}
				}
				if(!$trouveMatch){
					$nouveauMatch=array();
					$nouveauMatch['match_id']=$rangeeMatch['match_id'];
					$nouveauMatch['arenaId']=$rangeeMatch['arenaId'];
					$nouveauMatch['ligueId']=$rangeeMatch['ligueRef'];
					$nouveauMatch['eqDom']=$rangeeMatch['eq_dom'];
					$nouveauMatch['eqVis']=$rangeeMatch['eq_vis'];
					$nouveauMatch['nom']=$rangeeMatch['matchIdRef'];
					$nouveauMatch['date']=$rangeeMatch['date'];
					$nouveauMatch['periodes']= array();
					$nouveauMatch['videos']= array();
					$nouveauMatch['abons']= array();
					$nouveauMatch['arena']="";
					$mGameIndex=array_push($matchPeriode,$nouveauMatch)-1;
				}




			switch($rangeeMatch['type']){

				case 0:
					if($rangeeMatch['code']!=11){
						$mVideo= array();
						$mVideo['match_id'] = $rangeeMatch['match_id'];
						$mVideo['reference'] = $rangeeMatch['event_id'];
						$mVideo['type'] = $rangeeMatch['code'];
						$mVideo['chrono'] = $rangeeMatch['chrono'];
						$mVideo['ligueId'] = $rangeeMatch['ligueRef'];
						$mVideo['equipe'] = $rangeeMatch['scoringEnd'];
						$mVideo['CATLeagueId']=$rangeeMatch['LeagueId'];
						$mVideo['ActivationFlags']=$rangeeMatch['ActivationFlags'];
						$mVideo['ActivationArgs']=$rangeeMatch['ActivationArgs'];
						$mVideo['defaultDuration']=$rangeeMatch['defaultDuration'];

						array_push($matchPeriode[$mGameIndex]['videos'],$mVideo);


					}else{
						$unePeriode = Array();
						$unePeriode['noPer'] = $rangeeMatch['souscode'];
						$unePeriode['chronoDeb'] = $rangeeMatch['chrono'];
						array_push($matchPeriode[$mGameIndex]['periodes'],$unePeriode);

					}



				break;
				

				case 5:			
				$mVideo= array();
				$mVideo['match_id'] = $rangeeMatch['match_id'];
				$mVideo['reference'] = $rangeeMatch['event_id'];
				$mVideo['type'] = 5;
				$mVideo['chrono'] = $rangeeMatch['chrono'];
				$mVideo['ligueId'] = $rangeeMatch['ligueRef'];
				$mVideo['equipe'] = $rangeeMatch['scoringEnd'];
				array_push($matchPeriode[$mGameIndex]['videos'],$mVideo);

				break;
			}


		
//	}
	$IM++;

}


foreach($matchPeriode as &$unMatch){

	if(!is_null($unMatch['arenaId'])){
		error_log("dans foreach ".$unMatch['arenaId'])	;
		$qSelArena="SELECT * From TableArena
			WHERE arenaId={$unMatch['arenaId']}";
		$resArena = mysqli_query($conn,$qSelArena) or die(mysqli_error($conn) . $qSelArena);
		while ($rangeeArena = mysqli_fetch_array($resArena)){
			$nomArena =$rangeeArena['nomArena'];					
			$nomGlace = $rangeeArena['nomGlace'];	
		}
		
		$abons = array();
	//	$Ia=0;
		$qSelAbons="SELECT telId, abonAppareilMatch.role  From abonAppareilMatch
			WHERE matchId={$unMatch['match_id']}";
		$resAbons = mysqli_query($conn,$qSelAbons) or die(mysqli_error($conn) . $qSelAbons);
		while ($rangeeAbons = mysqli_fetch_array($resAbons)){
			array_push($abons, array("role"=>$rangeeAbons['role'], "telId"=>$rangeeAbons['telId']));
	//		$abons[$Ia]=array();
	//		$abons[$Ia]['role'] =$rangeeAbons['role'];					
	//		$abons[$Ia]['telId'] =$telId = $rangeeAbons['telId'];	
	//		$Ia++;
		}



		$unMatch['arena']=$nomArena." / ".$nomGlace;	
		$unMatch['abons']=$abons;
		//error_log("dans  arena ".$unMatch['arena']." et abons: ".$unMatch['abons'])	;
	}
}
if($maxSec>0){
$qLigues = "SELECT * FROM Ligue
			INNER JOIN AbonnementLigue
				ON (Ligue.ID_Ligue=AbonnementLigue.ligueid)
			INNER JOIN TableUser
				ON (AbonnementLigue.userid=TableUser.noCompte)
			WHERE  TableUser.username='{$username}'";
					
				
					
						mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
					$resultLigues = mysqli_query($conn, $qLigues) or die(mysqli_error($conn) . $qLigues);							

					
					$IL2=0;
$IL2 = 0;

while ($r = mysqli_fetch_array($resultLigues)) {

    $vecLigues[] = $r;

    $vecLigues[$IL2]['ligueId']   = $r['ID_Ligue'];
    $vecLigues[$IL2]['nomLigue']  = $r['Nom_Ligue'];

    // Sport par défaut = Hockey
    $vecLigues[$IL2]['sportId'] = 1;

    $cleValeur = json_decode($r['cleValeur']);

    // Vérification JSON valide
    if (json_last_error() === JSON_ERROR_NONE && is_object($cleValeur)) {

        if (isset($cleValeur->parametres) && 
            is_object($cleValeur->parametres) && 
            isset($cleValeur->parametres->sport)) {

            $sport = strtolower(trim($cleValeur->parametres->sport));

            switch ($sport) {
                case "baseball":
                    $vecLigues[$IL2]['sportId'] = 2;
                    break;

                case "dek":
                    $vecLigues[$IL2]['sportId'] = 3;
                    break;

                case "basketball":
                    $vecLigues[$IL2]['sportId'] = 4;
                    break;

                case "soccer":
                    $vecLigues[$IL2]['sportId'] = 5;
                    break;

                default:
                    // Sport inconnu → garder valeur par défaut
                    break;
            }

        } else {
            // Optionnel : journaliser les ligues sans sport
            // error_log("Ligue sans sport défini - ID: " . $r['ID_Ligue']);
        }

    } else {
        // Optionnel : JSON invalide
        // error_log("JSON invalide pour ligue ID: " . $r['ID_Ligue']);
    }

    $IL2++;
}
}

$repSite = array();

	$repSite['match']=array();
	$repSite['chronoBut']=array();
	$repSite['video']=array();


$repSite['heure'] = time();
$repSite['matchPeriode'] = $matchPeriode;
$repSite['ligues'] =$vecLigues;

//$repSite['ligues'] = $Ligues;
//$repSite['equipes'] = $Equipes;
$repSite['matchs'] = $matchs;
//$repSite['info']=$resultMatch;
if($IM==0){
	$mSleep = sleep(5);
	flush();
	$cpt = $cpt+5000;

	
}

//$repSite['IC'] =$IC;
//$repSite['cpt'] =$cpt;
//echo " -!- ". $maxSec." -!- ";

$comp =$maxSec-30000;
}
 while (($IM==0)&&($cpt<$comp));


//echo json_encode($Sommaire);
echo json_encode($repSite);
//mysqli_close($conn);
//	header("HTTP/1.1 200 OK");

?>	