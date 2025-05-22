<?php

// Activation du rapport d'erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../scriptsphp/defenvvar.php';

$ligueId = isset($_GET['ligueId']) ? intval($_GET['ligueId']) : null;
$plateauId = isset($_GET['plateauId']) ? intval($_GET['plateauId']) : null;

if (!$ligueId || !$plateauId) {
    echo json_encode(["error" => "Paramètres ligueId et plateauId requis."]);
    exit;
}

$sql = "SELECT e.event_id, e.chrono
        FROM TableEvenement0 e
        JOIN TableMatch m ON e.match_event_id = m.matchIdRef
        WHERE m.ligueRef = ? AND m.arenaId = ? 
        ORDER BY e.chrono DESC 
        LIMIT 100";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $ligueId, $plateauId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $eventId, $chrono);

    $resultats = [];

    while (mysqli_stmt_fetch($stmt)) {
        $videos = [];

        $sql2 = "SELECT nomFichier, emplacement FROM Video WHERE type = 0 AND reference = ?";
        if ($stmt2 = mysqli_prepare($conn, $sql2)) {
            mysqli_stmt_bind_param($stmt2, "i", $eventId);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_bind_result($stmt2, $nomFichier, $emplacement);

            while (mysqli_stmt_fetch($stmt2)) {
                $videos[] = "https://{$emplacement}/lookatthis/{$nomFichier}";
            }
            mysqli_stmt_close($stmt2);
        }

        $resultats[] = [
            "chrono" => $chrono,
            "videos" => $videos
        ];
    }

    mysqli_stmt_close($stmt);
    echo json_encode($resultats);
} else {
    echo json_encode(["error" => "Erreur SQL principale."]);
}

mysqli_close($conn);
?>
