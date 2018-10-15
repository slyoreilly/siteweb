<?php

include_once ($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR . "syncstatsconfig.php");
require("../scriptsphp/calculeMatch2.php");  /// N'appelle rien, défini seulement la fonction
											 /// CalculeMatch(ligueId);

function parseMatchID($ID) {

	//$monMatch['date'] = str_replace('/', '-', substr($ID,0,stripos($ID,'_')));
	$i1 = stripos($ID, '_');
	$i2 = stripos($ID, '_', $i1 + 1);
	$i3 = stripos($ID, '_', $i2 + 1);
	$i4 = stripos($ID, '_', $i3 + 1);
	$monMatch['date'] = substr($ID, 0, $i1);
	$i1 = stripos($ID, '_');

	$longueur = strlen($monMatch['date']);
	$monMatch['dom'] = substr($ID, $i1 + 1, $i2 - $i1 - 1);
	if ($i3 != false) {$monMatch['vis'] = substr($ID, $i2 + 1, $i3 - $i2 - 1);
		$monMatch['ligueId'] = substr($ID, $i3 + 1);
	} else {$monMatch['vis'] = substr($ID, $i2 + 1);
	}
	return $monMatch;

}

$IJ = 0;
//global $syncOK;

//		echo json_encode($leMatch)."//////";

$qRef = "SELECT event_id FROM TableEvenement0 WHERE 1 ORDER BY event_id DESC LIMIT 0,1";
$rRef = mysql_query($qRef) or die(mysql_error() . $qRef);
$vRef = mysql_fetch_row($rRef);

if (strcmp(TYPE_TERMINAL, 'syncboard') == 0) {
	function f_es($source_es) {
		return floor($source_es / 100);
	}

} else {
	function f_es($source_es) {
		return $source_es % 100;
	}

}



	$extra['DM']=3;
	$memNoMatchId=0;
	$noMatchId=0;
