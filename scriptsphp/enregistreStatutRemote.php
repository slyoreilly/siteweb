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
$codeEtat= $_POST['codeEtat'];

		echo " - INIT";


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


////////////////////////////
//
///		Chercher la ligne si elle existe.
///			La modifier si elle existe
///			L'insérer sinon.


		echo " - PRESEL";
		
		
	
		$querySel = "SELECT codeEtat FROM StatutRemote WHERE telId = '{$telId}'";
		$resultSel=mysql_query($querySel) or die("Erreur: "+$querySel+"\n"+mysql_error());
		
		$rangSel=mysql_num_rows($resultSel);
		
		echo " - SELNUM:".$rangSel;
		
		
		if($rangSel>0)
			{$tmpSel=mysql_fetch_row($resultSel );
			
			if($codeEtat=$tmpSel[0]){
				$queryMod = "UPDATE StatutRemote SET memoire = '{$memoire}', batterie = '{$batterie}', dernierMaJ=now(), userId = '{$usager}', arenaId = '{$arenaId}'
					WHERE telId='{$telId}'";
				mysql_query($queryMod) or die("Erreur: "+$queryMod+"\n"+mysql_error());
				echo "- MOD1";
				}
				else{
				$queryMod = "UPDATE StatutRemote SET dernierModif = now(), memoire = '{$memoire}', batterie = '{$batterie}', dernierMaJ=now(), userId = '{$usager}', arenaId = '{$arenaId}'
					WHERE telId='{$telId}'";
				mysql_query($queryMod) or die("Erreur: "+$queryMod+"\n"+mysql_error());
				echo "- MOD2";
				}
		echo "- MOD";
			
			}
		else {
		echo " - PREINS1";
			$queryIns = "INSERT INTO StatutRemote (userId,dernierModif,dernierMaJ,arenaId,batterie, memoire, telId, codeEtat) ".
				"VALUES ('{$usager}',now(),now(),'{$arenaId}','{$batterie}','{$memoire}','{$telId}','{$codeEtat}')";
		echo " - PREINS2";
		
			mysql_query($queryIns) or die("Erreur: "+$queryIns+"\n"+mysql_error());
		echo " - INS";
		
		}
	
	
		echo " - FIN";
		
		mysql_close();
	
?>
