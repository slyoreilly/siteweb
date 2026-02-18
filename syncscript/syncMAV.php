
<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$ligueId = 0;
if(isset($_POST['ligueId'])){
$ligueId = $_POST['ligueId'];
}
//if(isset($_POST['vielledate'])){
//$vielledate =$_POST['vielledate'];
//}

$qString="SELECT /*abonEquipeLigue.*,	*/TableMatch.* FROM TableMatch 
						JOIN abonEquipeLigue 
							ON (abonEquipeLigue.ligueId=TableMatch.ligueRef)
						WHERE TableMatch.ligueRef='{$ligueId}' 
							AND abonEquipeLigue.finAbon>NOW()
							AND TableMatch.date>(NOW()-INTERVAL 7 DAY)
							AND TableMatch.date<(NOW()+INTERVAL 2 WEEK)
							AND (TableMatch.eq_dom=abonEquipeLigue.equipeId OR TableMatch.eq_vis=abonEquipeLigue.equipeId)
						GROUP BY TableMatch.match_id";
						//AND TableMatch.TSDMAJ>'{$vielledate}'  retiré 15/01/2019
unset($retour);
$retour = mysqli_query($conn,$qString) or die(mysqli_error($conn));	


$vecMatch = array();
$Im=0;
while($r = mysqli_fetch_assoc($retour)) {
    $vecMatch[]=$r;
	    $vecMatch[$Im]['nom']=$r['matchIdRef'];
	    $vecMatch[$Im]['matchId']=$r['match_id'];
		$vecMatch[$Im]['eqDom']=$r['eq_dom'];
		$vecMatch[$Im]['eqVis']=$r['eq_vis'];
		$qAbon="SELECT abonAppMatchId, matchId, surfaceId, positionGabarits.gabaritId, positionGabarits.posGabId, abonAppareilMatch.role as role, telId, abonAppareilMatch.dernierMAJ, posX, posY FROM abonAppareilMatch
		LEFT JOIN positionGabarits
			ON (abonAppareilMatch.posGabId=positionGabarits.posGabId)
		WHERE matchId = '{$r['match_id']}' ";
		$retAbon= mysqli_query($conn,$qAbon) or die(mysqli_error($conn));	
while($rA = mysqli_fetch_array($retAbon,MYSQLI_ASSOC)) {
	$vecMatch[$Im]['abons'][]=$rA;		
}

    $Im++;
}
$adomper= stripslashes(json_encode($vecMatch));
$adomper= str_replace('"[','[',$adomper);
$adomper= str_replace(']"',']',$adomper);
$adomper= str_replace('"{','{',$adomper);
$adomper= str_replace('}"','}',$adomper);
if(count($vecMatch)!=0)
echo $adomper;
else {
	echo  "";
}

//mysqli_close($conn);
	//		header("HTTP/1.1 200 OK");
?>

