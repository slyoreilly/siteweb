<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];

$telId = $_POST['telId'];
$retour=array();



// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");
	
	
	$qSel="SELECT * FROM Controle WHERE telId='{$telId}'";	
	$retSel=mysqli_query($conn,$qSel) or die("Erreur: "+$qSel+"\n"+mysqli_error($conn));
	$cpt= 0;
	while($rangee = mysqli_fetch_assoc($retSel))
		{
		
		$retour[$cpt] = $rangee;
		$cpt++;
		}
	
	echo json_encode($retour);
	
	$qUp="UPDATE  Controle SET etatSync=12 WHERE telId='{$telId}'";	
	$retUp=mysqli_query($conn,$qUp) or die("Erreur: "+$qUp+"\n"+mysqli_error($conn));
	
	if(json_encode($retour)==False)
	{echo "erreur, count(syncOK:): ".count($retour)."- count($retour): ".count($retour);}
mysqli_close($conn);

?>
