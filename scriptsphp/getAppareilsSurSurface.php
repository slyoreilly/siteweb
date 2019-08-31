<?php
$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];

$arenaId = $_POST['arenaId'];
$usager = $_POST['userId'];
$matchId = $_POST['matchId'];

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

if(isset($_POST['mavId'])){
	$mavId = $_POST['mavId'];
	if ($mavId != "null" && $mavId != "undefined") {

		$rTM = mysqli_query($conn, "SELECT match_id 
							FROM TableMatch 
							WHERE mavId='{$mavId}'") or die(mysqli_error());
	
		if (mysqli_num_rows($rTM) > 0) {
			$match_id_vec = mysqli_fetch_row($rTM);
			$matchId = $match_id_vec[0];
		}
	}
	
}

function is_url_exist($url){
    $ch = curl_init($url);    
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if($code == 200){
       $status = true;
    }else{
      $status = false;
    }
    curl_close($ch);
   return $status;
}

////////////////////////////
//
///		A partir d'un telId et d'un username, trouver les appareils et leurs statuts.

$retourCam = mysqli_query($conn, "SELECT camId, telId, codeEtat, DATE_FORMAT(StatutCam.dernierModif, '%Y-%m-%dT%TZ') AS Modif , DATE_FORMAT(StatutCam.dernierMaJ, '%Y-%m-%dT%TZ') AS MaJ, batterie, memoire, temperature, TableUser.username
						FROM StatutCam
						INNER JOIN TableUser
							ON (StatutCam.userId=TableUser.username)
						 WHERE TableUser.username='{$usager}' 
						 AND StatutCam.arenaId='{$arenaId}' ORDER BY MaJ DESC") or die(mysqli_error($conn));

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
			$cams[$cptCams]['vidDetect']=date('c',$str1 );
		}
		
	if(!$trouve){
	
		if(is_url_exist("http://5.39.81.14/lookatthis/".$rangVid['nomFichier'])){
			$trouve=true;
			$str1= substr($rangVid['chrono'],0,10);
			$cams[$cptCams]['vidLoadeChrono']=date('c',$str1 );
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

	if ($matchId != "null" && $matchId != "undefined" && $matchId != null && $matchId != undefined) {

		$qCheckMatch = "Select role FROM abonAppareilMatch
							WHERE matchId='{$matchId}' AND telId='{$rangSel['telId']}'";
		$rCM = mysqli_query($conn, $qCheckMatch) or die(mysqli_error());

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
