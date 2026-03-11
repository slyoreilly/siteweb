<?php
require '../scriptsphp/defenvvar.php';

session_start();


//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
$params = array();
error_log("preEnregistre: ".$_POST['videos']);
$params =json_decode($_POST['videos'],true);
$heure = $_POST['heure'];
$emplacementTmp =  parse_url($_POST['emplacement']);
$emplacementHost = is_array($emplacementTmp) && isset($emplacementTmp['host']) ? $emplacementTmp['host'] : '';
$emplacementPath = is_array($emplacementTmp) && isset($emplacementTmp['path']) ? $emplacementTmp['path'] : '';
$emplacement = $emplacementHost.$emplacementPath;
$avanceServeur=time()*1000-$heure;

	
$syncOK=array();	
for($a=0;$a<count($params);$a++)
{	
$camID = $params[$a]['video']['camID'];
$exploded =explode('/',$params[$a]['video']['nomFic']); 
$nomFic=array_pop($exploded);
$rSServ=$params[$a]['video']['chrono']+$avanceServeur;

$cvDemande = json_decode(stripslashes($params[$a]['video']['cv']),true);
$referenceDemande = 0;
$typeDemande = null;
if (is_array($cvDemande) && isset($cvDemande['reference'])) {
	$referenceDemande = intval($cvDemande['reference']);
}
if (is_array($cvDemande) && isset($cvDemande['type'])) {
	$typeDemande = strval($cvDemande['type']);
}

$demandeAjoutVideo = null;
$qDemande = "SELECT * FROM DemandeAjoutVideo
            WHERE progression=2
                AND cameraId='{$camID}'";
if($referenceDemande>0){
	$qDemande .= " AND eventId='" . $referenceDemande . "'";
}
$qDemande .= " ORDER BY demandeId ASC
            LIMIT 0,1";
$retDemande = mysqli_query($conn,$qDemande);
if($retDemande && mysqli_num_rows($retDemande)>0){
    $demandeAjoutVideo = mysqli_fetch_array($retDemande);
}

$nomMatchVideo = $params[$a]['video']['nomMatch'];
if($demandeAjoutVideo!=null){
	$eventIdDemande = intval($demandeAjoutVideo['eventId']);
	$typeEvenementDemande = $typeDemande;
	if($typeEvenementDemande===null || $typeEvenementDemande===''){
		$typeEvenementDemande = strval($demandeAjoutVideo['typeEvenement']);
	}
	if($eventIdDemande>0){
		$qMatchDemande = '';
		if($typeEvenementDemande==='5'){
			$qMatchDemande = "SELECT TableMatch.match_id
						FROM Clips
						INNER JOIN TableMatch ON (Clips.matchId = TableMatch.matchIdRef)
						WHERE Clips.clipId='".$eventIdDemande."'
						LIMIT 0,1";
		}else{
			$qMatchDemande = "SELECT TableMatch.match_id
						FROM TableEvenement0
						INNER JOIN TableMatch ON (TableEvenement0.match_event_id = TableMatch.matchIdRef)
						WHERE TableEvenement0.event_id='".$eventIdDemande."'
						LIMIT 0,1";
		}

		$retMatchDemande = mysqli_query($conn, $qMatchDemande);
		if($retMatchDemande && mysqli_num_rows($retMatchDemande)>0){
			$rangeeMatchDemande = mysqli_fetch_array($retMatchDemande);
			if(isset($rangeeMatchDemande['match_id']) && $rangeeMatchDemande['match_id']!==''){
				$nomMatchVideo = $rangeeMatchDemande['match_id'];
			}
		}
	}
}
$nomMatchVideoSql = mysqli_real_escape_string($conn, $nomMatchVideo);
	
if(!empty($nomFic))
	{
		$monObj=array();   /// 11/12/2017 j'ai remplacé matchIdRef par match_id.
	$qSel="SELECT * FROM Video 
			JOIN TableMatch
				ON (match_id = nomMatch)  
		WHERE nomMatch='{$nomMatchVideoSql}' AND camId='{$camID}' AND nomFichier Like '{$nomFic}%'";	
	$retSel=mysqli_query($conn,$qSel) or die("Erreur: "+$qSel+"\n"+mysqli_error($conn));
		if(mysqli_num_rows($retSel)>0){
			while ($rangSel = mysqli_fetch_array($retSel))
			{
			// N'entre pas dans cette boucle si non-enregistré dans table Video.
			
				$type=1000;
				$reference=1000;
		
				$emplacement=$rangSel['emplacement'];
				if(is_null($emplacement)){
					$emplacement="www.syncstats.com";
				}
				$cv= json_decode(stripslashes($params[$a]['video']['cv']),true);
			
				if(isset($cv['type'])){
					$type=$cv['type'];
				}	
				if(isset($cv['reference'])){
					$reference=$cv['reference'];
				}
			}
			$esSimple=null;
			if(isset($params[$a]['video']['esSimple'])){
				$esSimple=$params[$a]['video']['esSimple'];
			}
			if(file_exists('http://'.$emplacement.'/lookatthis/'.$nomFic))
			{
				if($esSimple!=12){
				$monObj['etat']='insert';					
				}else{
				$monObj['etat']='deja';					
				}
			}
			else
			{
				$monObj['etat']='insert';
			}
			$monObj['nomFic']=$nomFic;
			$monObj['chrono']=$rSServ;
		
			array_push($syncOK, $monObj);
				
		}
	
		
			else {
				$type=0;
				$reference=0;
				
					$cv= json_decode(stripslashes($params[$a]['video']['cv']),true);
			
				if(isset($cv['type'])){
					$type=$cv['type'];
				}	
				if(isset($cv['reference'])){
					$reference=$cv['reference'];
				}		
				$type=0;
		$chronoInsertion = $rSServ;
		if($demandeAjoutVideo!=null){
			$chronoInsertion = intval($demandeAjoutVideo['chronoDemande']);
		}
		$query = "INSERT INTO Video (nomFichier,nomMatch,chrono,camId,type,reference,emplacement) ".
		"VALUES ('{$nomFic}','{$nomMatchVideoSql}','{$chronoInsertion}','{$camID}','{$type}' ,'{$reference}','{$emplacement}')";
		mysqli_query($conn,$query) or die("Erreur: ".$query."\n".mysqli_error($conn));
		
		$monObj['nomFic']=$nomFic;
		$monObj['etat']='insert';
		$monObj['chrono']=$chronoInsertion;
		
		array_push($syncOK, $monObj);
			
				
				
				
			}

		if($demandeAjoutVideo!=null){
			$qMajDemande = "UPDATE DemandeAjoutVideo
							SET progression=3, videoNomFichier='{$nomFic}', updatedAt=NOW()
							WHERE demandeId='".intval($demandeAjoutVideo['demandeId'])."'";
			mysqli_query($conn,$qMajDemande);

			$valeurControle = round((intval($demandeAjoutVideo['chronoVideo']) - intval($demandeAjoutVideo['chronoDemande'])) / 1000);
			$qInsControle = "INSERT INTO Controle (`telId`, `arg0`, `arg1`, `arg2`, `valeur`, `cleValeur`, `etatSync`)
							VALUES ('{$camID}','videos','recut','{$nomFic}','{$valeurControle}',NULL, 3)";
			mysqli_query($conn,$qInsControle);
		}

		}



	

}
$ret = json_encode($syncOK);
//error_log("retour preEnregistre: ".$ret)	;
	echo $ret;
	
	if($ret==False)
	{echo "erreur, count(syncOK:): ".count($syncOK)."- count($params): ".count($params);}
	//mysqli_close($conn);
?>

