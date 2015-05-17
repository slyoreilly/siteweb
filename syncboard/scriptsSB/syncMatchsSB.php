<?php $db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';
$db910 = 'syncsta1_910';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';


$lien900=false;
$lien900=mysql_connect($db_host, $db_user, $db_pwd,true);
if (!$lien900)
	die("Can't connect to database");

if (!mysql_select_db($database,$lien900)) {
	echo "<h1>Database: {$database}</h1>";
	echo "<h1>Table: {$table}</h1>";
	die("Can't select database");
}
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");


function parseMatchID($ID){
	 
//$monMatch['date'] = str_replace('/', '-', substr($ID,0,stripos($ID,'_')));
$i1=stripos($ID,'_');
$i2=stripos($ID,'_',$i1+1);
$i3=stripos($ID,'_',$i2+1);
$monMatch['date'] = substr($ID,0,$i1);
$i1=stripos($ID,'_');

$longueur = strlen($monMatch['date']);
$monMatch['dom'] = substr($ID,$i1+1,$i2-$i1-1);
if($i3!=false)
	$monMatch['vis'] = substr($ID,$i2+1,$i3-$i2-1);
else{$monMatch['vis']= substr($ID,$i2+1);}
	return $monMatch;
} 


//echo $_POST['matchjson'];
$username = $_POST['username'];
$password = $_POST['password'];
$heure = $_POST['heure'];

$matchjson = stripslashes(stripslashes(stripslashes(mysql_real_escape_string(stripslashes($_POST['matchjson'])))));
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
$rRef = mysql_query($qRef) or die(mysql_error() . $qRef);
$vRef = mysql_fetch_row($rRef);

