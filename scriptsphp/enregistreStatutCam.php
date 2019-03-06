<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
//$heure = $_POST['date'];
$usager = $_POST['username'];
$arenaId = $_POST['arenaId'];
$telId = $_POST['telId'];
$batterie = $_POST['batterie'];
$memoire = $_POST['memoire'];
$temperature = round($_POST['temperature']);
$codeEtat= $_POST['codeEtat'];
$camId= $_POST['camId'];
$version= $_POST['version'];


		echo " - INIT";
	echo " ".$temperature." ";



// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");
	

$dt = new DateTime("now", new DateTimeZone('GMT'));

$mTemps= $dt->format('Y-m-d H:i:s');

////////////////////////////
//
///		Chercher la ligne si elle existe.
///			La modifier si elle existe
///			L'insérer sinon.
		


		
	
		echo " - INIT2";
		$querySel = "SELECT codeEtat FROM StatutCam WHERE telId = '{$telId}'";
		echo $querySel;
		$resultSel=mysqli_query($conn, $querySel) or die("Erreur: ".$querySel."\n".mysqli_error($conn));
		
		echo " - INIT3";
		$message = " - Sel".$querySel. mysqli_error($conn);
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/statutCam.txt', $log, FILE_APPEND);	
							
		echo " - INIT4";
		$rangSel=mysqli_num_rows($resultSel);
		
		echo " - SELNUM:".$rangSel;
		
		if($rangSel>0)
			{$tmpSel=mysqli_fetch_row($resultSel );
			
			if($codeEtat==$tmpSel[0]){
				$queryMod = "UPDATE StatutCam SET memoire = '{$memoire}', batterie = '{$batterie}',temperature='{$temperature}', dernierMaJ='{$mTemps}', version='{$version}', userId = '{$usager}', arenaId = '{$arenaId}', camId = '{$camId}', codeEtat = '{$codeEtat}'
					WHERE telId='{$telId}'";
				mysqli_query($conn,$queryMod) or die("Erreur: ".$queryMod."\n".mysqli_error($conn));
						$message = " - mod1".$queryMod. mysql_error();
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/statutCam.txt', $log, FILE_APPEND);	
						
				}
				else{
				$queryMod = "UPDATE StatutCam SET dernierModif ='{$mTemps}', memoire = '{$memoire}', version = '{$version}', batterie = '{$batterie}'
				,temperature='{$temperature}', dernierMaJ='{$mTemps}', userId = '{$usager}', arenaId = '{$arenaId}', camId = '{$camId}', codeEtat = '{$codeEtat}'
					WHERE telId='{$telId}'";
				mysqli_query($conn, $queryMod) or die("Erreur: ".$queryMod."\n".mysqli_error($conn));
						echo " - mod2".$queryMod;
						$message = " - mod2".$queryMod. mysqli_error($conn);
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/statutCam.txt', $log, FILE_APPEND);				}
			
			}
		else {
			$queryIns = "INSERT INTO StatutCam (userId,dernierModif,dernierMaJ,arenaId,batterie, memoire,temperature, telId, codeEtat, camId, version) ".
				"VALUES ('{$usager}','{$mTemps}','{$mTemps}','{$arenaId}','{$batterie}','{$memoire}','{$temperature}','{$telId}','{$codeEtat}','{$camId}','{$version}')";
		echo $queryIns;
			mysqli_query($conn,$queryIns) or die("Erreur: ".$queryIns."\n".mysqli_error($conn));
		$message = " - ins".$queryIns. mysqli_error($conn);
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							//file_put_contents('../test/statutCam.txt', $log, FILE_APPEND);
		echo $log;
						}
	
	
		
	mysqli_close($conn);	
	
?>
