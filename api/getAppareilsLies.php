<?php
declare(strict_types=1);

require '../scriptsphp/defenvvar.php'; // doit fournir $conn (mysqli)
header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function normalizeEtat(string $codeEtat, string $dernierMaJ): array {
    $ts = strtotime($dernierMaJ);
    if ($ts === false) {
        return [(int)$codeEtat, true];
    }
    $age = time() - $ts;
    $etat = (int)$codeEtat;
    $isStale = false;

    // Règle héritée de votre ancien code:
    // > 600s => 20, > 3600s => 30, mais seulement si codeEtat initial=10
    if ($etat === 10) {
        if ($age > 3600) {
            $etat = 30;
            $isStale = true;
        } elseif ($age > 600) {
            $etat = 20;
            $isStale = true;
        }
    }

    return [$etat, $isStale];
}

if (!($conn instanceof mysqli)) {
    respond(500, ['ok' => false, 'error' => 'DB connection unavailable']);
}

// 1) Lecture body JSON (prioritaire)
$raw = file_get_contents('php://input');
$json = json_decode($raw ?: '', true);

// 2) Fallback form-data (ancien client)
$username = $json['username'] ?? ($_POST['username'] ?? null);
$mode = (int)($json['mode'] ?? ($_POST['mode'] ?? 2));

// Nouveau format
$devices = $json['devices'] ?? null;

// Compat ancien format arrayTel
if ($devices === null && isset($_POST['arrayTel'])) {
    $legacy = json_decode((string)$_POST['arrayTel'], true);
    if (is_array($legacy)) {
        $devices = [];
        foreach ($legacy as $d) {
            if (!empty($d['telId'])) {
                $devices[] = ['telId' => (string)$d['telId']];
            }
        }
    }
}

if (!$username) {
    respond(400, ['ok' => false, 'error' => 'username requis']);
}
if (!is_array($devices) || count($devices) === 0) {
    respond(400, ['ok' => false, 'error' => 'devices requis']);
}
if ($mode !== 2) {
    respond(400, ['ok' => false, 'error' => 'mode non supporté (utilisez mode=2)']);
}

// Dédoublonnage par telId
$seen = [];
$telIds = [];
foreach ($devices as $d) {
    $telId = isset($d['telId']) ? trim((string)$d['telId']) : '';
    if ($telId === '' || isset($seen[$telId])) {
        continue;
    }
    $seen[$telId] = true;
    $telIds[] = $telId;
}

if (count($telIds) === 0) {
    respond(400, ['ok' => false, 'error' => 'aucun telId valide']);
}

// Requête préparée: dernier statut caméra par telId
// Remplacez `userId` par le vrai champ de votre table si nécessaire.
$sql = "
SELECT sc.telId, sc.camId, sc.codeEtat, sc.batterie, sc.memoire, sc.dernierMaJ, sc.userId
FROM StatutCam sc
INNER JOIN (
    SELECT telId, MAX(dernierMaJ) AS maxMaj
    FROM StatutCam
    WHERE userId = ?
    GROUP BY telId
) latest ON latest.telId = sc.telId AND latest.maxMaj = sc.dernierMaJ
WHERE sc.userId = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    respond(500, ['ok' => false, 'error' => 'prepare failed', 'details' => $conn->error]);
}

$out = [];
foreach ($telIds as $telId) {
    $stmt->bind_param('ss', $userId, $userId);
    if (!$stmt->execute()) {
        continue;
    }
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    if (!$row) {
        continue;
    }

    // Filtre propriétaire (nouvelle logique voulue)
    // Si vous avez une table de liaison utilisateur/appareil, filtrez plutôt dessus.
    if ((string)$row['userId'] !== (string)$username) {
        continue;
    }

    [$etatNorm, $isStale] = normalizeEtat((string)$row['codeEtat'], (string)$row['dernierMaJ']);

    $out[] = [
        // appareilId peut venir d'une table Appareil si vous l'avez; sinon null/fallback
        'appareilId' => null,
        'telId' => (string)$row['telId'],
        'camId' => (string)$row['camId'],
        'ownerUsername' => (string)$row['userId'],
        'codeEtat' => $etatNorm,
        'batterie' => (int)$row['batterie'],
        'memoireMb' => (int)round(((float)$row['memoire']) / 1000000),
        'dernierMaJ' => (string)$row['dernierMaJ'],
        'isStale' => $isStale
    ];
}

$stmt->close();
$conn->close();

respond(200, [
    'ok' => true,
    'serverTime' => gmdate('c'),
    'devices' => $out
]);
