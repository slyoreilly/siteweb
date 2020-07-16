<?php
require '../scriptsphp/defenvvar.php';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
if(isset($_POST['telId'])){
	$telId = $_POST['telId'];
}
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
	
		$querySel = "SELECT arenaId FROM StatutRemote WHERE telId = $telId
		ORDER BY dernierMaJ DESC";
		$resultSel=mysqli_query($conn,$querySel) or die("Erreur: "+$querySel+"\n"+mysqli_error($conn));
		
		$rangSel = mysqli_fetch_row($resultSel);
		$arenaId = $rangSel[0];
		
		$querySelCam = "SELECT * FROM StatutCam WHERE arenaId = $arenaId AND telId <> $telId 
		ORDER BY dernierMaJ DESC";
		$resultSelCam=mysqli_query($conn,$querySelCam) or die("Erreur: "+$querySelCam+"\n"+mysqli_error($conn));
		
		$cptCams =0;
		while($rangSel = mysqli_fetch_assoc($resultSelCam)){
		$cams[] = $rangSel;	
			$cams[$cptCams]->memoire=round($rangSel['memoire']/1000000);
			$cams[$cptCams]->camId=$rangSel['camId'];
			if((time()-strtotime($rangSel['dernierMaJ']))>3600&&($rangSel['codeEtat']=='10')){
				$cams[$cptCams]->codeEtat='30';
				
			
				if((time()-strtotime($rangSel['dernierMaJ']))>600&&($rangSel['codeEtat']=='10')){
					$cams[$cptCams]->codeEtat='20';
				}	
			}	
			$cptCams++;
		}
		$adomper = json_encode($cams);
		break;
		
			case 2:
				
				$appareils = json_decode($arrayTel, true);
				$cptCams =0;
				$dejaIns = Array();
				$cams=Array();
				for($a=0;$a<count($appareils);$a++){

					if (!in_array($appareils[$a]['telId'],$dejaIns)){
														
					$querySel = "SELECT * FROM StatutRemote WHERE telId = '{$appareils[$a]['telId']}'
					ORDER BY dernierMaJ DESC";
		$resultSel=mysqli_query($conn,$querySel) or die("Erreur: "+$querySel+"\n"+mysqli_error($conn));
		

				
		$querySelCam = "SELECT * FROM StatutCam WHERE telId = '{$appareils[$a]['telId']}'
		ORDER BY dernierMaJ DESC";
		$resultSelCam=mysqli_query($conn,$querySelCam) or die("Erreur: "+$querySelCam+"\n"+mysqli_error($conn));
		
		
		if(mysqli_num_rows($resultSelCam)>0){
		$rangSelCam = mysqli_fetch_assoc($resultSelCam);
		$cams[$cptCams]= new \stdClass();
		$cams[$cptCams]->telId=$appareils[$a]['telId'];
		$cams[$cptCams]->camId=$rangSelCam['camId'];
		$cams[$cptCams]->memoire=round($rangSelCam['memoire']/1000000);
		$cams[$cptCams]->batterie=$rangSelCam['batterie'];
		$cams[$cptCams]->dernierMaJ=$rangSelCam['dernierMaJ'];
		$cams[$cptCams]->role=100;
		$cams[$cptCams]->codeEtat=$rangSelCam['codeEtat'];
			if((time()-strtotime($rangSelCam['dernierMaJ']))>3600&&($rangSelCam['codeEtat']=='10')){
				$cams[$cptCams]->codeEtat='30';
			}
			
				if((time()-strtotime($rangSelCam['dernierMaJ']))>600&&($rangSelCam['codeEtat']=='10')){
					$cams[$cptCams]->codeEtat='20';
				}	
				
			$cptCams++;


			array_push($dejaIns ,$appareils[$a]['telId']);
		}
		/*
		if(mysqli_num_rows($resultSel)>0){
		
			$rangSel = mysqli_fetch_assoc($resultSel);
			//$cams[$cptCams]=Array();

			$cams[$cptCams]->telId=$appareils[$a]['telId'];
			$cams[$cptCams]->batterie=$rangSel['batterie'];
			$cams[$cptCams]->role=10;

				$cams[$cptCams]->dernierMaJ=$rangSel['dernierMaJ'];
				$cams[$cptCams]->memoire=round($rangSel['memoire']/1000000);
				if((time()-strtotime($rangSel['dernierMaJ']))>3600&&($rangSel['codeEtat']=='10')){
					$cams[$cptCams]->codeEtat='30';
					
				
					if((time()-strtotime($rangSel['dernierMaJ']))>600&&($rangSel['codeEtat']=='10')){
						$cams[$cptCams]->codeEtat='20';
					}	
				}	
				$cptCams++;
			
			}*/



											
										
									
		}			
							
						
					
				}		

				function sortByDmaj($a, $b) {
				//	if(($b->role -$a->role)==0){
						return  $b->dernierMaJ -$a->dernierMaJ ;
				//	}
				//	else{ 
				//		return $b->role -$a->role;
				//	}
				}
				
				usort($cams, 'sortByDmaj');
				
				
				
				


				$adomper = json_encode($cams);		
				
				break;
		
		}
		
	$adomper = str_replace('"[', '[', $adomper);
	$adomper = str_replace(']"', ']', $adomper);
	echo utf8_encode($adomper);
	mysqli_close($conn);
	
?>
