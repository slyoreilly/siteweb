<?php


$vdhr = date("Y/m/d H:i:s",$vielledateMAV);
$vdhr= str_replace('/','-',$vdhr);




// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");
	
	
//$jDom = json_decode($jDomJSON, true);
//$jVis = json_decode($jVisJSON, true);
// Retiré de la requête suivante: AND dernierMAJ>'{$vdhr}'
$qString="SELECT abonEquipeLigue.*, TableMatch.*	FROM TableMatch 
						JOIN abonEquipeLigue 
							ON (abonEquipeLigue.ligueId=TableMatch.ligueRef)
						WHERE TableMatch.ligueRef='{$ligueId}' 
							
							AND abonEquipeLigue.finAbon>NOW()
							AND TableMatch.date>(NOW()-INTERVAL 1 DAY)
							AND TableMatch.date<(NOW()+INTERVAL 7 DAY)
							AND (TableMatch.eq_dom=abonEquipeLigue.equipeId OR TableMatch.eq_vis=abonEquipeLigue.equipeId)
						GROUP BY mavId";
						
unset($retour);
$retour = mysqli_query($conn,$qString) or die(mysql_error());	
//$strRetour.= mysql_num_rows($retour);
//$strRetour.="rege";

$vecMatch = array();
$Im=0;
while($r = mysqli_fetch_array($retour,MYSQL_ASSOC)) {
    $vecMatch[$Im]=$r;
	$qAbon="SELECT * FROM abonAppareilMatch
			LEFT JOIN positionGabarits
				ON (abonAppareilMatch.posGabId=positionGabarits.posGabId)
			WHERE matchId = '{$r['match_id']}' ";
			$retAbon= mysqli_query($conn,$qAbon) or die(mysql_error());	
	while($rA = mysqli_fetch_array($retAbon,MYSQL_ASSOC)) {
    	$vecMatch[$Im]['abons'][]=$rA;		
	}
    $Im++;
}
//$adomper= stripslashes(json_encode($vecMatch));
//$adomper= str_replace('"[','[',$adomper);
//$adomper= str_replace(']"',']',$adomper);

$vecMAV2=$vecMatch;
$infoMav2 = $qString;



	//		header("HTTP/1.1 200 OK");
?>
