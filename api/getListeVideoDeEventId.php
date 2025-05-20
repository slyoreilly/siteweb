<?php
require_once('../scriptsphp/defenvvar.php'); // Initialise $conn avec mysqli

// Vérifie les paramètres
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;
$clip_id = isset($_GET['clipId']) ? intval($_GET['clipId']) : null;

if (!$event_id && !$clip_id) {
    echo json_encode(["error" => "Paramètre event_id ou clipId requis."]);
    exit;
}

if ($event_id) {
    $sql = "SELECT cheminFichier FROM Video WHERE type = 0 AND reference = ?";
    $param = $event_id;
} else {
    $sql = "SELECT cheminFichier FROM Video WHERE type = 5 AND reference = ?";
    $param = $clip_id;
}

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $param);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $cheminFichier);

    $videos = [];
    while (mysqli_stmt_fetch($stmt)) {
        $videos[] = $cheminFichier;
    }

    mysqli_stmt_close($stmt);
    echo json_encode($videos);
} else {
    echo json_encode(["error" => "Erreur lors de la préparation de la requête."]);
}
?>
