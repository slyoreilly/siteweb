<?php
// Debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_log("▶️ Début script getVideosParLigueEtPlateau.php");

// Affichage des variables d’environnement
$workEnv = getenv('WORK_ENV');
$httpHost = $_SERVER['HTTP_HOST'] ?? 'N/A';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'N/A';
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? 'N/A';

error_log("🔧 Environnement : WORK_ENV = " . var_export($workEnv, true));
error_log("🌐 HTTP_HOST = " . $httpHost);
error_log("📄 SCRIPT_NAME = " . $scriptName);
error_log("📡 REMOTE_ADDR = " . $remoteAddr);

// Inclusion de la config
require '../scriptsphp/defenvvar.php';
global $conn, $db_host, $db_user, $db_pwd, $database, $db_port;

// Log détails de connexion
error_log("🛠️ DB_HOST = $db_host | DB_USER = $db_user | DATABASE = $database | DB_PORT = " . ($db_port ?? 'default'));

if (!$conn || !($conn instanceof mysqli)) {
    error_log("❌ Connexion MySQL invalide ou absente.");
    die(json_encode(["error" => "Connexion MySQL invalide"]));
}
error_log("✅ Connexion MySQL OK");

$ligueId = isset($_GET['ligueId']) ? intval($_GET['ligueId']) : null;
$plateauId = isset($_GET['plateauId']) ? intval($_GET['plateauId']) : null;

if (!$ligueId || !$plateauId) {
    error_log("❌ Paramètres GET manquants : ligueId={$ligueId}, plateauId={$plateauId}");
    echo json_encode(["error" => "Paramètres ligueId et plateauId requis."]);
    exit;
}
error_log("✅ Paramètres GET reçus : ligueId=$ligueId, plateauId=$plateauId");

$sql = "SELECT e.event_id, e.chrono
        FROM TableEvenement0 e
        JOIN TableMatch m ON e.match_event_id = m.matchIdRef
        WHERE m.ligueRef = ? AND m.arenaId = ?
        ORDER BY e.chrono DESC 
        LIMIT 100";

$evenements = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $ligueId, $plateauId);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("❌ Échec exécution requête principale : " . mysqli_error($conn));
        die(json_encode(["error" => "Erreur exécution requête principale."]));
    }

    mysqli_stmt_bind_result($stmt, $eventId, $chrono);

    while (mysqli_stmt_fetch($stmt)) {
        $evenements[] = ["eventId" => $eventId, "chrono" => $chrono];
    }

    mysqli_stmt_close($stmt);
    error_log("✅ Nombre d'événements récupérés : " . count($evenements));
} else {
    error_log("❌ Préparation requête principale échouée : " . mysqli_error($conn));
    die(json_encode(["error" => "Erreur préparation requête principale."]));
}

$resultats = [];

foreach ($evenements as $event) {
    $videos = [];

    $sql2 = "SELECT nomFichier, emplacement FROM Video WHERE type = 0 AND reference = ?";
    if ($stmt2 = mysqli_prepare($conn, $sql2)) {
        mysqli_stmt_bind_param($stmt2, "i", $event["eventId"]);
        if (mysqli_stmt_execute($stmt2)) {
            mysqli_stmt_bind_result($stmt2, $nomFichier, $emplacement);
            while (mysqli_stmt_fetch($stmt2)) {
                $videos[] = "https://{$emplacement}/lookatthis/{$nomFichier}";
            }
        } else {
            error_log("❌ Échec requête vidéo pour eventId={$event["eventId"]} : " . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt2);
    } else {
        error_log("❌ Échec préparation requête vidéo pour eventId={$event["eventId"]} : " . mysqli_error($conn));
    }

    $resultats[] = [
        "chrono" => $event["chrono"],
        "videos" => $videos
    ];
}

mysqli_close($conn);
error_log("✅ Script terminé avec succès");
echo json_encode($resultats);
?>
