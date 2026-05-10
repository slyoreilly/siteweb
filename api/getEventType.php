<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';

function normaliserSportIds($sportIds, $decodedSports, $sportIdsFournis) {
	$infos = array(
		'ids' => array(),
		'avant' => array(),
		'raison' => null
	);

	if(!$sportIdsFournis){
		$infos['raison'] = 'sportIds_absent';
		return $infos;
	}

	if($sportIds === '' || $sportIds === null){
		$infos['raison'] = 'sportIds_vide';
		return $infos;
	}

	if(json_last_error() !== JSON_ERROR_NONE){
		$infos['raison'] = 'json_invalide';
		return $infos;
	}

	if(!is_array($decodedSports)){
		$infos['raison'] = 'sportIds_non_tableau';
		return $infos;
	}

	$infos['avant'] = $decodedSports;
	if(empty($decodedSports)){
		$infos['raison'] = 'tableau_vide';
		return $infos;
	}

	$ids = array();
	foreach($decodedSports as $sportId){
		if(!is_scalar($sportId)){
			continue;
		}

		$sportId = (int)$sportId;
		if($sportId > 0){
			$ids[$sportId] = $sportId;
		}
	}

	$infos['ids'] = array_values($ids);
	if(empty($infos['ids'])){
		$infos['raison'] = 'aucun_sport_valide';
	}else if(count($infos['ids']) !== count($decodedSports)){
		$infos['raison'] = 'valeurs_filtrees';
	}

	return $infos;
}

function journaliserSportIdsEventType($infos, $sportIds) {
	if($infos['raison'] === null){
		return;
	}

	$source = isset($_POST['sportIdsSource']) ? $_POST['sportIdsSource'] : (isset($_POST['sourceSportIds']) ? $_POST['sourceSportIds'] : (isset($_POST['syncSource']) ? $_POST['syncSource'] : 'non_fournie'));
	$nbLigues = isset($_POST['nbLiguesLocales']) ? $_POST['nbLiguesLocales'] : (isset($_POST['liguesLocalesCount']) ? $_POST['liguesLocalesCount'] : (isset($_POST['localLeagueCount']) ? $_POST['localLeagueCount'] : 'non_fourni'));
	error_log("getEventType sportIds normalisation raison={$infos['raison']} nbLiguesLocales={$nbLigues} source={$source} avant=" . (string)$sportIds . " apres=" . json_encode($infos['ids']));
}

$event=Array();
$sportIds=null;
$sportIdsFournis = isset($_POST['sportIds']);
$decodedSports = null;
if(isset($_POST['sportIds'])){
$sportIds = $_POST["sportIds"];
$decodedSports = json_decode($sportIds, true);
}
$sportIdsNormalises = normaliserSportIds($sportIds, $decodedSports, $sportIdsFournis);
$sportIdArray = $sportIdsNormalises['ids'];
journaliserSportIdsEventType($sportIdsNormalises, $sportIds);

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

$saisonId =null;

/////////////////////////////////////////////////////////////
// 
//

if(empty($sportIdArray)){
	$rfEventType = mysqli_query($conn,"SELECT * FROM EventType WHERE 1");
}else{
	$sportConditions = array();
	foreach($sportIdArray as $sportId){
		$sportConditions[] = "SportID = '{$sportId}'";
	}
	$sportString = implode(" OR ", $sportConditions);

	if(empty($sportString)){
		echo json_encode($event);
		exit;
	}
	


	$rfEventType = mysqli_query($conn,"SELECT * FROM EventType WHERE {$sportString} ");
	
}

if(!$rfEventType){
	error_log("getEventType SQL: " . mysqli_error($conn) . " sportIds=" . (string)$sportIds);
	echo json_encode($event);
	exit;
}

//$vecEvent = array();
while($rangeeEvent=mysqli_fetch_assoc($rfEventType))
{
	$rangeeEvent['CreatedAt'] = strtotime($rangeeEvent['CreatedAt']); // convert to unix timestamp (in seconds)
    $rangeeEvent['CreatedAt'] = 1000 * $rangeeEvent['CreatedAt']; // convert seconds to milliseconds
	$rangeeEvent['UpdatedAt'] = strtotime($rangeeEvent['UpdatedAt']); // convert to unix timestamp (in seconds)
    $rangeeEvent['UpdatedAt'] = 1000 * $rangeeEvent['UpdatedAt']; // convert seconds to milliseconds
	$event[] = $rangeeEvent;
//	array_push($vecEvent, $event);
}
	
echo json_encode($event);;
	
//mysqli_close($conn);

?>
