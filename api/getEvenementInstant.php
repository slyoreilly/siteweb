<?php
declare(strict_types=1);

require '../scriptsphp/defenvvar.php'; // fournit $conn (mysqli)

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!($conn instanceof mysqli)) {
    respond(500, ['ok' => false, 'error' => 'DB connection unavailable']);
}

// --- Lecture entrée (JSON prioritaire, fallback POST) ---
$raw = file_get_contents('php://input');
$json = json_decode($raw ?: '', true);

$username    = $json['username']    ?? ($_POST['username'] ?? null);
$arenaId     = $json['arenaId']     ?? ($_POST['arenaId'] ?? null);
$lastEventId = $json['lastEventId'] ?? ($_POST['lastEventId'] ?? 0);

if (!$username || !is_numeric($arenaId)) {
    respond(400, [
        'ok' => false,
        'error' => 'username ou arenaId invalide'
    ]);
}

$username = trim((string)$username);
$arenaId = (int)$arenaId;
$lastEventId = (int)$lastEventId;

mysqli_query($conn, "SET SQL_BIG_SELECTS=1");

/*
    On cherche le PLUS RÉCENT événement
    dans cet arena
    dans une ligue où l'utilisateur est abonné
*/

$sql = "
SELECT 
    e.event_id AS eventId,
    e.chrono
FROM TableEvenement0 e
INNER JOIN TableMatch m ON e.match_event_id = m.matchIdRef
INNER JOIN AbonnementLigue al ON m.ligueRef = al.ligueid
INNER JOIN TableUser u ON al.userid = u.noCompte
WHERE 
    m.arenaId = ?
    AND u.username = ?
ORDER BY e.event_id DESC
LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    respond(500, ['ok' => false, 'error' => $conn->error]);
}

$stmt->bind_param("is", $arenaId, $username);

if (!$stmt->execute()) {
    respond(500, ['ok' => false, 'error' => $stmt->error]);
}

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$hasNewEvent = false;
$eventId = 0;
$chrono = 0;

if ($row) {
    $eventId = (int)$row['eventId'];
    $chrono  = (int)$row['chrono'];

    if ($lastEventId > 0) {
        $hasNewEvent = ($eventId > $lastEventId);
    } else {
        $hasNewEvent = true;
    }
}

$stmt->close();

respond(200, [
    'ok' => true,
    'serverTime' => gmdate('c'),
    'hasNewEvent' => $hasNewEvent,
    'eventId' => $eventId,
    'chrono' => $chrono
]);
