<?php
declare(strict_types=1);

// Socle exclusif des consultations de publication : GET + transaction READ ONLY.
function publicationRepondre(int $statut, mixed $corps): never
{
    http_response_code($statut);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($corps, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function publicationConnexion(): mysqli
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
        header('Allow: GET');
        publicationRepondre(405, ['etat' => 'methode_non_autorisee']);
    }
    publicationAuthentifier();
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/../../scriptsphp/defenvvar.php';
    $conn->set_charset('utf8mb4');
    $conn->query('START TRANSACTION READ ONLY');
    return $conn;
}

// Réutilisable sans connexion SQL par le destinataire d'écriture bloqué.
function publicationAuthentifier(): void
{
    require_once __DIR__ . '/../../scriptsphp/chargeSecrets.php';
    $attendu = getenv('LEGACY_MATCH_ACTIF_TOKEN');
    if ($attendu === false || trim($attendu) === '') {
        publicationRepondre(503, ['etat' => 'service_non_configure']);
    }
    $autorisation = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer ([^\s]+)$/iD', $autorisation, $jeton) || !hash_equals($attendu, $jeton[1])) {
        header('WWW-Authenticate: Bearer');
        publicationRepondre(401, ['etat' => 'non_autorise']);
    }
}

function publicationEntier(string $cle, bool $facultatif = false): ?int
{
    $valeur = $_GET[$cle] ?? null;
    if ($valeur === null && $facultatif) return null;
    if (!is_scalar($valeur) || !ctype_digit((string)$valeur) || (int)$valeur <= 0
        || (float)$valeur > 2147483647) {
        publicationRepondre(400, ['etat' => 'parametre_invalide', 'detail' => $cle . ' entier positif requis']);
    }
    return (int)$valeur;
}

function publicationLire(mysqli $conn, string $sql, string $types = '', array $valeurs = []): array
{
    $requete = $conn->prepare($sql);
    if ($types !== '') $requete->bind_param($types, ...$valeurs);
    $requete->execute();
    $lignes = $requete->get_result()->fetch_all(MYSQLI_ASSOC);
    $requete->close();
    return $lignes;
}

function publicationPeriode(mysqli $conn, int $ligueId, ?int $saisonId): array
{
    if ($saisonId === null) {
        $instant = publicationLire($conn, 'SELECT NOW() AS instant')[0]['instant'];
        return [$instant, $instant];
    }
    $saisons = publicationLire($conn,
        'SELECT premierMatch, dernierMatch FROM TableSaison WHERE ligueRef = ? AND saisonId = ?',
        'ii', [$ligueId, $saisonId]);
    if (count($saisons) !== 1 || !$saisons[0]['premierMatch'] || !$saisons[0]['dernierMatch']
        || $saisons[0]['premierMatch'] > $saisons[0]['dernierMatch']) {
        publicationRepondre(422, ['etat' => 'saison_inexploitable']);
    }
    return [$saisons[0]['premierMatch'], $saisons[0]['dernierMatch']];
}

function publicationJoueur(array $ligne): array
{
    return [
        'id' => (int)$ligne['id'], 'prenom' => null, 'nom' => $ligne['nom'],
        'hcr' => null, 'numeroChandail' => isset($ligne['numeroChandail']) ? (string)$ligne['numeroChandail'] : null,
        'position' => isset($ligne['position']) ? (string)$ligne['position'] : null,
        'inscriptionId' => isset($ligne['inscriptionId']) ? (int)$ligne['inscriptionId'] : null,
        'provenanceInscription' => 'inconnu',
        'debutAbon' => $ligne['debutAbon'] ?? null, 'finAbon' => $ligne['finAbon'] ?? null,
    ];
}

function publicationErreur(Throwable $erreur): never
{
    error_log('[publication-lecture] ' . $erreur->getMessage());
    publicationRepondre(503, ['etat' => 'erreur_consultation']);
}
