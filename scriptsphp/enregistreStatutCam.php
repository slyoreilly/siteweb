<?php
require '../scriptsphp/defenvvar.php';

require  __DIR__ .'/../phpobjects/Sensor.php';
require_once __DIR__ .'/../phpobjects/Alarm.php';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
//$heure = $_POST['date'];
$usager = $_POST['username'];
$arenaId = $_POST['arenaId']===''? 0:$_POST['arenaId'];
$telId = $_POST['telId'];
$batterie = $_POST['batterie'];
$memoire = round($_POST['memoire']);
$memoireInterne = round($_POST['memoireInterne']);
$temperature = round($_POST['temperature']);
$codeEtat= $_POST['codeEtat'];
$camId= $_POST['camId'];
$version= $_POST['version'];
$settings= $_POST['settings'];


		echo " - INIT";
	echo " ".$temperature." ";


$dt = new DateTime("now", new DateTimeZone('GMT'));

$mTemps= $dt->format('Y-m-d H:i:s');


function loadAlarms($conn,$telId){
	$alarms=array();
	$ret = mysqli_query($conn,
			"SELECT *
			FROM Alarms
				WHERE telId='{$telId}'
			")or die(mysqli_error($conn)." SELECT");
		while ($rangSel = mysqli_fetch_array($ret))
		{
			$toAdd=new Alarm();
			array_push($alarms, $toAdd);
			$toAdd->setData(
				$rangSel['alarmId'],
				$rangSel['target'],
				$rangSel['telId'],
				$rangSel['operand'],
				$rangSel['sensorType'],
				$rangSel['value'],
				$rangSel['ringing'],
				$rangSel['cv'],
				$rangSel['alarmClass']
			);
		}
		return $alarms;


}



//////////////////////

$chrono = time()*1000;
$sensors = array();
	$sensorTemp = new Sensor();
	$sensorTemp->setData(1,$telId,$temperature,$chrono);
	$retVal= $sensorTemp->db_dump();
//	error_log("1");
array_push($sensors,$sensorTemp);
//error_log("2");
$sensorMem = new Sensor();
//error_log("3");
$sensorMem->setData(2,$telId,$memoire,$chrono);
//error_log("4");
$retVal= $sensorMem->db_dump();
array_push($sensors,$sensorMem);
	$sensorBat = new Sensor();
	$sensorBat->setData(3,$telId,$batterie,$chrono);
	$retVal= $sensorBat->db_dump();
array_push($sensors,$sensorBat);
$sensorCamState = new Sensor();
$sensorCamState->setData(4,$telId,$codeEtat,$chrono);
$retVal= $sensorCamState->db_dump();
array_push($sensors,$sensorCamState);

$alarms = loadAlarms($conn,$telId);
foreach($alarms as $alarm){
	foreach($sensors as $sensor){
		$alarm->checkAlarm($sensor);
	}	
}











////////////////////////////
//
///		Chercher la ligne si elle existe.
///			La modifier si elle existe
///			L'insérer sinon.
		
		$querySel = "SELECT codeEtat FROM StatutCam WHERE telId = '{$telId}'";
		$resultSel=mysqli_query($conn, $querySel) or die("Erreur: ".$querySel."\n".mysqli_error($conn));
//		$message = " - Sel".$querySel. mysqli_error($conn);
//							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
//	        				"-------------------------".PHP_EOL;
//							file_put_contents('../test/statutCam.txt', $log, FILE_APPEND);	

		$rangSel=mysqli_num_rows($resultSel);
		
		
		if($rangSel>0)
			{$tmpSel=mysqli_fetch_row($resultSel );
			
			if($codeEtat==$tmpSel[0]){
				$queryMod = "UPDATE StatutCam SET memoire = '{$memoire}',  memoireInterne = '{$memoireInterne}', batterie = '{$batterie}',temperature='{$temperature}', dernierMaJ='{$mTemps}', version='{$version}', userId = '{$usager}', arenaId = '{$arenaId}', camId = '{$camId}',settings='{$settings}', codeEtat = '{$codeEtat}'
					WHERE telId='{$telId}'";
				mysqli_query($conn,$queryMod) or die("Erreur: ".$queryMod."\n".mysqli_error($conn));
						$message = " - mod1".$queryMod. mysqli_error($conn);
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
						//	file_put_contents('../test/statutCam.txt', $log, FILE_APPEND);	
						
				}
				else{
				$queryMod = "UPDATE StatutCam SET dernierModif ='{$mTemps}', memoire = '{$memoire}',  memoireInterne = '{$memoireInterne}', version = '{$version}', batterie = '{$batterie}'
				,temperature='{$temperature}', dernierMaJ='{$mTemps}', userId = '{$usager}',settings='{$settings}', arenaId = '{$arenaId}', camId = '{$camId}', codeEtat = '{$codeEtat}'
					WHERE telId='{$telId}'";
				mysqli_query($conn, $queryMod) or die("Erreur: ".$queryMod."\n".mysqli_error($conn));
						echo " - mod2".$queryMod;
						$message = " - mod2".$queryMod. mysqli_error($conn);
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
						//	file_put_contents('../test/statutCam.txt', $log, FILE_APPEND);			
							}
			
			}
		else {
			$queryIns = "INSERT INTO StatutCam (userId,dernierModif,dernierMaJ,arenaId,batterie, memoire,  memoireInterne ,temperature, telId, codeEtat, camId, version,settings) ".
				"VALUES ('{$usager}','{$mTemps}','{$mTemps}','{$arenaId}','{$batterie}','{$memoire}','{$memoireInterne}','{$temperature}','{$telId}','{$codeEtat}','{$camId}','{$version}','{$settings}')";
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
