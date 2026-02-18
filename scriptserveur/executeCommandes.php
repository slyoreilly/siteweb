<?php
require '../scriptsphp/defenvvar.php';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection/
if (!$conn) {
	error_log("Connection failed: " . mysqli_connect_error());
   die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

	
	//////////////////////////////////////////////////////////////////////
//
//	
//
/////////////////////////////////////////////////////////////////////


$retour1 = mysqli_query($conn,"SELECT * FROM TacheShell 
							WHERE  statut=0 AND essais<10  ORDER BY priorite DESC");
								error_log("Nb Taches: " . mysqli_num_rows($retour1));
							
		if(mysqli_num_rows($retour1)>0)			
		{while($rangee = mysqli_fetch_assoc($retour1)){
			$ret = shell_exec($rangee['commande']);
			$nouvEssai= intval($rangee['essais'])+1;
			mysqli_query($conn,"UPDATE TacheShell SET statut=1, retour='{$ret}', essais='{$nouvEssai}'  WHERE commande = '{$rangee['commande']}'");
		}
		
		}

//mysqli_close($conn);
//include 'library/closedb.php';
	
?>