while ($IJ < count($leMatch)) {

	//	echo json_encode($leMatch[$IJ]).",";
	if (isset($leMatch[$IJ]['but'])) {
		$matchAEnr = parseMatchID($leMatch[$IJ]['but']['match_id']);
		if(isset($matchAEnr['ligueId']))
			{$ligueId=$matchAEnr['ligueId'];}
		$retBut=$leMatch[$IJ]['but']['chrono']; // retourner le but, sans correction de chrono.
		if(isset($heure))
			{$leMatch[$IJ]['but']['chrono']=$leMatch[$IJ]['but']['chrono']+time()*1000-$heure;}
	
		switch($leMatch[$IJ]['but']['es']) {

			case 10 :
				$qDel = "DELETE FROM {$tableEvent} WHERE chrono='{$leMatch[$IJ]['but']['chrono']}' AND match_event_id='{$leMatch[$IJ]['but']['match_id']}'";
				mysql_query($qDel) or die(mysql_error() . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 12 :
				
				$qInsM = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['but']['match_id']}','{$leMatch[$IJ]['but']['eqId']}','{$leMatch[$IJ]['but']['m']}','{$leMatch[$IJ]['but']['chrono']}','{$leMatch[$IJ]['but']['sc']}',0)";
				$qInsP1 = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['but']['match_id']}','{$leMatch[$IJ]['but']['eqId']}','{$leMatch[$IJ]['but']['p1']}','{$leMatch[$IJ]['but']['chrono']}','{$leMatch[$IJ]['but']['sc']}',1)";
				$qInsP2 = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['but']['match_id']}','{$leMatch[$IJ]['but']['eqId']}','{$leMatch[$IJ]['but']['p2']}','{$leMatch[$IJ]['but']['chrono']}','{$leMatch[$IJ]['but']['sc']}',1)";

				mysql_query($qInsM) or die(mysql_error() . $qInsM);
				array_push($syncOK, $retBut);

				if ($leMatch[$IJ]['but']['p1'] != 0) {mysql_query($qInsP1) or die(mysql_error() . $qInsP1);
				}
				if ($leMatch[$IJ]['but']['p2'] != 0) {mysql_query($qInsP2) or die(mysql_error() . $qInsP2);
				}
				break;
		}
		$qSelButs = "SELECT * FROM {$tableEvent} WHERE match_event_id='{$leMatch[$IJ]['but']['match_id']}' AND code=0 AND equipe_event_id='{$leMatch[$IJ]['but']['eqId']}'";
		$resButs = mysql_query($qSelButs) or die(mysql_error() . $qSelButs);
		$score = mysql_num_rows($resButs);
		$qSelEq = "SELECT eq_dom, eq_vis FROM TableMatch WHERE matchIdRef='{$leMatch[$IJ]['but']['match_id']}'";
		$resEq = mysql_query($qSelEq) or die(mysql_error() . $qSelEq);
		$vecEq = mysql_fetch_row($resEq);
		$qUpMatch = "";
		if ($vecEq[0] == $leMatch[$IJ]['but']['eqId']) {$qUpMatch = "UPDATE TableMatch SET score_dom='{$score}' WHERE matchIdRef='{$leMatch[$IJ]['but']['match_id']}'";
		}
		if ($vecEq[1] == $leMatch[$IJ]['but']['eqId']) {$qUpMatch = "UPDATE TableMatch SET score_vis='{$score}' WHERE matchIdRef='{$leMatch[$IJ]['but']['match_id']}'";
		}
		if (strcmp($qUpMatch, "") != 0) {mysql_query($qUpMatch) or die(mysql_error() . $qUpMatch);
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
				mysql_query($qDel) or die(mysql_error() . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 12 :
				$qInsM = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['punition']['match_id']}','{$leMatch[$IJ]['punition']['eqId']}','{$leMatch[$IJ]['punition']['joueur']}','{$leMatch[$IJ]['punition']['chrono']}','{$leMatch[$IJ]['punition']['sc']}',4)";

				mysql_query($qInsM) or die(mysql_error() . $qInsM);
				array_push($syncOK, $leMatch[$IJ]['punition']['chrono']);
				break;
		}
		mysql_select_db($db910,$lien900);
		
		switch($leMatch[$IJ]['punition']['es']) {

			case 10 :
				$qDel = "DELETE FROM punitions WHERE chrono='{$leMatch[$IJ]['punition']['chrono']}' AND matchId='{$leMatch[$IJ]['punition']['match_id']}'";
				mysql_query($qDel) or die(mysql_error() . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 12 :
				$qInsM = "INSERT INTO punitions (matchId, equipeId,joueurId,chrono,motif,duree,active) VALUES ('{$leMatch[$IJ]['punition']['match_id']}','{$leMatch[$IJ]['punition']['eqId']}','{$leMatch[$IJ]['punition']['joueur']}','{$leMatch[$IJ]['punition']['chrono']}','{$leMatch[$IJ]['punition']['sc']}',3,1)";

				mysql_query($qInsM) or die(mysql_error() . $qInsM);
				array_push($syncOK, $leMatch[$IJ]['punition']['chrono']);
				break;
		}

		
	mysql_select_db($database,$lien900);
		
	}
	if (isset($leMatch[$IJ]['fusillade'])) {
		$matchAEnr = parseMatchID($leMatch[$IJ]['fusillade']['match_id']);
		if(isset($matchAEnr['ligueId']))
			{$ligueId=$matchAEnr['ligueId'];}
		
		switch($leMatch[$IJ]['fusillade']['es']) {

			case 10 :
				$qDel = "DELETE FROM {$tableEvent} WHERE chrono='{$leMatch[$IJ]['fusillade']['chrono']}' AND match_event_id='{$leMatch[$IJ]['fusillade']['match_id']}'";
				mysql_query($qDel) or die(mysql_error() . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 12 :
				$qInsM = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['fusillade']['match_id']}','{$leMatch[$IJ]['fusillade']['eqId']}','{$leMatch[$IJ]['fusillade']['joueur']}','{$leMatch[$IJ]['fusillade']['chrono']}','{$leMatch[$IJ]['fusillade']['sc']}',2)";

				mysql_query($qInsM) or die(mysql_error() . $qInsM);
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
				mysql_query($qDel) or die(mysql_error() . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 12 :
				$qInsM = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['periode']['match_id']}',0,0,'{$leMatch[$IJ]['periode']['chrono']}','{$leMatch[$IJ]['periode']['sc']}',11)";

				mysql_query($qInsM) or die(mysql_error() . $qInsM);
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

				mysql_query($qInsDM) or die(mysql_error() . $qInsDM);

				while ($cDom < count($leMatch[$IJ]['debutMatch']['alDom'])) {
					$qAlDom = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['debutMatch']['match_id']}','{$leMatch[$IJ]['debutMatch']['eqDom']}','{$leMatch[$IJ]['debutMatch']['alDom'][$cDom]}','{$leMatch[$IJ]['debutMatch']['chrono']}',0,3)";
					mysql_query($qAlDom) or die(mysql_error() . $qAlDom);
					$cDom++;
				}
				while ($cVis < count($leMatch[$IJ]['debutMatch']['alVis'])) {
					$qAlVis = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['debutMatch']['match_id']}','{$leMatch[$IJ]['debutMatch']['eqVis']}','{$leMatch[$IJ]['debutMatch']['alVis'][$cVis]}','{$leMatch[$IJ]['debutMatch']['chrono']}',0,3)";
					mysql_query($qAlVis) or die(mysql_error() . $qAlVis);
					$cVis++;
				}
				while ($cDef < count($leMatch[$IJ]['debutMatch']['alDef'])) {
					$qAlDef = "INSERT INTO {$tableEvent} (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$leMatch[$IJ]['debutMatch']['match_id']}','{$leMatch[$IJ]['debutMatch']['alDef'][$cDef]['eq']}','{$leMatch[$IJ]['debutMatch']['alDef'][$cDef]['joueur']}','{$leMatch[$IJ]['debutMatch']['chrono']}',0,3)";
					mysql_query($qAlDef) or die(mysql_error() . $qAlDef);
					$cDef++;
				}
				if ($leMatch[$IJ]['debutMatch']['gDom'] != 0) {mysql_query($qInsGDom) or die(mysql_error() . $qInsGDom);
				}
				if ($leMatch[$IJ]['debutMatch']['gVis'] != 0) {mysql_query($qInsGVis) or die(mysql_error() . $qInsGVis);
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
								mysql_query($qInsFM) or die(mysql_error() . $qInsFM);
			////////////  NO BREAK!!!  //////////////////////
			case 10 :
			default:
				//echo "1";
						$deSyncMatch = 1;
							/*?><?php*/ 
							//include('/public_html/scriptsphp/calculeMatch.php');
							/*?><?php*/
						$qMatch = "SELECT cleValeur FROM TableMatch WHERE matchIdRef = '{$leMatch[$IJ]['finMatch']['match_id']}'";
										$testmatch = mysql_query($qMatch) or die(mysql_error() . " Select " . $leMatch[$IJ]['finMatch']['db_id']);
				//echo "2";
						if(mysql_num_rows($testmatch)==0)
						{
				//echo "3";
							include('/public_html/scriptsphp/actualiseMatch.php');			
				//			include($_SERVER['DOCUMENT_ROOT'] . '/scriptsphp/actualiseMatch.php');			
							$qMatch = "SELECT cleValeur FROM TableMatch WHERE matchIdRef = '{$leMatch[$IJ]['finMatch']['match_id']}'";
							$testmatch = mysql_query($qMatch) or die(mysql_error() . " Select " . $leMatch[$IJ]['finMatch']['db_id']);
				//echo "4";
													}
						$rMatch = mysql_fetch_row($testmatch);
						if (($rMatch[0] != NULL) && (strlen($rMatch[0]) > 2)) {
							$condMatch = $rMatch[0];
 							$jMerge = json_encode(array_merge((array) json_decode($condMatch), (array) json_decode($leMatch[$IJ]['finMatch']['cv'])));
							} else {
							$jMerge = $leMatch['cleValeur'];
						}
										//echo "5";
												mysql_query("UPDATE TableMatch SET cleValeur='{$jMerge}' WHERE matchIdRef = '{$leMatch[$IJ]['finMatch']['match_id']}'") or die(mysql_error() );
												$monCV = json_decode($leMatch[$IJ]['finMatch']['cv']);
	//											echo $monCV['scoreFinal']." ".isset($monCV['scoreFinal'])." ".$leMatch[$IJ]['finMatch']['cv'];
										//echo "6";
												if(isset($leMatch[$IJ]['finMatch']['cv']['scoreFinal']))
												{
													
													$i1=stripos($leMatch[$IJ]['finMatch']['cv']['scoreFinal'],'-');
													$i2=stripos($leMatch[$IJ]['finMatch']['cv']['scoreFinal'],'-',$i1+1);
													
													$sD = substr($leMatch[$IJ]['finMatch']['cv']['scoreFinal'],0,$i1);
													$sV = substr($leMatch[$IJ]['finMatch']['cv']['scoreFinal'],$i1+1);
												mysql_query("UPDATE TableMatch SET score_dom='{$sD}',score_vis='{$sV}' WHERE matchIdRef = '{$leMatch[$IJ]['finMatch']['match_id']}'") or die(mysql_error() );
												}
						array_push($syncOK, $leMatch[$IJ]['finMatch']['chrono']);
				}
			}

	$IJ++;
