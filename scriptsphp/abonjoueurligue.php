<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';


$pm=null;
if(isset($_POST['premierMatch'])){
	$pm = $_POST['premierMatch'];}

$dm=null;
	if(isset($_POST['dernierMatch'])){
		$dm = $_POST['dernierMatch'];}
	


$joueurId = $_POST['joueurId'];
$ligueId = $_POST['ligueId'];
$code = $_POST['code'];




$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");


//////////////////////////////////
//
//	Les queries
//


if($premierMatch==null||$premierMatch=="")
{$pm='NOW()';}
else
{$pm="'".$pm."'";}
	
/// Code 60: Effacer les abonnements completement de joueur a une ligue. 
if($code==60)
{
	$query_delete = "UPDATE abonJoueurLigue SET finAbon=NOW()-INTERVAL 1 DAY WHERE ligueId=$ligueId AND joueurId=$joueurId";
$retour = mysqli_query($conn,$query_delete)or die('Error, query failed: '.mysqli_error($conn).$query_delete1);


$retour1 = mysqli_query($conn,"SELECT * FROM abonJoueurEquipe 
							JOIN abonEquipeLigue 
							 ON (abonJoueurEquipe.equipeId=abonEquipeLigue.equipeId) 
							WHERE  joueurId='{$joueurId}' 
								AND abonEquipeLigue.ligueId=$ligueId")or die('Error, query failedrtrtytr: '.mysqli_error($conn));
		if(mysqli_num_rows($retour1)>0)			//S'il y avait déjà un abonnement, mettre fin à celui-ci.
		{while($rangee = mysqli_fetch_assoc($retour1))
			//{$equipe=$rangee['equipeId'];}
			
//			$retour.=mysql_query("UPDATE abonJoueurEquipe SET finAbon=NOW() WHERE joueurId='{$lesJoueurs[$Ij]['joueurId']}' AND equipeId=$equipe");
			$retour.=mysqli_query($conn,"DELETE FROM abonJoueurEquipe  WHERE abonJouEq = {$rangee['abonJouEq']}")or die('Error, query faileqdwqd: '.mysqli_error($conn));

		}




}
else
{
	
	
	
	$query_equipe = "INSERT INTO abonJoueurLigue (joueurId, ligueId, permission, debutAbon, finAbon) ".
"VALUES ($joueurId, $ligueId, 30, ".$pm.",'$dm' )";
		
$retour = mysqli_query($conn,$query_equipe)or die('Error, query failed: '.mysqli_error($conn).$query_equipe);
}	

	echo $retour ;
	mysqli_close($conn);
?>
