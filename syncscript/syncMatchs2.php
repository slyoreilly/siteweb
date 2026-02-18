<?php require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$username = $_POST['username'];
$password = $_POST['password'];

	

function parseMatchID($ID){
	 
//$monMatch['date'] = str_replace('/', '-', substr($ID,0,stripos($ID,'_')));
$i1=stripos($ID,'_');
$i2=stripos($ID,'_',$i1+1);
$i3=stripos($ID,'_',$i2+1);
$i4=stripos($ID,'_',$i3+1);
$monMatch['date'] = substr($ID,0,$i1);
$i1=stripos($ID,'_');

$longueur = strlen($monMatch['date']);
$monMatch['dom'] = substr($ID,$i1+1,$i2-$i1-1);
if($i3!=false)
	{$monMatch['vis'] = substr($ID,$i2+1,$i3-$i2-1);
	$monMatch['ligueId']=substr($ID,$i3+1);
	}
else{$monMatch['vis']= substr($ID,$i2+1);}
	return $monMatch;

} 


//echo $_POST['matchjson'];

$matchjson = stripslashes(stripslashes(stripslashes(mysqli_real_escape_string($conn,stripslashes($_POST['matchjson'])))));
$matchjson = str_replace('"{','{',$matchjson);
$matchjson = str_replace('}"','}',$matchjson);
$ligueId = $_POST['ligueId'];
//echo $matchjson;


//$json=json_decode("'".$matchjson."'");
$leMatch = json_decode($matchjson, true);




$IJ = 0;
$syncOK = array();
//		echo json_encode($leMatch)."//////";

$qRef="SELECT event_id FROM {$tableEvent} WHERE 1 ORDER BY event_id DESC LIMIT 0,1";
$rRef = mysqli_query($conn,$qRef) or die(mysqli_error($conn) . $qRef);
$vRef=mysqli_data_seek($rRef,0);

