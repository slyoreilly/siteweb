<?php

include_once ($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR . "syncstatsconfig.php");
require("../scriptsphp/calculeMatch2.php");  /// N'appelle rien, défini seulement la fonction
											 /// CalculeMatch(ligueId);

require '../scriptsphp/defenvvar.php';
// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");
	
$evenements = $_POST['evenements'];

$IJ = 0;
//global $syncOK;

//		echo json_encode($leMatch)."//////";

$qRef = "SELECT event_id FROM TableEvenement0 WHERE 1 ORDER BY event_id DESC LIMIT 0,1";
$rRef = mysqli_query($conn,$qRef) or die(mysqli_error($conn) . $qRef);
$vRef = mysqli_fetch_row($rRef);


	$extra['DM']=3;
	$memNoMatchId=0;
	$noMatchId=0;

	

	if (!strcmp($evenement['type'],'generic')) {
		$matchAEnr = parseMatchID($evenement['match_id']);
		if (isset($matchAEnr['ligueId'])) {$ligueId = $matchAEnr['ligueId'];
		}
		if (isset($evenement['noseq'])) {$noseq = intval($evenement['noseq']);
		}else{$noseq=0;}
		
		$trouvePun=0;

		$retPun = $evenement['chrono'];
		// retourner le but, sans correction de chrono.

		if (isset($heure)) {$evenement['chrono'] = $evenement['chrono'] + $heureServeur - $heure;
		}
		switch(f_es($evenement['es'])) {

			case 10 :
				$qDel = "DELETE FROM TableEvenement0 WHERE chrono='{$evenement['chrono']}' AND match_event_id='{$evenement['match_id']}'";
				mysqli_query($conn,$qDel) or die(mysqli_error($conn) . $qDel);
			//	break;		 NO BREAK!!!!!!!
			case 15 :
			
				$qSelPun = "SELECT event_id FROM TableEvenement0 WHERE match_event_id='{$evenement['match_id']}' AND code='{$evenement['code']}' AND noSequence={$noseq}";
				$resPun = mysqli_query($conn,$qSelPun) or die(mysqli_error($conn) . $qSelPun);
				$trouvePun = mysqli_num_rows($resPun);
				mysqli_data_seek($resPun,0);
				$evId = mysqli_fetch_row($resPun);

			//	break;		 NO BREAK!!!!!!!
			case 12 :
				if ($trouvePun == 0) {
				$qInsM = "INSERT INTO TableEvenement0 (match_event_id, equipe_event_id,joueur_event_ref,chrono,souscode,code,noSequence) VALUES ('{$evenement['match_id']}','{$evenement['eqId']}','{$evenement['joueur']}','{$evenement['chrono']}','{$evenement['sc']}','{$evenement['code']}','{$noseq}')";

				mysqli_query($conn,$qInsM) or die(mysqli_error($conn) . $qInsM);
				$retObj = array("type"=>"generic","chronoInit"=>$retPun,"chronoFin"=>$evenement['chrono'],"db_id"=>$evenement['db_id'], "webId"=>mysqli_insert_id($conn));
				array_push($syncOKdetail, $retObj);
				
				} else{
					array_push($syncOK, $retPun);
					$retObj = array("type"=>"generic","chronoInit"=>$retPun,"chronoFin"=>$evenement['chrono'],"webId"=>$evId[0],"db_id"=>"{$evenement['db_id']}");
					array_push($syncOKdetail, $retObj);

				}


		}
				



	}

	$IJ++;
	//echo json_encode($syncOK);
}

/// Voir explications début du foreach
	if($noMatchId!=0){
		 if($workEnv=="production"){
		$url = 'http://syncstats.com/scriptsphp/calculeUnMatch.php';
		 }else{
			$url = 'http://vieuxsite.sm.syncstats.ca/scriptsphp/calculeUnMatch.php';
		 }
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
					if ($result === FALSE) {
						error_log("erreur dans calcule match, 926",0);

					}
					$memNoMatchId=$noMatchId;
	}

$deSyncMatch = 1;

mysqli_close($conn);

?>