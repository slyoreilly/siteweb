<?php

declare(strict_types=1);

/**
 * GET /api/getPublicationLigues.php
 *
 * Retourne la liste des ligues Legacy ayant au moins une équipe avec un
 * abonnement actif. Lecture seule, sans écriture.
 *
 * Authentification : Bearer token interservices (LEGACY_MATCH_ACTIF_TOKEN).
 * Même mécanisme que getEvenementsAvecVideos.php.
 *
 * Réponse 200 :
 *   [{"id": ligueId, "nom": "Nom Ligue"}, ...]
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function pubLiguesRepondre(int $code, mixed $corps): void
{
    http_response_code($code);
    echo json_encode($corps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    pubLiguesRepondre(405, ['etat' => 'methode_non_autorisee']);
    return;
}

// --- Authentification ---
require_once __DIR__ . '/../scriptsphp/chargeSecrets.php';
$jetonAttendu = getenv('LEGACY_MATCH_ACTIF_TOKEN');
if ($jetonAttendu === false || trim($jetonAttendu) === '') {
    pubLiguesRepondre(503, ['etat' => 'service_non_configure']);
    return;
}

$autorisation = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/^Bearer ([^\s]+)$/iD', $autorisation, $jeton)
    || !hash_equals($jetonAttendu, $jeton[1])) {
    header('WWW-Authenticate: Bearer');
    pubLiguesRepondre(401, ['etat' => 'non_autorise']);
    return;
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/../scriptsphp/defenvvar.php';
    if (!($conn instanceof mysqli)) {
        pubLiguesRepondre(500, ['etat' => 'erreur_consultation']);
        return;
    }

    mysqli_set_charset($conn, 'utf8mb4');

    // Ligues ayant au moins une équipe avec abonnement actif à ce jour
    $stmt = $conn->prepare(
        'SELECT DISTINCT l.ID_Ligue AS id, l.Nom_Ligue AS nom
         FROM Ligue l
         JOIN abonEquipeLigue ael ON ael.ligueId = l.ID_Ligue
         WHERE ael.debutAbon <= NOW() AND ael.finAbon >= NOW()
         ORDER BY l.Nom_Ligue'
    );
    $stmt->execute();
    $res = $stmt->get_result();
    $ligues = [];
    while ($row = $res->fetch_assoc()) {
        $ligues[] = [
            'id'  => (int)$row['id'],
            'nom' => $row['nom'],
        ];
    }
    $stmt->close();

    pubLiguesRepondre(200, $ligues);
} catch (Throwable $erreur) {
    error_log('[getPublicationLigues] ' . $erreur->getMessage());
    pubLiguesRepondre(500, ['etat' => 'erreur_consultation']);
}