while ($IJ < count($leMatch)) {

	//	echo json_encode($leMatch[$IJ]).",";
	if (isset($leMatch[$IJ]['but'])) {
		$matchAEnr = parseMatchID($leMatch[$IJ]['but']['match_id']);
		if(isset($matchAEnr['ligueId']))
			{$ligueId=$matchAEnr['ligueId'];}

		switch($leMatch[$IJ]['but']['es']) {

			case 10 :
				$qDel = "DELETE FROM {$tableEvent} WHERE chrono='{$leMatch[$IJ]['but']['chrono']}' AND match_event_id='{$leMatch[$IJ]['but']['match_id']}'";
				mysqli_query($conn,$qDel) or die(mysqli_error($conn) . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 12 :
				
				$qInsM = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['but']['match_id']}','{$leMatch[$IJ]['but']['eqId']}','{$leMatch[$IJ]['but']['m']}','{$leMatch[$IJ]['but']['chrono']}','{$leMatch[$IJ]['but']['sc']}',0)";
				$qInsP1 = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['but']['match_id']}','{$leMatch[$IJ]['but']['eqId']}','{$leMatch[$IJ]['but']['p1']}','{$leMatch[$IJ]['but']['chrono']}','{$leMatch[$IJ]['but']['sc']}',1)";
				$qInsP2 = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['but']['match_id']}','{$leMatch[$IJ]['but']['eqId']}','{$leMatch[$IJ]['but']['p2']}','{$leMatch[$IJ]['but']['chrono']}','{$leMatch[$IJ]['but']['sc']}',1)";

				mysqli_query($conn,$qInsM) or die(mysqli_error($conn) . $qInsM);
				array_push($syncOK, $leMatch[$IJ]['but']['chrono']);

				if ($leMatch[$IJ]['but']['p1'] != 0) {mysqli_query($conn,$qInsP1) or die(mysqli_error($conn) . $qInsP1);
				}
				if ($leMatch[$IJ]['but']['p2'] != 0) {mysqli_query($conn,$qInsP2) or die(mysqli_error($conn) . $qInsP2);
				}
				$moins = json_decode($leMatch[$IJ]['but']['moins']);
				for($a=0;$a<count($leMatch[$IJ]['but']['plus']);$a++)
				{
						$qInsPlus="INSERT INTO PlusMoins (matchId,joueurId, equipeId, ligueId, plusMoins, chrono) VALUES ('{$leMatch[$IJ]['but']['match_id']}','{$leMatch[$IJ]['but']['plus'][$a]}','{$leMatch[$IJ]['but']['eqId']}','{$ligueId}',1,'{$leMatch[$IJ]['but']['chrono']}')";
						mysqli_query($conn,$qInsPlus) or die(mysqli_error($conn) . $qInsPlus);							
				}
				for($a=0;$a<count($leMatch[$IJ]['but']['moins']);$a++)
				{
						$qInsMoins="INSERT INTO PlusMoins (matchId ,joueurId, equipeId, ligueId, plusMoins, chrono) VALUES ('{$leMatch[$IJ]['but']['match_id']}','{$leMatch[$IJ]['but']['moins'][$a]}','{$leMatch[$IJ]['but']['advId']}','{$ligueId}',-1,'{$leMatch[$IJ]['but']['chrono']}')";
						mysqli_query($conn,$qInsMoins) or die(mysqli_error($conn) . $qInsMoins);							
				}
								
				break;
		}
		$qSelButs = "SELECT * FROM {$tableEvent} WHERE match_event_id='{$leMatch[$IJ]['but']['match_id']}' AND code=0 AND equipe_event_id='{$leMatch[$IJ]['but']['eqId']}'";
		$resButs = mysqli_query($conn,$qSelButs) or die(mysqli_error($conn) . $qSelButs);
		$score = mysqli_num_rows($resButs);
		$qSelEq = "SELECT eq_dom, eq_vis FROM TableMatch WHERE matchIdRef='{$leMatch[$IJ]['but']['match_id']}'";
		$resEq = mysqli_query($conn,$qSelEq) or die(mysqli_error($conn) . $qSelEq);
		$vecEq = mysqli_data_seek($resEq,0);
		$qUpMatch = "";
		if ($vecEq[0] == $leMatch[$IJ]['but']['eqId']) {$qUpMatch = "UPDATE TableMatch SET score_dom='{$score}' WHERE matchIdRef='{$leMatch[$IJ]['but']['match_id']}'";
		}
		if ($vecEq[1] == $leMatch[$IJ]['but']['eqId']) {$qUpMatch = "UPDATE TableMatch SET score_vis='{$score}' WHERE matchIdRef='{$leMatch[$IJ]['but']['match_id']}'";
		}
		if (strcmp($qUpMatch, "") != 0) {mysqli_query($conn,$qUpMatch) or die(mysqli_error($conn) . $qUpMatch);
		}
		//	$syncOK= "/".$leMatch[$IJ]['but']['eqId'];

	}
	if (isset($leMatch[$IJ]['punition'])) {
		$matchAEnr = parseMatchID($leMatch[$IJ]['punition']['match_id']);
		if(isset($matchAEnr['ligueId']))
			{$ligueId=$matchAEnr['ligueId'];}
		

		switch($leMatch[$IJ]['punition']['es']) {

			case 10 :
				$qDel = "DELETE FROM {$tableEvent} WHERE chrono='{$leMatch[$IJ]['punition']['chrono']}' AND match_event_id='{$leMatch[$IJ]['punition']['match_id']}'";
				mysqli_query($conn,$qDel) or die(mysqli_error($conn) . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 12 :
				$qInsM = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['punition']['match_id']}','{$leMatch[$IJ]['punition']['eqId']}','{$leMatch[$IJ]['punition']['joueur']}','{$leMatch[$IJ]['punition']['chrono']}','{$leMatch[$IJ]['punition']['sc']}',4)";

				mysqli_query($conn,$qInsM) or die(mysqli_error($conn) . $qInsM);
				array_push($syncOK, $leMatch[$IJ]['punition']['chrono']);
				break;
		}

	}
	if (isset($leMatch[$IJ]['fusillade'])) {
		$matchAEnr = parseMatchID($leMatch[$IJ]['fusillade']['match_id']);
		if(isset($matchAEnr['ligueId']))
			{$ligueId=$matchAEnr['ligueId'];}
		
		switch($leMatch[$IJ]['fusillade']['es']) {

			case 10 :
				$qDel = "DELETE FROM {$tableEvent} WHERE chrono='{$leMatch[$IJ]['fusillade']['chrono']}' AND match_event_id='{$leMatch[$IJ]['fusillade']['match_id']}'";
				mysqli_query($conn,$qDel) or die(mysqli_error($conn) . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 12 :
				$qInsM = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['fusillade']['match_id']}','{$leMatch[$IJ]['fusillade']['eqId']}','{$leMatch[$IJ]['fusillade']['joueur']}','{$leMatch[$IJ]['fusillade']['chrono']}','{$leMatch[$IJ]['fusillade']['sc']}',2)";

				mysqli_query($conn,$qInsM) or die(mysqli_error($conn) . $qInsM);
				array_push($syncOK, $leMatch[$IJ]['fusillade']['chrono']);
				break;
		}

	}

	if (isset($leMatch[$IJ]['periode'])) {
		$matchAEnr = parseMatchID($leMatch[$IJ]['periode']['match_id']);
		if(isset($matchAEnr['ligueId']))
			{$ligueId=$matchAEnr['ligueId'];}
		

		switch($leMatch[$IJ]['periode']['es']) {

			case 10 :
				$qDel = "DELETE FROM {$tableEvent} WHERE chrono='{$leMatch[$IJ]['periode']['chrono']}' AND match_event_id='{$leMatch[$IJ]['periode']['match_id']}'";
				mysqli_query($conn,$qDel) or die(mysqli_error($conn) . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 12 :
				$qInsM = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['periode']['match_id']}',0,0,'{$leMatch[$IJ]['periode']['chrono']}','{$leMatch[$IJ]['periode']['sc']}',11)";

				mysqli_query($conn,$qInsM) or die(mysqli_error($conn) . $qInsM);
				array_push($syncOK, $leMatch[$IJ]['periode']['chrono']);
				break;
		}

	}

	if (isset($leMatch[$IJ]['debutMatch'])) {
		$matchAEnr = parseMatchID($leMatch[$IJ]['debutMatch']['match_id']);
		if(isset($matchAEnr['ligueId']))
			{$ligueId=$matchAEnr['ligueId'];}
		
		switch($leMatch[$IJ]['debutMatch']['es']) {

			case 10 :
			//	$qDel = "DELETE FROM {$tableEvent} WHERE chrono=$leMatch[$IJ]['debutMatch']['chrono'] AND match_event_id='{$leMatch[$IJ]['debutMatch']['match_id']}'";
			//	mysql_query($qDel) or die(mysql_error() . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 12 :
				$code1010 = 10000 * $leMatch[$IJ]['debutMatch']['eqDom'] + $leMatch[$IJ]['debutMatch']['eqVis'];

				$qInsGDom = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['debutMatch']['match_id']}','{$leMatch[$IJ]['debutMatch']['eqDom']}','{$leMatch[$IJ]['debutMatch']['gDom']}','{$leMatch[$IJ]['debutMatch']['chrono']}',5,3)";
				$qInsGVis = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['debutMatch']['match_id']}','{$leMatch[$IJ]['debutMatch']['eqVis']}','{$leMatch[$IJ]['debutMatch']['gVis']}','{$leMatch[$IJ]['debutMatch']['chrono']}',5,3)";
				$qInsDM = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['debutMatch']['match_id']}'," . $code1010 . " ,'{$leMatch[$IJ]['debutMatch']['ligueId']}','{$leMatch[$IJ]['debutMatch']['chrono']}',0,10)";

				$cDom = 0;
				$cVis = 0;
				$cDef = 0;

				mysqli_query($conn,$qInsDM) or die(mysqli_error($conn) . $qInsDM);

				while ($cDom < count($leMatch[$IJ]['debutMatch']['alDom'])) {
					$qAlDom = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['debutMatch']['match_id']}','{$leMatch[$IJ]['debutMatch']['eqDom']}','{$leMatch[$IJ]['debutMatch']['alDom'][$cDom]}','{$leMatch[$IJ]['debutMatch']['chrono']}',0,3)";
					mysqli_query($conn,$qAlDom) or die(mysqli_error($conn) . $qAlDom);
					$cDom++;
				}
				while ($cVis < count($leMatch[$IJ]['debutMatch']['alVis'])) {
					$qAlVis = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['debutMatch']['match_id']}','{$leMatch[$IJ]['debutMatch']['eqVis']}','{$leMatch[$IJ]['debutMatch']['alVis'][$cVis]}','{$leMatch[$IJ]['debutMatch']['chrono']}',0,3)";
					mysqli_query($conn,$qAlVis) or die(mysqli_error($conn) . $qAlVis);
					$cVis++;
				}
				while ($cDef < count($leMatch[$IJ]['debutMatch']['alDef'])) {
					$qAlDef = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['debutMatch']['match_id']}','{$leMatch[$IJ]['debutMatch']['alDef'][$cDef]['eq']}','{$leMatch[$IJ]['debutMatch']['alDef'][$cDef]['joueur']}','{$leMatch[$IJ]['debutMatch']['chrono']}',0,3)";
					mysqli_query($conn,$qAlDef) or die(mysqli_error($conn) . $qAlDef);
					$cDef++;
				}
				if ($leMatch[$IJ]['debutMatch']['gDom'] != 0) {mysqli_query($conn,$qInsGDom) or die(mysqli_error($conn) . $qInsGDom);
				}
				if ($leMatch[$IJ]['debutMatch']['gVis'] != 0) {mysqli_query($conn,$qInsGVis) or die(mysqli_error($conn) . $qInsGVis);
				}

				array_push($syncOK, $leMatch[$IJ]['debutMatch']['chrono']);

				break;
		}

	}

	if (isset($leMatch[$IJ]['finMatch'])) {
		$matchAEnr = parseMatchID($leMatch[$IJ]['finMatch']['match_id']);
		if(isset($matchAEnr['ligueId']))
			{$ligueId=$matchAEnr['ligueId'];}
		
		switch($leMatch[$IJ]['finMatch']['es']) {

			case 12 :
				$qInsFM = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['finMatch']['match_id']}',
				0,0,'{$leMatch[$IJ]['finMatch']['chrono']}',10,10)";
								mysqli_query($conn,$qInsFM) or die(mysqli_error($conn) . $qInsFM);
			////////////  NO BREAK!!!  //////////////////////
			case 10 :
			default:
				//echo "1";
						$deSyncMatch = 1;
							/*?><?php*/ 
							//include('/public_html/scriptsphp/calculeMatch.php');
							/*?><?php*/
						$qMatch = "SELECT cleValeur FROM TableMatch WHERE matchIdRef = '{$leMatch[$IJ]['finMatch']['match_id']}'";
										$testmatch = mysqli_query($conn,$qMatch) or die(mysqli_error($conn) . " Select " . $leMatch[$IJ]['finMatch']['db_id']);
				//echo "2";
						if(mysqli_num_rows($testmatch)==0)
						{
				//echo "3";
							include('/public_html/scriptsphp/actualiseMatch.php');			
				//			include($_SERVER['DOCUMENT_ROOT'] . '/scriptsphp/actualiseMatch.php');			
							$qMatch = "SELECT cleValeur FROM TableMatch WHERE matchIdRef = '{$leMatch[$IJ]['finMatch']['match_id']}'";
							$testmatch = mysqli_query($conn,$qMatch) or die(mysqli_error($conn) . " Select " . $leMatch[$IJ]['finMatch']['db_id']);
				//echo "4";
													}
						$rMatch = mysqli_data_seek($testmatch,0);

						if (($rMatch[0] != NULL) && (strlen($rMatch[0]) > 2)) {
							$condMatch = $rMatch[0];
 							$jMerge = json_encode(array_merge((array) json_decode($condMatch), (array) json_decode($leMatch[$IJ]['finMatch']['cv'])));
							} else {
//							$jMerge = $leMatch['cleValeur'];
								$jMerge = json_encode($leMatch[$IJ]['finMatch']['cv']);
						}
										//echo "5";
												mysqli_query($conn,"UPDATE TableMatch SET cleValeur='{$jMerge}' WHERE matchIdRef = '{$leMatch[$IJ]['finMatch']['match_id']}'") or die(mysqli_error($conn) );
												$monCV = json_decode($leMatch[$IJ]['finMatch']['cv']);
	//											echo $monCV['scoreFinal']." ".isset($monCV['scoreFinal'])." ".$leMatch[$IJ]['finMatch']['cv'];
										//echo "6";
												if(isset($leMatch[$IJ]['finMatch']['cv']['scoreFinal']))
												{
													
													$i1=stripos($leMatch[$IJ]['finMatch']['cv']['scoreFinal'],'-');
													$i2=stripos($leMatch[$IJ]['finMatch']['cv']['scoreFinal'],'-',$i1+1);
													
													$sD = substr($leMatch[$IJ]['finMatch']['cv']['scoreFinal'],0,$i1);
													$sV = substr($leMatch[$IJ]['finMatch']['cv']['scoreFinal'],$i1+1);
												mysqly_query($conn,"UPDATE TableMatch SET score_dom='{$sD}',score_vis='{$sV}' WHERE matchIdRef = '{$leMatch[$IJ]['finMatch']['match_id']}'") or die(mysqli_error($conn) );
												}
						array_push($syncOK, $leMatch[$IJ]['finMatch']['chrono']);
				}
			}

	$IJ++;
