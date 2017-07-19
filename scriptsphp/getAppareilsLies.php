<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];

$telId = $_POST['telId'];
$usager = $_POST['username'];
$mode = $_POST['mode'];
$arrayTel = $_POST['arrayTel'];


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

////////////////////////////
//
///		A partir d'un telId et d'un username, trouver les appareils et leurs statuts.


		switch($mode){
			case 1:
	
		$querySel = "SELECT arenaId FROM StatutRemote WHERE telId = $telId";
		$resultSel=mysqli_query($conn,$querySel) or die("Erreur: "+$querySel+"\n"+mysqli_error());
		
		$rangSel = mysqli_fetch_row($resultSel);
		$arenaId = $rangSel[0];
		
		$querySelCam = "SELECT * FROM StatutCam WHERE arenaId = $arenaId AND telId <> $telId";
		$resultSelCam=mysqli_query($conn,$querySelCam) or die("Erreur: "+$querySelCam+"\n"+mysqli_error());
		
		$cptCams =0;
		while($rangSel = mysqli_fetch_assoc($resultSelCam)){
		$cams[] = $rangSel;	
			$cams[$cptCams]['memoire']=round($rangSel['memoire']/1000000);
			if((time()-strtotime($rangSel['dernierMaJ']))>3600&&($rangSel['codeEtat']=='10')){
				$cams[$cptCams]['codeEtat']='30';
				
			
				if((time()-strtotime($rangSel['dernierMaJ']))>600&&($rangSel['codeEtat']=='10')){
					$cams[$cptCams]['codeEtat']='20';
				}	
			}	
			$cptCams++;
		}
		
		break;
		
			case 2:
				
				$appareils = json_decode($arrayTel, true);
				$cptCams =0;
				$cams=Array();
				for($a=0;$a<count($appareils);$a++){
														
					$querySel = "SELECT * FROM StatutRemote WHERE telId = '{$appareils[$a]['telId']}'";
		$resultSel=mysqli_query($conn,$querySel) or die("Erreur: "+$querySel+"\n"+mysqli_error());
		
		if(mysqli_num_rows($resultSel)>0){
		
		$rangSel = mysqli_fetch_assoc($resultSel);
		
		$cams[$cptCams]['telId']=$appareils[$a]['telId'];
		$cams[$cptCams]['batterie']=$rangSel['batterie'];
			$cams[$cptCams]['dernierMaJ']=$rangSel['dernierMaJ'];
			$cams[$cptCams]['memoire']=round($rangSel['memoire']/1000000);
			if((time()-strtotime($rangSel['dernierMaJ']))>3600&&($rangSel['codeEtat']=='10')){
				$cams[$cptCams]['codeEtat']='30';
				
			
				if((time()-strtotime($rangSel['dernierMaJ']))>600&&($rangSel['codeEtat']=='10')){
					$cams[$cptCams]['codeEtat']='20';
				}	
			}	
			$cptCams++;
		
		}
				
		$querySelCam = "SELECT * FROM StatutCam WHERE telId = '{$appareils[$a]['telId']}'";
		$resultSelCam=mysqli_query($conn,$querySelCam) or die("Erreur: "+$querySelCam+"\n"+mysqli_error());
		
		
		if(mysqli_num_rows($resultSelCam)>0){
		$rangSelCam = mysqli_fetch_assoc($resultSelCam);
		$cams[$cptCams]['telId']=$appareils[$a]['telId'];
		
		$cams[$cptCams]['memoire']=round($rangSelCam['memoire']/1000000);
		$cams[$cptCams]['batterie']=$rangSelCam['batterie'];
		$cams[$cptCams]['dernierMaJ']=$rangSelCam['dernierMaJ'];
			if((time()-strtotime($rangSelCam['dernierMaJ']))>3600&&($rangSelCam['codeEtat']=='10')){
				$cams[$cptCams]['codeEtat']='30';
				
			
				if((time()-strtotime($rangSelCam['dernierMaJ']))>600&&($rangSelCam['codeEtat']=='10')){
					$cams[$cptCams]['codeEtat']='20';
				}	
			}	
			$cptCams++;
		}						
												
											
										
									
								
							
						
					
				}				
					
				
				break;
		
		}
			$adomper = json_encode($cams);
	$adomper = str_replace('"[', '[', $adomper);
	$adomper = str_replace(']"', ']', $adomper);
	echo utf8_encode($adomper);
		
	
?>
