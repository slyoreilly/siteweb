<?php
require '../scriptsphp/defenvvar.php';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];

$arenaId = $_POST['arenaId'];
$usager = $_POST['userId'];
$matchId=null;
if(isset($_POST['matchId'])){
$matchId = $_POST['matchId'];}

////////////////////////////
//
///		A partir d'un telId et d'un username, trouver les appareils et leurs statuts.

$retourCam = mysqli_query($conn, "SELECT StatutCam.*, TableUser.username,abonAppareilSurface.*
						FROM StatutCam
						INNER JOIN TableUser
							ON (StatutCam.userId=TableUser.username)
							INNER JOIN abonAppareilSurface
								ON (StatutCam.telId=abonAppareilSurface.telId)
						 WHERE TableUser.username='{$usager}' 
						 AND (abonAppareilSurface.role>99 OR abonAppareilSurface.role<1 ) 
						 AND abonAppareilSurface.surfaceId='{$arenaId}'") or die(mysqli_error($conn));

$querySel = "SELECT StatutRemote.*, TableUser.username, abonAppareilSurface.*
						FROM StatutRemote
						INNER JOIN TableUser
							ON (StatutRemote.userId=TableUser.username)
							INNER JOIN abonAppareilSurface
								ON (StatutRemote.telId=abonAppareilSurface.telId)
						 WHERE TableUser.username='{$usager}' AND abonAppareilSurface.role<100
						 AND abonAppareilSurface.surfaceId='{$arenaId}'";

$resultSel = mysqli_query($conn, $querySel) or die("Erreur: " + $querySel + "\n" + mysqli_error($conn));
$cams=array();
$cptCams = 0;
while ($rangSel = mysqli_fetch_assoc($retourCam)) {
	$cams[] = $rangSel;
//	$cams[$cptCams]=array();
	$cams[$cptCams]['memoire'] = $rangSel['memoire'];//round(intval($rangSel['memoire']) / 1000000);
		$cams[$cptCams]['batterie']=$rangSel['batterie'];
			$cams[$cptCams]['codeEtat']=$rangSel['codeEtat'];
				$cams[$cptCams]['dernierMaJ']=$rangSel['dernierMaJ'];
	if($matchId!=null&&$matchId!=""){
	$qCheckMatch = "Select role FROM abonAppareilMatch
							WHERE matchId='{$matchId}' AND telId='{$rangSel['telId']}'";
	$rCM = mysqli_query($conn, $qCheckMatch) or die(mysqli_error($conn));

	if (mysqli_num_rows($rCM) > 0) {
		$rCM_vec = mysqli_fetch_row($rCM);
		$cams[$cptCams]['role'] = $rCM_vec[0];
	}
	
	}
	
	if ((time() - strtotime($rangSel['dernierMaJ'])) > 3600 && ($rangSel['codeEtat'] == '10')) {
		$cams[$cptCams]['codeEtat'] = '30';

		if ((time() - strtotime($rangSel['dernierMaJ'])) > 600 && ($rangSel['codeEtat'] == '10')) {
			$cams[$cptCams]['codeEtat'] = '20';
		}
	}
	$qCheckVideo = "Select nomFichier,chrono FROM Video
							WHERE camId='{$rangSel['camId']}' ORDER BY chrono DESC limit 0,20";
$resultVid = mysqli_query($conn, $qCheckVideo) or die("Erreur: " + $qCheckVideo + "\n" + mysqli_error($conn));
$cptVid=0;
$trouve=false;
while ($rangVid = mysqli_fetch_assoc($resultVid)) {
		if($cptVid==0){
		$str1= substr($rangVid['chrono'],0,10);
			$cams[$cptCams]['vidDetect']=date('d/m/Y H:i:s',$str1 );
		}
		
	if(!$trouve){
	
		if(filter_var("http://5.39.81.14/lookatthis/".$rangVid['nomFichier'], FILTER_VALIDATE_URL)){
			$trouve=true;
			$str1= substr($rangVid['chrono'],0,10);
			$cams[$cptCams]['vidLoadeChrono']=date('d/m/Y H:i:s',$str1 );
			$cams[$cptCams]['vidLoade']="<a href='http://5.39.81.14/lookatthis/".$rangVid['nomFichier']."'>Voir</a>";
		}
	}
	$cptVid++;	
	
}	
	
	
	$cptCams++;
}
$remotes = Array();
$cptRemotes = 0;
while ($rangSel = mysqli_fetch_assoc($resultSel)) {
	$remotes[$cptRemotes] = Array();
	$remotes[$cptRemotes]['telId'] = $rangSel['telId'];
	$remotes[$cptRemotes]['codeEtat'] = $rangSel['codeEtat'];
	$remotes[$cptRemotes]['dernierModif'] = $rangSel['dernierModif'];
	$remotes[$cptRemotes]['dernierMaJ'] = $rangSel['dernierMaJ'];
	$remotes[$cptRemotes]['batterie'] = $rangSel['batterie'];
	$remotes[$cptRemotes]['remoteId'] = $rangSel['remoteId'];
	$remotes[$cptRemotes]['role'] = $rangSel['role'];
	//					$tmpRem[]= $rangSel;
	//$remotes[$cptRemotes]=Array();
	//		$remotes[$cptRemotes]=	$tmpRem;
	$remotes[$cptRemotes]['memoire'] = round($rangSel['memoire'] / 1000000);

	if ($matchId != "null" && $matchId != null) {

		$qCheckMatch = "Select role FROM abonAppareilMatch
							WHERE matchId='{$matchId}' AND telId='{$rangSel['telId']}'";
		$rCM = mysqli_query($conn, $qCheckMatch) or die(mysqli_error($conn));

		if (mysqli_num_rows($rCM) > 0) {
			$rCM_vec = mysqli_fetch_row($rCM);
			$remotes[$cptRemotes]['role'] = $rCM_vec[0];
		}
		if ((time() - strtotime($rangSel['dernierMaJ'])) > 3600 && ($rangSel['codeEtat'] == '10')) {
			$remotes[$cptRemotes]['codeEtat'] = '30';

			if ((time() - strtotime($rangSel['dernierMaJ'])) > 600 && ($rangSel['codeEtat'] == '10')) {
				$remotes[$cptRemotes]['codeEtat'] = '20';
			}
		}
	}

	$cptRemotes++;
}

$vecRet = array();
$vecRet['arenaId'] = $arenaId;
$vecRet['cams'] = $cams;
$vecRet['remotes'] = $remotes;

echo utf8_encode(json_encode($vecRet));
mysqli_close($conn);
?>