foreach ($leMatch as $evenement) {
	
	
	// Regarder si on a un nouveau match. Si oui (et que ce n'est pas le premier), calculer l'ancien.
	if($memNoMatchId!=$noMatchId){
					$url = 'http://syncstats.com/scriptsphp/calculeUnMatch.php';
					$data = array('noMatchId' => $memNoMatchId);

					// use key 'http' even if you send the request to https://...
					$options = array(
    					'http' => array(
        				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        				'method'  => 'POST',
        				'content' => http_build_query($data)
    					)
					);
					$context  = stream_context_create($options);
					$result = file_get_contents($url, false, $context);
					if ($result === FALSE) { /* Handle error */ }
					$memNoMatchId=$noMatchId;
	}
//$message = "Seq evenement: ".$evenement['type'].PHP_EOL;
	//$log  = $message;
		//					file_put_contents('../test/logTest.txt', $log, FILE_APPEND);	
	//	echo json_encode($evenement).",";
	if (!strcmp($evenement['type'],'but')) {
				//	$message = "Execution de syncscript/dechargeMatch_2.";
				//			$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        	//			"------- Log de Buts".PHP_EOL;
				//			file_put_contents('../test/logTest.txt', $log, FILE_APPEND);	
		
		$matchAEnr = parseMatchID($evenement['match_id']);
		$trouveBut = 0;
		if (isset($matchAEnr['ligueId'])) {$ligueId = $matchAEnr['ligueId'];
		}
		if (isset($evenement['noseq'])) {$noseq = $evenement['noseq'];
		}else{$noseq=0;}

		$retBut = $evenement['chrono'];
		// retourner le but, sans correction de chrono.

		if (isset($heure)) {$evenement['chrono'] = $evenement['chrono'] + $heureServeur - $heure;
		}

		switch(f_es($evenement['es'])) {

			case 10 :
				$qDel = "DELETE FROM TableEvenement0 WHERE chrono='{$evenement['chrono']}' AND match_event_id='{$evenement['match_id']}'";
				mysql_query($qDel) or die(mysql_error() . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 15 :
			
				$qSelButs = "SELECT * FROM TableEvenement0 WHERE match_event_id='{$evenement['match_id']}' AND code=0 AND noSequence={$evenement['noseq']}";
				$resButs = mysql_query($qSelButs) or die(mysql_error() . $qSelButs);
				$trouveBut = mysql_num_rows($resButs);

			//	break;		 NO BREAK!!!!!!!
			case 12 :
				if ($trouveBut == 0) {
					$qInsM = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code,noSequence) VALUES ('{$evenement['match_id']}','{$evenement['eqId']}','{$evenement['m']}','{$evenement['chrono']}','{$evenement['sc']}',0,'{$noseq}')";
					$qInsP1 = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code,noSequence) VALUES ('{$evenement['match_id']}','{$evenement['eqId']}','{$evenement['p1']}','{$evenement['chrono']}','{$evenement['sc']}',1,'{$noseq}')";
					$qInsP2 = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code,noSequence) VALUES ('{$evenement['match_id']}','{$evenement['eqId']}','{$evenement['p2']}','{$evenement['chrono']}','{$evenement['sc']}',1,'{$noseq}')";
					mysql_query($qInsM) or die(mysql_error() . $qInsM);

					if ($evenement['p1'] != 0) {mysql_query($qInsP1) or die(mysql_error() . $qInsP1);
					}
					if ($evenement['p2'] != 0) {mysql_query($qInsP2) or die(mysql_error() . $qInsP2);
					}
					$moins = $evenement['moins'];
					for ($a = 0; $a < count($evenement['plus']); $a++) {
						$qInsPlus = "INSERT INTO PlusMoins (matchId,joueurId, equipeId, ligueId, plusMoins, chrono) VALUES ('{$evenement['match_id']}','{$evenement['plus'][$a]}','{$evenement['eqId']}','{$ligueId}',1,'{$evenement['chrono']}')";
						mysql_query($qInsPlus) or die(mysql_error() . $qInsPlus);
					}
					for ($a = 0; $a < count($evenement['moins']); $a++) {
						$qInsMoins = "INSERT INTO PlusMoins (matchId ,joueurId, equipeId, ligueId, plusMoins, chrono) VALUES ('{$evenement['match_id']}','{$evenement['moins'][$a]}','{$evenement['advId']}','{$ligueId}',-1,'{$evenement['chrono']}')";
						mysql_query($qInsMoins) or die(mysql_error() . $qInsMoins);
					}
				}
				array_push($syncOK, $retBut);

				break;
		}
		$qSelButs = "SELECT * FROM TableEvenement0 WHERE match_event_id='{$evenement['match_id']}' AND code=0 AND equipe_event_id='{$evenement['eqId']}'";
		$resButs = mysql_query($qSelButs) or die(mysql_error() . $qSelButs);
		$score = mysql_num_rows($resButs);
		$qSelEq = "SELECT eq_dom, eq_vis, match_id FROM TableMatch WHERE matchIdRef='{$evenement['match_id']}'";
		$resEq = mysql_query($qSelEq) or die(mysql_error() . $qSelEq);
		$vecEq = mysql_fetch_row($resEq);
		$qUpMatch = "";
		if ($vecEq[0] == $evenement['eqId']) {$qUpMatch = "UPDATE TableMatch SET score_dom='{$score}' WHERE matchIdRef='{$evenement['match_id']}'";
		}
		if ($vecEq[1] == $evenement['eqId']) {$qUpMatch = "UPDATE TableMatch SET score_vis='{$score}' WHERE matchIdRef='{$evenement['match_id']}'";
		}
		if (strcmp($qUpMatch, "") != 0) {mysql_query($qUpMatch) or die(mysql_error() . $qUpMatch);
		}
		$noMatchId=$vecEq[2];
		//	$syncOK= "/".$evenement['eqId'];

	}
	if (!strcmp($evenement['type'],'punition')) {
		$matchAEnr = parseMatchID($evenement['match_id']);
		if (isset($matchAEnr['ligueId'])) {$ligueId = $matchAEnr['ligueId'];
		}
		if (isset($evenement['noseq'])) {$noseq = $evenement['noseq'];
		}else{$noseq=0;}
		
		$trouvePun=0;

		switch(f_es($evenement['es'])) {

			case 10 :
				$qDel = "DELETE FROM TableEvenement0 WHERE chrono='{$evenement['chrono']}' AND match_event_id='{$evenement['match_id']}'";
				mysql_query($qDel) or die(mysql_error() . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 15 :
			
				$qSelPun = "SELECT * FROM TableEvenement0 WHERE match_event_id='{$evenement['match_id']}' AND code=4 AND noSequence={$evenement['noseq']}";
				$resPun = mysql_query($qSelPun) or die(mysql_error() . $qSelPun);
				$trouvePun = mysql_num_rows($resPun);

			//	break;		 NO BREAK!!!!!!!
			case 12 :
				if ($trouvePun == 0) {
				$qInsM = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code,noSequence) VALUES ('{$evenement['match_id']}','{$evenement['eqId']}','{$evenement['joueur']}','{$evenement['chrono']}','{$evenement['sc']}',4,'{$noseq}')";

				mysql_query($qInsM) or die(mysql_error() . $qInsM);
				}
				
				/////   Pour DB Syncboard
			$db_host="localhost";
			$db_user="syncsta1_u01";
			$db_pwd="test";

			$database = 'syncsta1_910';

			$connSB = mysqli_connect($db_host, $db_user, $db_pwd, $database);
			// Check connection/
			if (!$connSB) {
				   die("Connection failed: " . mysqli_connect_error());
			}
			
//			$maDate=date('Y-m-d H:i:s', $evenement['chrono']/1000);

			mysqli_query($connSB,"SET NAMES 'utf8'");
			mysqli_query($connSB,"SET CHARACTER SET 'utf8'");
			$qInsSB = "INSERT INTO punitions (matchId,motif, joueurId,equipeId,chrono,duree,active) VALUES ('{$evenement['match_id']}','{$evenement['sc']}','{$evenement['joueur']}','{$evenement['eqId']}','{$evenement['chrono']}','3','1')";

				mysqli_query($connSB,$qInsSB) or die(mysql_error($connSB) . $qInsSB);
				
				
				
				array_push($syncOK, $evenement['chrono']);
				break;
		}
				



	}
	if (!strcmp($evenement['type'],'clip')) {
		$matchAEnr = parseMatchID($evenement['match_id']);
		if (isset($matchAEnr['ligueId'])) {$ligueId = $matchAEnr['ligueId'];
		}
		if (isset($heure)) {
			$retClip = $evenement['chrono'];
			// retourner le but, sans correction de chrono.
			$evenement['chrono'] = $evenement['chrono'] + $heureServeur - $heure;
		}
		if (isset($evenement['noseq'])) {$noseq = $evenement['noseq'];
		}else{$noseq=0;}
		
		$trouveClip=0;
		switch(f_es($evenement['es'])) {

			case 10 :
				$qDel = "DELETE FROM Clips WHERE chrono='{$evenement['chrono']}' AND matchId='{$evenement['match_id']}'";
				mysql_query($qDel) or die(mysql_error() . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 15 :
				//$qSelClip = "SELECT * FROM Clips WHERE matchId='{$evenement['match_id']}' noSequence={$evenement['noseq']}";
				//$resClip = mysql_query($qSelClip) or die(mysql_error() . $qSelClip);
				//$trouveClip = mysql_num_rows($resClip);

			//	break;		 NO BREAK!!!!!!!
			case 12 :
				if ($trouveClip == 0) {
				$qInsM = "INSERT INTO Clips (matchId, chrono) VALUES ('{$evenement['match_id']}','{$evenement['chrono']}')";

				mysql_query($qInsM) or die(mysql_error() . $qInsM);
				}
				array_push($syncOK, $retClip);
				break;
		}

	}

	if (!strcmp($evenement['type'],'fusillade')) {
		$matchAEnr = parseMatchID($evenement['match_id']);
		if (isset($matchAEnr['ligueId'])) {$ligueId = $matchAEnr['ligueId'];
		}

		switch(f_es($evenement['es'])) {

			case 10 :
			case 12 :
				$qDel = "DELETE FROM TableEvenement0 WHERE chrono='{$evenement['chrono']}' AND match_event_id='{$evenement['match_id']}'";
				mysql_query($qDel) or die(mysql_error() . $qDel);
			//	break;		 NO BREAK!!!!!!!
			
			
				$qInsM = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$evenement['match_id']}','{$evenement['eqId']}','{$evenement['joueur']}','{$evenement['chrono']}','{$evenement['sc']}',2)";

				mysql_query($qInsM) or die(mysql_error() . $qInsM);
				array_push($syncOK, $evenement['chrono']);
				break;
		}

	}

	if (!strcmp($evenement['type'],'board')) {
		$matchAEnr = parseMatchID($evenement['match_id']);
		if (isset($matchAEnr['ligueId'])) {$ligueId = $matchAEnr['ligueId'];
		}
		if (isset($heure)) {
			$retClip = $evenement['chrono'];
			// retourner le but, sans correction de chrono.
			$evenement['chrono'] = $evenement['chrono'] + $heureServeur - $heure;
		}
		if (isset($evenement['noseq'])) {$noseq = $evenement['noseq'];
		}else{$noseq=0;}
		
		$trouveClip=0;
		switch(f_es($evenement['es'])) {

			case 10 :
			case 12 :
				/////   Pour DB Syncboard
			$db_host="localhost";
			$db_user="syncsta1_u01";
			$db_pwd="test";

			$database = 'syncsta1_910';

			$connSB = mysqli_connect($db_host, $db_user, $db_pwd, $database);
			// Check connection/
			if (!$connSB) {
				   die("Connection failed: " . mysqli_connect_error());
			}
			
//			$maDate=date('Y-m-d H:i:s', $evenement['chrono']/1000);

			mysqli_query($connSB,"SET NAMES 'utf8'");
			mysqli_query($connSB,"SET CHARACTER SET 'utf8'");
			$qSelSB = "SELECT * FROM  commandeboard WHERE matchId='{$evenement['match_id']}' AND remoteDbId='{$evenement['db_id']}'";
			$retSel =mysqli_query($connSB,$qSelSB) or die(mysqli_error($connSB) . $qSelSB);
			if(mysqli_num_rows($retSel)==0){
				$qInsSB = "INSERT INTO commandeboard (matchId,cle,valeur,chrono,remoteDbId) VALUES ('{$evenement['match_id']}','toggleBoard','{$evenement['sc']}','{$evenement['chrono']}','{$evenement['db_id']}')";

				mysqli_query($connSB,$qInsSB) or die(mysqli_error($connSB) . $qInsSB);				
			}
				
				
				
				array_push($syncOK, $evenement['chrono']);
				break;
		}

	}


	if (!strcmp($evenement['type'],'periode')) {
		$matchAEnr = parseMatchID($evenement['match_id']);
		if (isset($matchAEnr['ligueId'])) {$ligueId = $matchAEnr['ligueId'];
		}
		$retPer = $evenement['chrono'];
		// retourner le but, sans correction de chrono.

		if (isset($heure)) {$evenement['chrono'] = $evenement['chrono'] + $heureServeur - $heure;
		}
	$trouvePer=0;
		switch(f_es($evenement['es'])) {

			case 10 :
				$qDel = "DELETE FROM TableEvenement0 WHERE chrono='{$evenement['chrono']}' AND match_event_id='{$evenement['match_id']}'";
				mysql_query($qDel) or die(mysql_error() . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 15 :
			
				$qSelPer = "SELECT * FROM TableEvenement0 WHERE chrono='{$evenement['chrono']}' AND match_event_id='{$evenement['match_id']}'";
				$resPer = mysql_query($qSelPer) or die(mysql_error() . $qSelPer);
				$trouvePer = mysql_num_rows($resPer);

			//	break;		 NO BREAK!!!!!!!
			case 12 :
				if ($trouvePer == 0) {

				$qInsM = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$evenement['match_id']}',0,0,'{$evenement['chrono']}','{$evenement['sc']}',11)";

				mysql_query($qInsM) or die(mysql_error() . $qInsM);
				mysql_query("UPDATE TableMatch SET statut='{$evenement['sc']}' WHERE matchIdRef = '{$evenement['match_id']}'") or die(mysql_error());	
				
				}
				array_push($syncOK, $retPer);
				break;
		}

	}

	if (!strcmp($evenement['type'],'debutMatch')) {
		

		
		//$matchAEnr = parseMatchID($evenement['match_id']);
		//if (isset($matchAEnr['ligueId'])) {$ligueId = $matchAEnr['ligueId'];
		
		//}
	$trouveDM=0;
		switch(f_es($evenement['es'])) {

			case 10 :
			//	$qDel = "DELETE FROM TableEvenement0 WHERE chrono=$evenement['chrono'] AND match_event_id='{$evenement['match_id']}'";
			//	mysql_query($qDel) or die(mysql_error() . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 15 :
			
				$qSelDM= "SELECT * FROM TableEvenement0 WHERE code =10 AND souscode=0 AND match_event_id='{$evenement['match_id']}'";
				$resDM = mysql_query($qSelDM) or die(mysql_error() . $qSelDM);
				$trouveDM = mysql_num_rows($resDM);
			
			case 12 :
			
				if($trouveDM==0){
				$code1010 = 10000 * $evenement['eqDom'] + $evenement['eqVis'];

				$qInsGDom = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$evenement['match_id']}','{$evenement['eqDom']}','{$evenement['gDom']}','{$evenement['chrono']}',5,3)";
				$qInsGVis = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$evenement['match_id']}','{$evenement['eqVis']}','{$evenement['gVis']}','{$evenement['chrono']}',5,3)";
				$qInsDM = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$evenement['match_id']}'," . $code1010 . " ,'{$evenement['ligueId']}','{$evenement['chrono']}',0,10)";

				$cDom = 0;
				$cVis = 0;
				$cDef = 0;

				mysql_query($qInsDM) or die(mysql_error() . $qInsDM);

				while ($cDom < count($evenement['alDom'])) {
					$qAlDom = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$evenement['match_id']}','{$evenement['eqDom']}','{$evenement['alDom'][$cDom]}','{$evenement['chrono']}',0,3)";
					mysql_query($qAlDom) or die(mysql_error() . $qAlDom);
					$cDom++;
				}
				while ($cVis < count($evenement['alVis'])) {
					$qAlVis = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$evenement['match_id']}','{$evenement['eqVis']}','{$evenement['alVis'][$cVis]}','{$evenement['chrono']}',0,3)";
					mysql_query($qAlVis) or die(mysql_error() . $qAlVis);
					$cVis++;
				}
				while ($cDef < count($evenement['alDef'])) {
					$qAlDef = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$evenement['match_id']}','{$evenement['alDef'][$cDef]['eq']}','{$evenement['alDef'][$cDef]['joueur']}','{$evenement['chrono']}',0,3)";
					mysql_query($qAlDef) or die(mysql_error() . $qAlDef);
					$cDef++;
				}
				if ($evenement['gDom'] != 0) {mysql_query($qInsGDom) or die(mysql_error() . $qInsGDom);
				}
				if ($evenement['gVis'] != 0) {mysql_query($qInsGVis) or die(mysql_error() . $qInsGVis);
				}
				//											include('../scriptsphp/actualiseMatchs.php');
				}
				array_push($syncOK, $evenement['chrono']);
				
				break;
		}
		//$_POST["ligueId"] =$ligueId;
		$deSyncMatch = 1;
		
			$qSelMatch="SELECT * from TableMatch
					WHERE matchIdRef='{$evenement['match_id']}'
					";
					
					$resSelMatch = mysql_query($qSelMatch) or die(mysql_error() . $qSelMatch);
					
					if(mysql_num_rows($resSelMatch)==0){
						$qAlDef = "INSERT INTO TableMatch (ligueRef,eq_dom,score_dom,eq_vis,score_vis,matchIdRef, date,statut) 
						VALUES ({$evenement['ligueId']}, {$evenement['eqDom']}, 0, {$evenement['eqVis']}, 0,
						'{$evenement['match_id']}', '{$evenement['date']}' ,0)";
					mysql_query($qAlDef) or die(mysql_error() . $qAlDef);
					
					} else{
									mysql_query("UPDATE TableMatch SET statut='0' WHERE matchIdRef = '{$evenement['match_id']}'") or die(mysql_error());	
									
					}
					//CalculeMatch($ligueId);
					
		if(isset($evenement['arenaId'])){
			if($evenement['arenaId']!=""){
							mysql_query("UPDATE TableMatch SET arenaId='{$evenement['arenaId']}' WHERE matchIdRef = '{$evenement['match_id']}'") or die(mysql_error());
				
			}
			
				
			
		}
		
		/////  Abonnement Appareil Match
		if($deviceId!="null"&&$deviceId!="undefined"){
				
    	if (!in_array($evenement['match_id'], $arrMatchs))
			{
					if(mysql_num_rows($resSelMatch)==0){
					$resSelMatch = mysql_query($qSelMatch) or die(mysql_error() . $qSelMatch);
					}
				$arrMatchId = mysql_fetch_row($resSelMatch);
				$qInsAAM = "INSERT INTO abonAppareilMatch (matchId, role,telId) VALUES ('{$arrMatchId[0]}',
				1,'$deviceId')";
				mysql_query($qInsAAM) or die(mysql_error() . $qInsAAM);
				
				
		
			}

				
		}
		//	$message = "Execution de syncscript/dechargeMatch_2.";
		//					$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	     //   				"------------------ position 2".PHP_EOL;
		//					file_put_contents('../test/logTest.txt', $log, FILE_APPEND);	
		
	}

	if (!strcmp($evenement['type'],'finMatch')) {
		//$matchAEnr = parseMatchID($evenement['match_id']);
		//if (isset($matchAEnr['ligueId'])) {$ligueId = $matchAEnr['ligueId'];
		//}
		$trouveFM=0;

		switch(f_es($evenement['es'])) {
			case 15 :
		
				$qSelFM= "SELECT * FROM TableEvenement0 WHERE code =10 AND souscode=10 AND match_event_id='{$evenement['match_id']}'";
				$resFM = mysql_query($qSelFM) or die(mysql_error() . $qSelFM);
				$trouveFM = mysql_num_rows($resFM);
			
				case 12 :
				if($trouveFM==0){
				$qInsFM = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code) VALUES ('{$evenement['match_id']}',
				0,0,'{$evenement['chrono']}',10,10)";
				mysql_query($qInsFM) or die(mysql_error() . $qInsFM);
				}
			////////////  NO BREAK!!!  //////////////////////
			case 10 :
			default :
				//echo "1";
				$deSyncMatch = 1;
				/*?><?php*/
				//include('/public_html/scriptsphp/calculeMatch.php');
				/*?><?php*/
				$qMatch = "SELECT cleValeur, match_id FROM TableMatch WHERE matchIdRef = '{$evenement['match_id']}'";
				$testmatch = mysql_query($qMatch) or die(mysql_error() . " Select " . $evenement['db_id']);
				//echo "2";
				if (mysql_num_rows($testmatch) == 0) {
					//echo "3";
					//			include($_SERVER['DOCUMENT_ROOT'] . '/scriptsphp/actualiseMatch.php');
					$qMatch = "SELECT cleValeur FROM TableMatch WHERE matchIdRef = '{$evenement['match_id']}'";
					$testmatch = mysql_query($qMatch) or die(mysql_error() . " Select " . $evenement['db_id']);
					//echo "4";
				}
				$rMatch = mysql_fetch_row($testmatch);
				if (($rMatch[0] != NULL) && (strlen($rMatch[0]) > 2)) {
					$condMatch = $rMatch[0];
					$jMerge = json_encode(array_merge((array) json_decode($condMatch), (array) json_decode($evenement['cv'])));
				} else {
					//							$jMerge = $leMatch['cleValeur'];
					$jMerge = json_encode($evenement['cv']);
				}
				$noMatchId=$rMatch[1];
				//echo "5";
				mysql_query("UPDATE TableMatch SET cleValeur='{$jMerge}' WHERE matchIdRef = '{$evenement['match_id']}'") or die(mysql_error());
				//$monCV = json_decode($evenement['cv']);
				//											echo $monCV['scoreFinal']." ".isset($monCV['scoreFinal'])." ".$evenement['cv'];
				//echo "6";
				
				// Section enlevée pour accepter les cas d'utilisation de score entré par chacun des capitaines.
				/*if (isset($evenement['cv']['scoreFinal'])) {

					$i1 = stripos($evenement['cv']['scoreFinal'], '-');
					$i2 = stripos($evenement['cv']['scoreFinal'], '-', $i1 + 1);

					$sD = substr($evenement['cv']['scoreFinal'], 0, $i1);
					$sV = substr($evenement['cv']['scoreFinal'], $i1 + 1);
					mysql_query("UPDATE TableMatch SET score_dom='{$sD}',score_vis='{$sV}', statut='F' WHERE matchIdRef = '{$evenement['match_id']}'") or die(mysql_error());
				}*/
				array_push($syncOK, $evenement['chrono']);
		}
	}

	$IJ++;
	//echo json_encode($syncOK);
}

