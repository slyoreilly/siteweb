<?php



// Create connection
$connDV = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$connDV) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($connDV,"SET NAMES 'utf8'");
mysqli_query($connDV,"SET CHARACTER SET 'utf8'");


$vdhr = date("Y/m/d H:i:s",$vielledateMAV);
$vdhr= str_replace('/','-',$vdhr);
	
	
//$jDom = json_decode($jDomJSON, true);
//$jVis = json_decode($jVisJSON, true);
// Retiré de la requête suivante: AND dernierMAJ>'{$vdhr}'
$qString="SELECT abonEquipeLigue.*, MatchAVenir.*	FROM MatchAVenir 
						JOIN abonEquipeLigue 
							ON (abonEquipeLigue.ligueId=MatchAVenir.ligueId)
						WHERE MatchAVenir.ligueId='{$ligueId}' 
							
							AND abonEquipeLigue.finAbon>NOW()
							AND MatchAVenir.date>(NOW()-INTERVAL 1 DAY)
							AND MatchAVenir.date<(NOW()+INTERVAL 2 WEEK)
							AND (MatchAVenir.eqDom=abonEquipeLigue.equipeId OR MatchAVenir.eqVis=abonEquipeLigue.equipeId)
						GROUP BY mavId";
						
unset($retour);
$retour = mysqli_query($connDV, $qString) or die(mysqli_error($connDV));	
//$strRetour.= mysql_num_rows($retour);
//$strRetour.="rege";

$vecMatch = array();
$Im=0;
while($r = mysqli_fetch_array($retour,MYSQL_ASSOC)) {
    $vecMatch[]=$r;
    $Im++;
}
//$adomper= stripslashes(json_encode($vecMatch));
//$adomper= str_replace('"[','[',$adomper);
//$adomper= str_replace(']"',']',$adomper);

$vecMAV=$vecMatch;
$infoMav = $qString;

mysqli_close($connDV);

	//		header("HTTP/1.1 200 OK");
?>
