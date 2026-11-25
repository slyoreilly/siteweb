<?php

declare(strict_types=1);

/**
 * GET /api/getPublicationJoueurs.php?equipeId=X
 *
 * Retourne la liste des joueurs Legacy actifs d'une équipe. Lecture seule.
 *
 * Paramètre obligatoire (GET) :
 *   equipeId : identifiant entier de l'équipe
 *
 * Authentification : Bearer token interservices (LEGACY_MATCH_ACTIF_TOKEN).
 *
 * Réponse 200 :
 *   [
 *     {
 *       "id": joueurId,          -- identifiant Legacy (entier)
 *       "prenom": null,          -- non stocké séparément dans TableJoueur
 *       "nom": "NomJoueur",      -- nom complet tel que saisi dans Legacy
 *       "hcr": null,             -- non stocké dans la base Legacy actuelle
 *       "numeroChandail": "17",
 *       "position": "attaquant",
 *       "inscriptionId": inscriptionId,
 *       "provenanceInscription": "manuel"
 *     },
 *     ...
 *   ]
 *
 * Limitation : le champ `hcr` est toujours null — la table TableJoueur ne
 * stocke pas le numéro Hockey Québec. Le rapprochement automatique par HCR
 * n'est donc pas disponible ; l'admin doit confirmer les correspondances
 * manuellement via l'interface de prévisualisation.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function pubJoueursRepondre(int $code, mixed $corps): void
{
    http_response_code($code);
    echo json_encode($corps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    pubJoueursRepondre(405, ['etat' => 'methode_non_autorisee']);
    return;
}

// --- Authentification ---
require_once __DIR__ . '/../scriptsphp/chargeSecrets.php';
$jetonAttendu = getenv('LEGACY_MATCH_ACTIF_TOKEN');
if ($jetonAttendu === false || trim($jetonAttendu) === '') {
    pubJoueursRepondre(503, ['etat' => 'service_non_configure']);
    return;
}

$autorisation = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/^Bearer ([^\s]+)$/iD', $autorisation, $jeton)
    || !hash_equals($jetonAttendu, $jeton[1])) {
    header('WWW-Authenticate: Bearer');
    pubJoueursRepondre(401, ['etat' => 'non_autorise']);
    return;
}

// --- Validation du paramètre ---
$equipeIdRaw = $_GET['equipeId'] ?? '';
if ($equipeIdRaw === '' || !ctype_digit((string)$equipeIdRaw)) {
    pubJoueursRepondre(400, ['etat' => 'parametre_invalide', 'detail' => 'equipeId entier requis']);
    return;
}
$equipeId = (int)$equipeIdRaw;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/../scriptsphp/defenvvar.php';
    if (!($conn instanceof mysqli)) {
        pubJoueursRepondre(500, ['etat' => 'erreur_consultation']);
        return;
    }

    mysqli_set_charset($conn, 'utf8mb4');

    // Joueurs de l'équipe avec abonnement actif à ce jour.
    // abonJoueurEquipe.id est la clé de l'inscription (si disponible).
    $stmt = $conn->prepare(
        'SELECT
            tj.joueur_id      AS id,
            tj.NomJoueur      AS nom,
            tj.NumeroJoueur   AS numeroChandail,
            tj.position       AS position,
            aje.id            AS inscriptionId
         FROM TableJoueur tj
         JOIN abonJoueurEquipe aje ON aje.joueurId = tj.joueur_id
         WHERE aje.equipeId = ?
           AND aje.debutAbon <= NOW()
           AND aje.finAbon >= NOW()
         ORDER BY tj.NomJoueur'
    );
    $stmt->bind_param('i', $equipeId);
    $stmt->execute();
    $res = $stmt->get_result();
    $joueurs = [];
    while ($row = $res->fetch_assoc()) {
        $joueurs[] = [
            'id'                   => (int)$row['id'],
            'prenom'               => null,
            'nom'                  => $row['nom'],
            'hcr'                  => null,
            'numeroChandail'       => $row['numeroChandail'],
            'position'             => $row['position'],
            'inscriptionId'        => $row['inscriptionId'] !== null ? (int)$row['inscriptionId'] : null,
            'provenanceInscription' => 'manuel',
        ];
    }
    $stmt->close();

    pubJoueursRepondre(200, $joueurs);
} catch (Throwable $erreur) {
    error_log('[getPublicationJoueurs] ' . $erreur->getMessage());
    pubJoueursRepondre(500, ['etat' => 'erreur_consultation']);
}
