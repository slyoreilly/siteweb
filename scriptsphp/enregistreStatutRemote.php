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
$codeEtat= $_POST['codeEtat'];

	
// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

////////////////////////////
//
///		Chercher la ligne si elle existe.
///			La modifier si elle existe
///			L'insérer sinon.


		echo " - PRESEL";
		
		
	
		$querySel = "SELECT codeEtat FROM StatutRemote WHERE telId = '{$telId}'";
		$resultSel=mysqli_query($conn,$querySel) or die("Erreur: "+$querySel+"\n"+mysqli_error($conn));
		
		$rangSel=mysqli_num_rows($resultSel);
		
		echo " - SELNUM:".$rangSel;
		
		
		if($rangSel>0)
			{$tmpSel=mysqli_fetch_row($resultSel );
			
			if($codeEtat=$tmpSel[0]){
				$queryMod = "UPDATE StatutRemote SET memoire = '{$memoire}', batterie = '{$batterie}', dernierMaJ=now(), userId = '{$usager}', arenaId = '{$arenaId}'
					WHERE telId='{$telId}'";
				mysqli_query($conn,$queryMod) or die("Erreur: "+$queryMod+"\n"+mysqli_error($conn));
				echo "- MOD1";
				}
				else{
				$queryMod = "UPDATE StatutRemote SET dernierModif = now(), memoire = '{$memoire}', batterie = '{$batterie}', dernierMaJ=now(), userId = '{$usager}', arenaId = '{$arenaId}'
					WHERE telId='{$telId}'";
				mysqli_query($conn,$queryMod) or die("Erreur: "+$queryMod+"\n"+mysqli_error($conn));
				echo "- MOD2";
				}
		echo "- MOD";
			
			}
		else {
		echo " - PREINS1";
			$queryIns = "INSERT INTO StatutRemote (userId,dernierModif,dernierMaJ,arenaId,batterie, memoire, telId, codeEtat) ".
				"VALUES ('{$usager}',now(),now(),'{$arenaId}','{$batterie}','{$memoire}','{$telId}','{$codeEtat}')";
		echo " - PREINS2";
		
			mysqli_query($conn,$queryIns) or die("Erreur: "+$queryIns+"\n"+mysqli_error($conn));
		echo " - INS";
		
		}
	
	
		echo " - FIN";
		
		mysqli_close($conn);
	
?>
