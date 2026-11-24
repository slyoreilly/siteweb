<?php

declare(strict_types=1);

/**
 * GET /api/getEvenementsAvecVideos.php
 *
 * Endpoint de LECTURE SEULE, authentifié, retournant les événements et leurs
 * vidéos DÉCOUPÉES (Video.type = 0) pour alimenter le nouveau Backend
 * expérimental à partir de Legacy production, sans accès direct MySQL.
 *
 * Authentification : réutilise le jeton interservices LEGACY_MATCH_ACTIF_TOKEN
 * (même mécanisme que getMatchActifParSurface.php / getCamerasParSurface.php,
 * chargé par scriptsphp/chargeSecrets.php). Périmètre d'autorisation : ce jeton
 * donne un accès de CONSULTATION serveur à serveur aux données Legacy de toutes
 * les surfaces. Le présent endpoint est strictement en lecture seule et n'expose
 * que des références (événement, match, caméra, fichier) et des URL vidéo déjà
 * publiques ; il reste donc dans le périmètre de consultation du jeton.
 *
 * Paramètres (GET) :
 *   - matchId      : identifiant INTERNE Legacy du match (TableMatch.match_id, clé numérique), facultatif
 *   - ligueId      : identifiant de ligue (TableMatch.ligueRef), facultatif
 *   - minChronoMs  : horodatage Unix minimal (ms), facultatif
 *   - maxChronoMs  : horodatage Unix maximal (ms), facultatif
 *   - codes        : codes d'événement, liste séparée par des virgules, facultatif (ex. "0" = but)
 *   - minCameras   : nombre minimal de caméras distinctes par événement (défaut 1)
 *   - limit        : nombre maximal d'événements (défaut 50, borné à 200)
 *
 * Périmètre obligatoire : au moins matchId OU ligueId.
 *
 * Aucune écriture : SELECT uniquement, requêtes paramétrées.
 */

require_once __DIR__ . '/lib/evenements_avec_videos.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function evAvVideosRepondre(int $code, array $corps): void
{
    http_response_code($code);
    echo json_encode($corps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

// --- Méthode ---
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    evAvVideosRepondre(405, ['etat' => 'methode_non_autorisee']);
    return;
}

// --- Authentification (identique aux routes Legacy existantes) ---
require_once __DIR__ . '/../scriptsphp/chargeSecrets.php';
$jetonAttendu = getenv('LEGACY_MATCH_ACTIF_TOKEN');
if ($jetonAttendu === false || trim($jetonAttendu) === '') {
    evAvVideosRepondre(503, ['etat' => 'service_non_configure']);
    return;
}

$autorisation = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/^Bearer ([^\s]+)$/iD', $autorisation, $jeton)
    || !hash_equals($jetonAttendu, $jeton[1])) {
    header('WWW-Authenticate: Bearer');
    evAvVideosRepondre(401, ['etat' => 'non_autorise']);
    return;
}

// --- Validation des paramètres ---
[$ok, $codeErreur, $params] = evAvVideosValiderParametres($_GET);
if (!$ok) {
    $corps = ['etat' => $codeErreur];
    if (!empty($params)) {
        $corps = array_merge($corps, $params);
    }
    evAvVideosRepondre(400, $corps);
    return;
}

// --- Consultation (lecture seule, paramétrée) ---
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/../scriptsphp/defenvvar.php'; // fournit $conn (mysqli)
    if (!($conn instanceof mysqli)) {
        evAvVideosRepondre(500, ['etat' => 'erreur_consultation']);
        return;
    }

    // 1) Sélection bornée des événements multivues.
    $rq = evAvVideosRequeteEvenements($params);
    $stmt = $conn->prepare($rq['sql']);
    $stmt->bind_param($rq['types'], ...$rq['args']);
    $stmt->execute();
    $res = $stmt->get_result();
    $evenements = [];
    $eventIds = [];
    while ($row = $res->fetch_assoc()) {
        $evenements[] = $row;
        $eventIds[] = (int)$row['event_id'];
    }
    $stmt->close();

    // 2) Vidéos découpées des événements sélectionnés.
    // La colonne `duree` peut être absente du schéma déployé : détection
    // défensive (même technique que preEnregistre.php) pour ne jamais échouer.
    $colonneDuree = false;
    $resCol = $conn->query("SHOW COLUMNS FROM Video LIKE 'duree'");
    if ($resCol instanceof mysqli_result) {
        $colonneDuree = $resCol->num_rows > 0;
        $resCol->free();
    }

    // IMPORTANT : aucune limite ici. `limit` et `minCameras` ne s'appliquent
    // qu'à la SÉLECTION des événements (étape 1). Toutes les vidéos découpées
    // des événements retenus sont renvoyées, sans troncature.
    $videos = [];
    if (!empty($eventIds)) {
        $rqV = evAvVideosRequeteVideos($eventIds, $colonneDuree);
        $stmtV = $conn->prepare($rqV['sql']);
        $stmtV->bind_param($rqV['types'], ...$rqV['args']);
        $stmtV->execute();
        $resV = $stmtV->get_result();
        while ($row = $resV->fetch_assoc()) {
            $videos[] = $row;
        }
        $stmtV->close();
    }

    $evenementsFormates = evAvVideosFormerReponse($evenements, $videos);

    evAvVideosRepondre(200, [
        'etat'         => 'ok',
        'perimetre'    => [
            'matchId'     => $params['matchId'],
            'ligueId'     => $params['ligueId'],
            'minChronoMs' => $params['minChronoMs'],
            'maxChronoMs' => $params['maxChronoMs'],
            'codes'       => $params['codes'],
            'minCameras'  => $params['minCameras'],
            'limit'       => $params['limit'],
        ],
        'nbEvenements' => count($evenementsFormates),
        'evenements'   => $evenementsFormates,
    ]);
} catch (Throwable $erreur) {
    error_log('[getEvenementsAvecVideos] ' . $erreur->getMessage());
    evAvVideosRepondre(500, ['etat' => 'erreur_consultation']);
    return;
}
