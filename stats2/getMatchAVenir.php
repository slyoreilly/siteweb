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

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
$mavId = $_POST['mavId'];
$ligueId = $_POST['ligueId'];

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
//$jDom = json_decode($jDomJSON, true);
//$jVis = json_decode($jVisJSON, true);
// Ancienne requête:SELECT MatchAVenir.*	
//						FROM MatchAVenir
//						LEFT JOIN abonEquipeLigue
//							ON (MatchAVenir.ligueId=abonEquipeLigue.ligueId)
//						 WHERE MatchAVenir.ligueId='{$ligueId}'
//						 	AND abonEquipeLigue.finAbon>NOW()
//						 	AND (abonEquipeLigue.equipeId =MatchAVenir.eqDom OR abonEquipeLigue.equipeId =MatchAVenir.eqVis)
//						 GROUP BY MatchAVenir.mavId

// Faudrait refaire en cadrant par rapport à la saison en cours plutôt qu'aux équipes abonnées.
$strRetour.=$mavId;
$retour = mysql_query("SELECT MatchAVenir.*	
						FROM MatchAVenir
						LEFT JOIN TableSaison
							ON (MatchAVenir.ligueId=TableSaison.ligueRef)
						 WHERE MatchAVenir.ligueId='{$ligueId}'
						
						 	
						 GROUP BY MatchAVenir.mavId")or die(mysql_error());	
						 /* AND MatchAVenir.date > (NOW()-INTERVAL 30 DAY) AND TableSaison.dernierMatch>NOW()*/
$strRetour.= mysql_num_rows($retour);

$vecMatch = array();
$IM=0;
while($r = mysql_fetch_assoc($retour)) {
	
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
	$tmp =  str_replace('"[','[',$r['alignementVis']);
	$tmp =  str_replace(']"',']',$tmp);
	$alVis=json_decode($tmp,true);
	for($a=0;$a<count($alDom);$a++)
	{
//	$vecMatch[$IM]['alDom'][$a]=array();

	$qAlDom= "SELECT joueur_id,NomJoueur,NumeroJoueur,position		
						FROM TableJoueur
						 WHERE joueur_id='{$alDom[$a]}'";
	$rAlDom = mysql_query($qAlDom)or die(mysql_error());
	while($rangAlDom = mysql_fetch_assoc($rAlDom)) {
		$vecMatch[$IM]['alDom'][$a]=$rangAlDom;
    	 }
	}
	for($a=0;$a<count($alVis);$a++)
	{
//	$vecMatch[$IM]['alVis'][$a]=array();

	$qAlVis= "SELECT joueur_id,NomJoueur,NumeroJoueur,position		
						FROM TableJoueur
						 WHERE joueur_id='{$alVis[$a]}'";
	$rAlVis = mysql_query($qAlVis)or die(mysql_error());
	while($rangAlVis = mysql_fetch_assoc($rAlVis)) {
		$vecMatch[$IM]['alVis'][$a]=$rangAlVis;
    	 }
	}
	
		$qGDom= "SELECT joueur_id,NomJoueur,NumeroJoueur,position		
						FROM TableJoueur
						 WHERE joueur_id='{$r['gardienDom']}'";
	$rGDom = mysql_query($qGDom)or die(mysql_error());
	while($rangGDom = mysql_fetch_assoc($rGDom)) {
		$vecMatch[$IM]['gDom']=$rangGDom;
	}
		$qGVis= "SELECT joueur_id,NomJoueur,NumeroJoueur,position		
						FROM TableJoueur
						 WHERE joueur_id='{$r['gardienVis']}'";
	$rGVis = mysql_query($qGVis)or die(mysql_error());
	while($rangGVis = mysql_fetch_assoc($rGVis)) {
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
