<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_log("▶️ Script optimisé (sous-select + join)");

require '../scriptsphp/defenvvar.php';
global $conn;

$ligueId = isset($_GET['ligueId']) ? intval($_GET['ligueId']) : null;
$plateauId = isset($_GET['plateauId']) ? intval($_GET['plateauId']) : null;

if (!$ligueId || !$plateauId) {
    error_log("❌ Paramètres manquants");
    echo json_encode(["error" => "Paramètres ligueId et plateauId requis."]);
    exit;
}

$sql = "
SELECT e.event_id, e.chrono, v.nomFichier, v.emplacement
FROM (
    SELECT e.event_id, e.chrono
    FROM TableEvenement0 e
    JOIN TableMatch m ON e.match_event_id = m.matchIdRef
    WHERE m.ligueRef = ? AND m.arenaId = ?
    ORDER BY e.chrono DESC
    LIMIT 100
) e
LEFT JOIN Video v ON v.reference = e.event_id AND v.type = 0
";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $ligueId, $plateauId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $eventId, $chrono, $nomFichier, $emplacement);

    $resultats = [];

    while (mysqli_stmt_fetch($stmt)) {
        if (!isset($resultats[$eventId])) {
            $resultats[$eventId] = [
                "chrono" => $chrono,
                "videos" => []
            ];
        }

        if ($nomFichier) {
            $resultats[$eventId]["videos"][] = "https://{$emplacement}/lookatthis/{$nomFichier}";
        }
    }

    mysqli_stmt_close($stmt);
    error_log("✅ Requête exécutée, événements : " . count($resultats));
} else {
    error_log("❌ Erreur SQL : " . mysqli_error($conn));
    die(json_encode(["error" => "Erreur SQL."]));
}

mysqli_close($conn);
echo json_encode(array_values($resultats));
?>