//echo json_encode($syncOK);
}
//echo json_encode($syncOK);

$qFin="SELECT * FROM {$tableEvent} WHERE event_id>{$vRef[0]} ORDER BY event_id DESC";
$rFin = mysql_query($qFin) or die(mysql_error() . $qFin);
//echo $qFin;
while($vFin = mysql_fetch_array($rFin))
{
	$qCheck="SELECT * FROM {$tableEvent} WHERE event_id<>{$vFin['event_id']}
											AND match_event_id='{$vFin['match_event_id']}'
											AND equipe_event_id='{$vFin['equipe_event_id']}'
											AND joueur_event_ref='{$vFin['joueur_event_ref']}'
											AND code={$vFin['code']}
											AND souscode={$vFin['souscode']}
											AND chrono='{$vFin['chrono']}'
											";

		$rCheck =mysql_query($qCheck) or die(mysql_error() . $qCheck);			
		
		$nCheck=mysql_num_rows($rCheck); 	
//		echo $nCheck.'/n';						
	if($nCheck>0)
	{
		while($vCheck = mysql_fetch_array($rCheck))
		{
		$qDel2 = "DELETE FROM {$tableEvent} WHERE event_id={$vCheck['event_id']} ";
				mysql_query($qDel2) or die(mysql_error() . $qDel2);
//				echo $qDel2.'\n';
		}
	}
}	

echo json_encode($syncOK);
//.$matchjson.json_encode($leMatch);
$deSyncMatch = 1;
							include('/public_html/scriptsphp/calculeMatch.php');
//include ($_SERVER['DOCUMENT_ROOT'] . '/scriptsphp/calculeMatch.php');

header("HTTP/1.1 200 OK");
?>
