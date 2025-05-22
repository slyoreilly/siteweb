<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../scriptsphp/defenvvar.php';
global $conn;

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

$evenements = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $ligueId, $plateauId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $eventId, $chrono);

    while (mysqli_stmt_fetch($stmt)) {
        $evenements[] = ["eventId" => $eventId, "chrono" => $chrono];
    }

    mysqli_stmt_close($stmt);
}

$resultats = [];

foreach ($evenements as $event) {
    $videos = [];

    $sql2 = "SELECT nomFichier, emplacement FROM Video WHERE type = 0 AND reference = ?";
    if ($stmt2 = mysqli_prepare($conn, $sql2)) {
        mysqli_stmt_bind_param($stmt2, "i", $event["eventId"]);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_bind_result($stmt2, $nomFichier, $emplacement);

        while (mysqli_stmt_fetch($stmt2)) {
            $videos[] = "https://{$emplacement}/lookatthis/{$nomFichier}";
        }

        mysqli_stmt_close($stmt2);
    }

    $resultats[] = [
        "chrono" => $event["chrono"],
        "videos" => $videos
    ];
}

echo json_encode($resultats);
mysqli_close($conn);
?>
