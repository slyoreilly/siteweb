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

// --- Lecture de l'entrée (JSON prioritaire, fallback POST) ---
$raw = file_get_contents('php://input');
$json = json_decode($raw ?: '', true);

$username    = $json['username']    ?? ($_POST['username']    ?? null);
$tempsDepart = $json['tempsDepart'] ?? ($_POST['tempsDepart'] ?? null);
$arenaId     = $json['arenaId']     ?? ($_POST['arenaId']     ?? null);

if (!$username) {
    respond(400, ['ok' => false, 'error' => 'username requis']);
}
if ($tempsDepart === null || !is_numeric($tempsDepart)) {
    respond(400, ['ok' => false, 'error' => 'tempsDepart numérique requis (chrono en ms)']);
}
if ($arenaId === null || !is_numeric($arenaId)) {
    respond(400, ['ok' => false, 'error' => 'arenaId numérique requis']);
}

$username    = trim((string)$username);
$tempsDepart = (int)$tempsDepart;
$arenaId     = (int)$arenaId;

// --- Construction de la requête ---
// Idée :
//  - événements TableEvenement0 susceptibles de produire une vidéo (code != 11 par ex.)
//  - clips existants (Clips)
//  - pour les matchs où l'utilisateur est abonné (AbonnementLigue + TableUser)
//  - dans le même aréna
//  - après tempsDepart

mysqli_query($conn, "SET SQL_BIG_SELECTS=1");

$sql = "
(
SELECT 
    e.event_id AS eventId,
    e.chrono,
    m.matchIdRef,
    m.match_id AS matchId,
    m.ligueRef AS ligueId,
    m.arenaId,
    m.eq_dom AS eqDom,
    m.eq_vis AS eqVis,
    m.date,
    0 AS type,
    e.code,
    e.souscode AS sousCode,
    e.equipe_event_id AS scoringEnd
FROM TableEvenement0 e
INNER JOIN TableMatch m ON e.match_event_id = m.matchIdRef
INNER JOIN AbonnementLigue al ON m.ligueRef = al.ligueid
INNER JOIN TableUser u ON al.userid = u.noCompte
WHERE 
    e.chrono >= ?
    AND m.arenaId = ?
    AND u.username = ?
)
UNION ALL
(
SELECT 
    c.clipId AS eventId,
    c.chrono,
    m.matchIdRef,
    m.match_id AS matchId,
    m.ligueRef AS ligueId,
    m.arenaId,
    m.eq_dom AS eqDom,
    m.eq_vis AS eqVis,
    m.date,
    5 AS type,
    5 AS code,
    0 AS sousCode,
    c.scoringEnd
FROM Clips c
INNER JOIN TableMatch m ON c.matchId = m.matchIdRef
INNER JOIN AbonnementLigue al ON m.ligueRef = al.ligueid
INNER JOIN TableUser u ON al.userid = u.noCompte
WHERE 
    c.chrono >= ?
    AND m.arenaId = ?
    AND u.username = ?
)
ORDER BY matchIdRef, chrono
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    respond(500, ['ok' => false, 'error' => $conn->error]);
}

$stmt->bind_param(
    "iisiis",
    $tempsDepart, $arenaId, $username,
    $tempsDepart, $arenaId, $username
);

if (!$stmt->execute()) {
    respond(500, ['ok' => false, 'error' => $stmt->error]);
}

$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = [
        'type'        => (int)$row['type'],
        'eventId'     => (int)$row['eventId'],
        'chrono'      => (int)$row['chrono'],
        'matchIdRef'  => $row['matchIdRef'],
        'matchId'     => (int)$row['matchId'],
        'ligueId'     => (int)$row['ligueId'],
        'arenaId'     => (int)$row['arenaId'],
        'eqDom'       => (int)$row['eqDom'],
        'eqVis'       => (int)$row['eqVis'],
        'date'        => $row['date'],
        'code'        => (int)$row['code'],
        'sousCode'    => (int)$row['sousCode'],
        'scoringEnd'  => isset($row['scoringEnd']) ? (int)$row['scoringEnd'] : null,
    ];
}

$stmt->close();

mysqli_free_result($result);
// mysqli_close($conn); // en général on laisse PHP fermer

respond(200, [
    'ok'          => true,
    'serverTime'  => gmdate('c'),
    'tempsDepart' => $tempsDepart,
    'events'      => $events
]);
