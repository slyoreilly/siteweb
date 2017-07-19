<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];

$arenaId = $_POST['arenaId'];
$usager = $_POST['userId'];
$mavId = $_POST['mavId'];
$matchId = $_POST['matchId'];

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");


if($mavId!="null"&&$mavId!="undefined"){

$rTM = mysqli_query($conn,"SELECT match_id 
						FROM TableMatch 
						WHERE mavId='{$mavId}'")or die(mysqli_error());	
						
						
if(mysqli_num_rows($rTM)>0)
{
	$match_id_vec = mysqli_fetch_row($rTM);
	$matchId=$match_id_vec[0];
}
}


////////////////////////////
//
///		A partir d'un telId et d'un username, trouver les appareils et leurs statuts.

	$retourCam = mysqli_query($conn,"SELECT StatutCam.*, TableUser.username,abonAppareilSurface.*
						FROM StatutCam
						INNER JOIN TableUser
							ON (StatutCam.userId=TableUser.username)
							INNER JOIN abonAppareilSurface
								ON (StatutCam.telId=abonAppareilSurface.telId)
						 WHERE TableUser.username='{$usager}' 
						 AND (abonAppareilSurface.role>99 OR abonAppareilSurface.role<1 ) 
						 AND abonAppareilSurface.surfaceId='{$arenaId}'") or die(mysqli_error());
		
	
		$querySel = "SELECT StatutRemote.*, TableUser.username, abonAppareilSurface.*
						FROM StatutRemote
						INNER JOIN TableUser
							ON (StatutRemote.userId=TableUser.username)
							INNER JOIN abonAppareilSurface
								ON (StatutRemote.telId=abonAppareilSurface.telId)
						 WHERE TableUser.username='{$usager}' AND abonAppareilSurface.role<100
						 AND abonAppareilSurface.surfaceId='{$arenaId}'";
						 
		$resultSel=mysqli_query($conn,$querySel) or die("Erreur: "+$querySel+"\n"+mysqli_error());
		
		

		
		
		
		$cptCams =0;
		while($rangSel = mysqli_fetch_assoc($retourCam)){
		$cams[] = $rangSel;	
			$cams[$cptCams]['memoire']=round($rangSel['memoire']/1000000);
			$qCheckMatch = "Select role FROM abonAppareilMatch
							WHERE matchId='{$matchId}'";
			$rCM = mysqli_query($conn,$qCheckMatch)or die(mysqli_error());	
						
						
					if(mysqli_num_rows($rCM)>0)
					{
						$rCM_vec = mysqli_fetch_row($rCM);
						$cams[$cptCams]['role']=$rCM_vec[0];
					}
			if((time()-strtotime($rangSel['dernierMaJ']))>3600&&($rangSel['codeEtat']=='10')){
				$cams[$cptCams]['codeEtat']='30';
				
			
				if((time()-strtotime($rangSel['dernierMaJ']))>600&&($rangSel['codeEtat']=='10')){
					$cams[$cptCams]['codeEtat']='20';
				}	
			}	
			$cptCams++;
		}
		$remotes=Array();
		$cptRemotes=0;
				while($rangSel = mysqli_fetch_assoc($resultSel)){
					$remotes[$cptRemotes]=Array();
					$remotes[$cptRemotes]['telId']=$rangSel['telId'];
					$remotes[$cptRemotes]['codeEtat']=$rangSel['codeEtat'];
					$remotes[$cptRemotes]['dernierModif']=$rangSel['dernierModif'];
					$remotes[$cptRemotes]['dernierMaJ']=$rangSel['dernierMaJ'];
					$remotes[$cptRemotes]['batterie']=$rangSel['batterie'];
					$remotes[$cptRemotes]['remoteId']=$rangSel['remoteId'];
					$remotes[$cptRemotes]['role']=$rangSel['role'];
//					$tmpRem[]= $rangSel;
			//$remotes[$cptRemotes]=Array();		
//		$remotes[$cptRemotes]=	$tmpRem;
			$remotes[$cptRemotes]['memoire']=round($rangSel['memoire']/1000000);
			$qCheckMatch = "Select role FROM abonAppareilMatch
							WHERE matchId='{$matchId}'";
			$rCM = mysqli_query($conn,$qCheckMatch)or die(mysqli_error());	
						
						
					if(mysqli_num_rows($rCM)>0)
					{
						$rCM_vec = mysqli_fetch_row($rCM);
						$remotes[$cptRemotes]['role']=$rCM_vec[0];
					}
			if((time()-strtotime($rangSel['dernierMaJ']))>3600&&($rangSel['codeEtat']=='10')){
				$remotes[$cptRemotes]['codeEtat']='30';
				
			
				if((time()-strtotime($rangSel['dernierMaJ']))>600&&($rangSel['codeEtat']=='10')){
					$remotes[$cptRemotes]['codeEtat']='20';
				}	
			}	
			$cptRemotes++;
		}
		
		$vecRet=array();
		$vecRet['cams']=$cams;
		$vecRet['remotes']=$remotes;


	echo utf8_encode(json_encode($vecRet));
		
	
?>
