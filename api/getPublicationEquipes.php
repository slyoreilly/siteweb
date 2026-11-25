<?php

declare(strict_types=1);

/**
 * GET /api/getPublicationEquipes.php?ligueId=X
 *
 * Retourne la liste des équipes Legacy actives d'une ligue. Lecture seule.
 *
 * Paramètre obligatoire (GET) :
 *   ligueId : identifiant entier de la ligue
 *
 * Authentification : Bearer token interservices (LEGACY_MATCH_ACTIF_TOKEN).
 *
 * Réponse 200 :
 *   [{"id": equipeId, "nom": "Nom Equipe"}, ...]
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function pubEquipesRepondre(int $code, mixed $corps): void
{
    http_response_code($code);
    echo json_encode($corps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    pubEquipesRepondre(405, ['etat' => 'methode_non_autorisee']);
    return;
}

// --- Authentification ---
require_once __DIR__ . '/../scriptsphp/chargeSecrets.php';
$jetonAttendu = getenv('LEGACY_MATCH_ACTIF_TOKEN');
if ($jetonAttendu === false || trim($jetonAttendu) === '') {
    pubEquipesRepondre(503, ['etat' => 'service_non_configure']);
    return;
}

$autorisation = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/^Bearer ([^\s]+)$/iD', $autorisation, $jeton)
    || !hash_equals($jetonAttendu, $jeton[1])) {
    header('WWW-Authenticate: Bearer');
    pubEquipesRepondre(401, ['etat' => 'non_autorise']);
    return;
}

// --- Validation du paramètre ---
$ligueIdRaw = $_GET['ligueId'] ?? '';
if ($ligueIdRaw === '' || !ctype_digit((string)$ligueIdRaw)) {
    pubEquipesRepondre(400, ['etat' => 'parametre_invalide', 'detail' => 'ligueId entier requis']);
    return;
}
$ligueId = (int)$ligueIdRaw;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/../scriptsphp/defenvvar.php';
    if (!($conn instanceof mysqli)) {
        pubEquipesRepondre(500, ['etat' => 'erreur_consultation']);
        return;
    }

    mysqli_set_charset($conn, 'utf8mb4');

    // Équipes de la ligue avec abonnement actif à ce jour
    $stmt = $conn->prepare(
        'SELECT te.equipe_id AS id, te.nom_equipe AS nom
         FROM TableEquipe te
         JOIN abonEquipeLigue ael ON ael.equipeId = te.equipe_id
         WHERE ael.ligueId = ?
           AND ael.debutAbon <= NOW()
           AND ael.finAbon >= NOW()
         ORDER BY te.nom_equipe'
    );
    $stmt->bind_param('i', $ligueId);
    $stmt->execute();
    $res = $stmt->get_result();
    $equipes = [];
    while ($row = $res->fetch_assoc()) {
        $equipes[] = [
            'id'  => (int)$row['id'],
            'nom' => $row['nom'],
        ];
    }
    $stmt->close();

    pubEquipesRepondre(200, $equipes);
} catch (Throwable $erreur) {
    error_log('[getPublicationEquipes] ' . $erreur->getMessage());
    pubEquipesRepondre(500, ['etat' => 'erreur_consultation']);
}