//echo json_encode($syncOK);
}
//echo json_encode($syncOK);

$qFin="SELECT * FROM {$tableEvent} WHERE event_id>{$vRef[0]} ORDER BY event_id DESC";
$rFin = mysqli_query($conn,$qFin) or die(mysqli_error($conn) . $qFin);
//echo $qFin;
while($vFin = mysqli_fetch_array($rFin))
{
	$qCheck="SELECT * FROM {$tableEvent} WHERE event_id<>{$vFin['event_id']}
											AND match_event_id='{$vFin['match_event_id']}'
											AND equipe_event_id='{$vFin['equipe_event_id']}'
											AND joueur_event_ref='{$vFin['joueur_event_ref']}'
											AND code={$vFin['code']}
											AND souscode={$vFin['souscode']}
											AND chrono='{$vFin['chrono']}'
											";

		$rCheck =mysqli_query($conn,$qCheck) or die(mysqli_error($conn) . $qCheck);			
		
		$nCheck=mysqli_num_rows($rCheck); 	
//		echo $nCheck.'/n';						
	if($nCheck>0)
	{
		while($vCheck = mysqli_fetch_array($rCheck))
		{
		$qDel2 = "DELETE FROM {$tableEvent} WHERE event_id={$vCheck['event_id']} ";
				mysqli_query($conn,$qDel2) or die(mysqli_error($conn) . $qDel2);
//				echo $qDel2.'\n';
		}
	}
}	

echo json_encode($syncOK);

//mysqli_close($conn);

//.$matchjson.json_encode($leMatch);
$deSyncMatch = 1;
							include('/public_html/scriptsphp/calculeMatch.php');
//include ($_SERVER['DOCUMENT_ROOT'] . '/scriptsphp/calculeMatch.php');

header("HTTP/1.1 200 OK");
?>
