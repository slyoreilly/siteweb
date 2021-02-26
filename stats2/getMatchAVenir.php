<?php

require '../scriptsphp/defenvvar.php';

$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
$mavId = $_POST['mavId'];
$ligueId = $_POST['ligueId'];

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_query($conn, "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY','')");

//						 GROUP BY MatchAVenir.mavId

// Faudrait refaire en cadrant par rapport à la saison en cours plutôt qu'aux équipes abonnées.
$strRetour.=$mavId;
$retour = mysqli_query($conn, "SELECT TableMatch.*	
						FROM TableMatch
						LEFT JOIN TableSaison
							ON (TableMatch.ligueRef=TableSaison.ligueRef)
						 WHERE TableMatch.ligueRef='{$ligueId}'
						
						 	
						 GROUP BY TableMatch.mavId")or die(mysqli_error($conn));	
						 /* AND MatchAVenir.date > (NOW()-INTERVAL 30 DAY) AND TableSaison.dernierMatch>NOW()*/
$strRetour.= mysqli_num_rows($retour);

$vecMatch = array();
$IM=0;
while($r = mysqli_fetch_assoc($retour)) {
	
	if(!is_numeric($mavId)||$r['mavId']==$mavId)
	{
    $vecMatch[$IM] = $r;
	$vecMatch[$IM]['alDom']=array();
	$vecMatch[$IM]['alVis']=array();
	$vecMatch[$IM]['gDom']=array();
	$vecMatch[$IM]['gVis']=array();
	$tmp =  str_replace('"[','[',$r['alignementDom']);
	$tmp =  str_replace(']"',']',$tmp);
	$alDom=json_decode($tmp,true);
	  if(!is_array($alDom)) {$alDom= array();}
	$tmp =  str_replace('"[','[',$r['alignementVis']);
	$tmp =  str_replace(']"',']',$tmp);
	$alVis=json_decode($tmp,true);
	if(!is_array($alVis)) {$alVis= array();}
	for($a=0;$a<count($alDom);$a++)
	{
//	$vecMatch[$IM]['alDom'][$a]=array();

	$qAlDom= "SELECT joueur_id,NomJoueur,NumeroJoueur,position		
						FROM TableJoueur
						 WHERE joueur_id='{$alDom[$a]}'";
	$rAlDom = mysqli_query($conn,$qAlDom)or die(mysqli_error($conn));
	while($rangAlDom = mysqli_fetch_assoc($rAlDom)) {
		$vecMatch[$IM]['alDom'][$a]=$rangAlDom;
    	 }
	}
	for($a=0;$a<count($alVis);$a++)
	{
//	$vecMatch[$IM]['alVis'][$a]=array();

	$qAlVis= "SELECT joueur_id,NomJoueur,NumeroJoueur,position		
						FROM TableJoueur
						 WHERE joueur_id='{$alVis[$a]}'";
	$rAlVis = mysqli_query($conn,$qAlVis)or die(mysqli_error($conn));
	while($rangAlVis = mysqli_fetch_assoc($rAlVis)) {
		$vecMatch[$IM]['alVis'][$a]=$rangAlVis;
    	 }
	}
	
		$qGDom= "SELECT joueur_id,NomJoueur,NumeroJoueur,position		
						FROM TableJoueur
						 WHERE joueur_id='{$r['gardienDom']}'";
	$rGDom = mysqli_query($conn,$qGDom)or die(mysqli_error($conn));
	while($rangGDom = mysqli_fetch_assoc($rGDom)) {
		$vecMatch[$IM]['gDom']=$rangGDom;
	}
		$qGVis= "SELECT joueur_id,NomJoueur,NumeroJoueur,position		
						FROM TableJoueur
						 WHERE joueur_id='{$r['gardienVis']}'";
	$rGVis = mysqli_query($conn,$qGVis)or die(mysqli_error($conn));
	while($rangGVis = mysqli_fetch_assoc($rGVis)) {
		$vecMatch[$IM]['gVis']=$rangGVis;
	}
	
		$IM++;
	}
	
	
	
}
//$adomper= stripslashes(json_encode($vecMatch));
$adomper= json_encode($vecMatch);
$adomper= str_replace('\"','"',$adomper);
$adomper= str_replace('"[','[',$adomper);
$adomper= str_replace(']"',']',$adomper);
echo $adomper;


	//		header("HTTP/1.1 200 OK");
?>
<?php  ?>
