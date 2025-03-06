<?php

include_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "syncstatsconfig.php");
require("../scriptsphp/calculeMatch2.php");  /// N'appelle rien, défini seulement la fonction
/// CalculeMatch(ligueId);

require '../scriptsphp/defenvvar.php';

$heure = $_POST['heure'];
$heureServeur = time() * 1000;


$preEvenements = null;
if (isset($_POST['evenements'])) {
	$preEvenements = $_POST["evenements"];
	$evenements = json_decode($preEvenements, true);
}

$syncOK = array();

$IJ = 0;
//global $syncOK;

//		echo json_encode($leMatch)."//////";

$qRef = "SELECT event_id FROM TableEvenement0 WHERE 1 ORDER BY event_id DESC LIMIT 0,1";
$rRef = mysqli_query($conn, $qRef) or die(mysqli_error($conn) . $qRef);
$vRef = mysqli_fetch_row($rRef);


$extra['DM'] = 3;
$memNoMatchId = 0;
$noMatchId = 0;

if ($evenements != null) {


	foreach ($evenements as $evenement) {

		// retourner le but, sans correction de chrono.

		if (isset($heure)) {
			$evenement['chrono'] = $evenement['chrono'] + $heureServeur - $heure;
		}
		if ($evenement['etatSync'] == 10) {

			$qDel = "DELETE FROM TableEvenement0 WHERE event_id='{$evenement['EventComId']}'";
			mysqli_query($conn, $qDel) or die(mysqli_error($conn) . $qDel);
			$retObj = array("id" => $evenement['id'], "EventComId" => mysqli_insert_id($conn), "etatSync" => 10);
			array_push($syncOK, $retObj);

		} else {
				// Sécurisation des variables en forçant les types attendus
				$gameStringID = (int) $evenement['GameStringID'];
				$teamID = (int) $evenement['TeamID'];
				$playerID = (int) $evenement['PlayerID'];
				$chrono = (int) $evenement['chrono'];
				$eventTypeDetailID = (int) $evenement['EventTypeDetailID'];
				$eventTypeID = (int) $evenement['EventTypeID'];
				$id = (int) $evenement['id'];

				if (is_null($evenement['EventComId'])) {
					
				// Préparation de la requête INSERT
				$stmt = mysqli_prepare(
					$conn,
					"INSERT INTO TableEvenement0 
    (match_event_id, equipe_event_id, joueur_event_ref, chrono, souscode, code, noSequence) 
    VALUES (?, ?, ?, ?, ?, ?, 0)"
				);

				// Liaison des paramètres (tous les paramètres sont des entiers)
				mysqli_stmt_bind_param($stmt, "iiiiii", $gameStringID, $teamID, $playerID, $chrono, $eventTypeDetailID, $eventTypeID);

				// Exécution de la requête
				$success = mysqli_stmt_execute($stmt);

				// Vérification de la réussite de l'insertion
				if (!$success) {
					die("Erreur d'insertion : " . mysqli_error($conn));
				}

				// Récupération de l'ID de l'événement inséré
				$eventComId = mysqli_insert_id($conn);

				// Vérification si un ID valide a été retourné
				if ($eventComId <= 0) {
					die("Erreur : Aucun ID inséré.");
				}

				// Ajout du résultat dans le tableau de synchronisation
				$retObj = array("id" => $id, "EventComId" => $eventComId, "etatSync" => 12);
				array_push($syncOK, $retObj);

				// Fermeture du statement
				mysqli_stmt_close($stmt);

			} else {
				// Sécurisation des variables
				$eventComId = mysqli_real_escape_string($conn, $evenement['EventComId']);

				// Récupération du code et sous-code de l'EventType
				$stmt = mysqli_prepare($conn, "SELECT Code, Subcode FROM Eventtype WHERE EventTypeId = ? LIMIT 1");
				mysqli_stmt_bind_param($stmt, "i", $eventTypeID);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_bind_result($stmt, $code, $subcode);
				mysqli_stmt_fetch($stmt);
				mysqli_stmt_close($stmt);

				// Vérification des résultats
				if ($code === null || $subcode === null) {
					die("Erreur: Aucun Code/Subcode trouvé pour EventTypeId={$eventTypeID}");
				}

				// Mise à jour de la table TableEvenement0 avec une requête préparée
				$stmt = mysqli_prepare(
					$conn,
					"UPDATE TableEvenement0 
     SET match_event_id = ?, 
         equipe_event_id = ?, 
         joueur_event_ref = ?, 
         chrono = ?, 
         code = ?, 
         souscode = ?, 
         noSequence = 0 
     WHERE event_id = ?"
				);

				mysqli_stmt_bind_param($stmt, "iiiiiss", $gameStringID, $teamID, $playerID, $chrono, $code, $subcode, $eventComId);
				$success = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);

				// Vérification de la mise à jour
				if (!$success || mysqli_affected_rows($conn) <= 0) {
					die("Erreur: Mise à jour échouée pour event_id={$eventComId}");
				}

				// Retour des résultats
				$retObj = array("id" => $id, "EventComId" => $eventComId, "etatSync" => 12);
				array_push($syncOK, $retObj);
			}

		}




	}
}



/// Voir explications début du foreach
if ($noMatchId != 0) {
	if ($workEnv == "production") {
		$url = 'http://syncstats.com/scriptsphp/calculeUnMatch.php';
	} else {
		$url = 'http://vieuxsite.sm.syncstats.ca/scriptsphp/calculeUnMatch.php';
	}
	$data = array('noMatchId' => $noMatchId);

	// use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
			'header' => "Content-type: application/x-www-form-urlencoded\r\n",
			'method' => 'POST',
			'content' => http_build_query($data)
		)
	);
	$context = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result === FALSE) {
		error_log("erreur dans calcule match, 926", 0);

	}
	$memNoMatchId = $noMatchId;
}

$deSyncMatch = 1;
echo json_encode($syncOK);
mysqli_close($conn);

?>