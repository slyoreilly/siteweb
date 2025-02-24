<?php
require '../scriptsphp/defenvvar.php';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
//$heure = $_POST['date'];
$usager = $_POST['username'];
$arenaId = $_POST['arenaId'];
$telId = $_POST['telId'];
$remoteId = $_POST['telId'];
$batterie = $_POST['batterie'];
$memoire = $_POST['memoire'];
$temperature = round($_POST['temperature']);
$codeEtat= $_POST['codeEtat'];
$camId= $_POST['remoteId'];
$version= $_POST['version'];
$settings= $_POST['settings'];

$dt = new DateTime("now", new DateTimeZone('GMT'));

$mTemps= $dt->format('Y-m-d H:i:s');




////////////////////////////
//
///		Chercher la ligne si elle existe.
///			La modifier si elle existe
///			L'insérer sinon.


		echo " - PRESEL";
		
		
	
		$querySel = "SELECT codeEtat FROM StatutRemote WHERE telId = '{$telId}'";
		$resultSel=mysqli_query($conn,$querySel) or die("Erreur: ".$querySel."\n".mysqli_error($conn));
		
		$rangSel=mysqli_num_rows($resultSel);
		
		echo " - SELNUM:".$rangSel;
		
		
		if($rangSel>0)
			{$tmpSel=mysqli_fetch_row($resultSel );
			
			if($codeEtat=$tmpSel[0]){
				$queryMod = "UPDATE StatutRemote SET memoire = '{$memoire}', batterie = '{$batterie}',temperature='{$temperature}', dernierMaJ='{$mTemps}', version='{$version}', userId = '{$usager}', arenaId = '{$arenaId}',settings='{$settings}', codeEtat = '{$codeEtat}'
					WHERE telId='{$telId}'";
				mysqli_query($conn,$queryMod) or die("Erreur: ".$queryMod."\n".mysqli_error($conn));
				echo "- MOD1";
				}
				else{
					$queryMod = "UPDATE StatutRemote SET dernierModif ='{$mTemps}', memoire = '{$memoire}', version = '{$version}', batterie = '{$batterie}'
					,temperature='{$temperature}', dernierMaJ='{$mTemps}', userId = '{$usager}',settings='{$settings}', arenaId = '{$arenaId}', codeEtat = '{$codeEtat}'
						WHERE telId='{$telId}'";
								mysqli_query($conn,$queryMod) or die("Erreur: ".$queryMod."\n".mysqli_error($conn));
				echo "- MOD2";
				}
		echo "- MOD";
			
			}
		else {
		echo " - PREINS1";
		$queryIns = "INSERT INTO StatutRemote (userId,dernierModif,dernierMaJ,arenaId,batterie, memoire,temperature, telId, codeEtat, remoteId, version,settings) ".
		"VALUES ('{$usager}','{$mTemps}','{$mTemps}','{$arenaId}','{$batterie}','{$memoire}','{$temperature}','{$telId}','{$codeEtat}','{$remoteId}','{$version}','{$settings}')";
		echo " - PREINS2";
		
			mysqli_query($conn,$queryIns) or die("Erreur: ".$queryIns."\n".mysqli_error($conn));
		echo " - INS";
		
		}
	
	
		echo " - FIN";
		
		mysqli_close($conn);
	
?>
