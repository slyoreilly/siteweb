<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
$heure = $_POST['date'];
$usager = $_POST['username'];
$arenaId = $_POST['arenaId'];
$telId = $_POST['telId'];
$batterie = $_POST['batterie'];
$memoire = $_POST['memoire'];
$temperature = round($_POST['temperature']);
$codeEtat= $_POST['codeEtat'];
$camId= $_POST['camId'];

		echo " - INIT";
	echo " ".$temperature." ";


if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");



$dt = new DateTime("now", new DateTimeZone('America/Toronto'));

$mTemps= $dt->format('Y-m-d H:i:s');

////////////////////////////
//
///		Chercher la ligne si elle existe.
///			La modifier si elle existe
///			L'insérer sinon.
		


		
	
		echo " - INIT2";
		$querySel = "SELECT codeEtat FROM StatutCam WHERE telId = '{$telId}'";
		echo $querySel;
		$resultSel=mysql_query($querySel) or die("Erreur: ".$querySel."\n".mysql_error());
		
		echo " - INIT3";
		$message = " - Sel".$querySel. mysql_error();
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/statutCam.txt', $log, FILE_APPEND);	
							
		echo " - INIT4";
		$rangSel=mysql_num_rows($resultSel);
		
		echo " - SELNUM:".$rangSel;
		
		if($rangSel>0)
			{$tmpSel=mysql_fetch_row($resultSel );
			
			if($codeEtat==$tmpSel[0]){
				$queryMod = "UPDATE StatutCam SET memoire = '{$memoire}', batterie = '{$batterie}',temperature='{$temperature}', dernierMaJ='{$mTemps}', userId = '{$usager}', arenaId = '{$arenaId}', camId = '{$camId}', codeEtat = '{$codeEtat}'
					WHERE telId='{$telId}'";
				mysql_query($queryMod) or die("Erreur: "+$queryMod+"\n"+mysql_error());
						$message = " - mod1".$queryMod. mysql_error();
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/statutCam.txt', $log, FILE_APPEND);	
						
				}
				else{
				$queryMod = "UPDATE StatutCam SET dernierModif ='{$mTemps}', memoire = '{$memoire}', batterie = '{$batterie}'
				,temperature='{$temperature}', dernierMaJ='{$mTemps}', userId = '{$usager}', arenaId = '{$arenaId}', camId = '{$camId}', codeEtat = '{$codeEtat}'
					WHERE telId='{$telId}'";
				mysql_query($queryMod) or die("Erreur: "+$queryMod+"\n"+mysql_error());
						echo " - mod2".$queryMod;
						$message = " - mod2".$queryMod. mysql_error();
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/statutCam.txt', $log, FILE_APPEND);				}
			
			}
		else {
			$queryIns = "INSERT INTO StatutCam (userId,dernierModif,dernierMaJ,arenaId,batterie, memoire,temperature, telId, codeEtat, camId) ".
				"VALUES ('{$usager}''{$mTemps}','{$mTemps}','{$arenaId}','{$batterie}','{$memoire}','{$temperature}','{$telId}','{$codeEtat}','{$camId}')";
		
			mysql_query($queryIns) or die("Erreur: "+$queryIns+"\n"+mysql_error());
		$message = " - ins".$queryIns. mysql_error();
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/statutCam.txt', $log, FILE_APPEND);
		}
	
	
		
	mysql_close();	
	
?>
