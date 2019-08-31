<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$videoId= $_POST['videoId'];


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");
	


//////////////////////////////////
//
//	Les queries
//


$qVielleSaison="SELECT nbVues FROM Video WHERE videoId='{$videoId}' limit 0,1";
$resVS=mysqli_query($conn,qVielleSaison) or die(mysqli_error($conn).'Error, query failed'.$qVielleSaison);
		while ($rVS = mysqli_fetch_array($resVS)) {
			$aMettre = $rVS[0]+1;
			
	$qSUp = "UPDATE Video 
							SET nbVues='{$aMettre}'
							WHERE videoId='{$videoId}' ";
		mysqli_query($conn,$qSUp) or die(mysqli_error($conn).' Error, query failed'.$qSUp);

		}
		

mysqli_close($conn);	
?>