/// Voir explications début du foreach
	if($noMatchId!=0){
					$url = 'http://syncstats.com/scriptsphp/calculeUnMatch.php';
					$data = array('noMatchId' => $noMatchId);

					// use key 'http' even if you send the request to https://...
					$options = array(
    					'http' => array(
        				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        				'method'  => 'POST',
        				'content' => http_build_query($data)
    					)
					);
					$context  = stream_context_create($options);
					$result = file_get_contents($url, false, $context);
					if ($result === FALSE) { /* Handle error */ }
					$memNoMatchId=$noMatchId;
	}

//echo json_encode($syncOK);
/*
 $qFin="SELECT * FROM TableEvenement0 WHERE event_id>{$vRef[0]} ORDER BY event_id DESC";
 $rFin = mysql_query($qFin) or die(mysql_error() . $qFin);
 //echo $qFin;
 while($vFin = mysql_fetch_array($rFin))
 {
 $qCheck="SELECT * FROM TableEvenement0 WHERE event_id<>{$vFin['event_id']}
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
 $qDel2 = "DELETE FROM TableEvenement0 WHERE event_id={$vCheck['event_id']} ";
 mysql_query($qDel2) or die(mysql_error() . $qDel2);
 //				echo $qDel2.'\n';
 }
 }
 }	*/
/*
 echo "json_encode(lematch)".json_encode($leMatch)."\n";
 echo "matchTS".$matchsTS."\n";
 echo "post: ".$_POST['matchs']."\n";
 echo "m1:".$m1."\n";
 */

//echo json_encode($syncOK);
//.$matchjson.json_encode($leMatch);
$deSyncMatch = 1;
//							include(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'scriptsphp'.DIRECTORY_SEPARATOR.'actualiseMatchs.php');	// technique de dir inutile mais bonne pratique

// $globalSyncOK=$syncOK;
//							include('../scriptsphp/calculeMatch.php');
//include ($_SERVER['DOCUMENT_ROOT'] . '/scriptsphp/calculeMatch.php');

//header("HTTP/1.1 200 OK");

?>