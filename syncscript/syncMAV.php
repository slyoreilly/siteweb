
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

$mavId = $_POST['mavId'];
$ligueId = $_POST['ligueId'];
$vielledate =$_POST['vielledate'];

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
$strRetour.="yo";
$strRetour.=$mavId;
$qString="SELECT abonEquipeLigue.*,	TableMatch.* FROM MatchAVenir 
						JOIN abonEquipeLigue 
							ON (abonEquipeLigue.ligueId=MatchAVenir.ligueId)
						INNER JOIN TableMatch 
							ON (MatchAVenir.mavId=TableMatch.mavId)
							
						WHERE MatchAVenir.ligueId='{$ligueId}' 
							AND MatchAVenir.dernierMAJ>'{$vielledate}'
							AND abonEquipeLigue.finAbon>NOW()
							AND MatchAVenir.date>(NOW()-INTERVAL 1 DAY)
							AND MatchAVenir.date<(NOW()+INTERVAL 2 WEEK)
							AND (MatchAVenir.eqDom=abonEquipeLigue.equipeId OR MatchAVenir.eqVis=abonEquipeLigue.equipeId)
						GROUP BY TableMatch.mavId";
						
unset($retour);
$retour = mysql_query($qString) or die(mysql_error());	
//$strRetour.= mysql_num_rows($retour);
//$strRetour.="rege";

$vecMatch = array();
$Im=0;
while($r = mysql_fetch_array($retour)) {
    $vecMatch[]=$r;
	    $vecMatch[$Im]['nom']=$r['matchIdRef'];
	    $vecMatch[$Im]['matchId']=$r['match_id'];
		$vecMatch[$Im]['eqDom']=$r['eq_dom'];
		$vecMatch[$Im]['eqVis']=$r['eq_vis'];
    $Im++;
}
$adomper= stripslashes(json_encode($vecMatch));
$adomper= str_replace('"[','[',$adomper);
$adomper= str_replace(']"',']',$adomper);

if(count($vecMatch)!=0)
echo $adomper;
else {
	echo  "";
}


	//		header("HTTP/1.1 200 OK");
?>
