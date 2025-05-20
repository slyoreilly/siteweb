<?php
require_once('../scriptsphp/defenvvar.php'); // Connexion MySQL

$ligueId = isset($_GET['ligueId']) ? intval($_GET['ligueId']) : null;
$plateauId = isset($_GET['plateauId']) ? intval($_GET['plateauId']) : null;

if (!$ligueId || !$plateauId) {
    echo json_encode(["error" => "Paramètres ligueId et plateauId requis."]);
    exit;
}

// Étape 1 : chercher tous les events liés à la ligue et au plateau
$sql = "SELECT e.event_id, e.chrono
        FROM tableEvenement0 e
        JOIN tableMatch m ON e.match_id = m.matchId
        WHERE m.ligueRef = ? AND m.arenaId = ?";

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
?>
