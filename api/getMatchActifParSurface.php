<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/match_actif_surface.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function envoyerReponseMatchActif(int $code, array $corps): void
{
    http_response_code($code);
    echo json_encode($corps);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    envoyerReponseMatchActif(405, ['etat' => 'methode_non_autorisee']);
    return;
}

$jetonAttendu = getenv('LEGACY_MATCH_ACTIF_TOKEN');
if ($jetonAttendu === false || trim($jetonAttendu) === '') {
    envoyerReponseMatchActif(503, ['etat' => 'service_non_configure']);
    return;
}

$autorisation = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/^Bearer ([^\s]+)$/iD', $autorisation, $jeton)
    || !hash_equals($jetonAttendu, $jeton[1])) {
    header('WWW-Authenticate: Bearer');
    envoyerReponseMatchActif(401, ['etat' => 'non_autorise']);
    return;
}

$arenaId = filter_var($_GET['arenaId'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
if ($arenaId === false) {
    envoyerReponseMatchActif(400, ['etat' => 'arena_id_invalide']);
    return;
}

[$code, $corps] = reponseMatchActifSurface($arenaId, static function (int $arenaId): array {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/../scriptsphp/defenvvar.php';

    $requete = $conn->prepare(REQUETE_MATCH_ACTIF_SURFACE);
    $requete->bind_param('i', $arenaId);
    $requete->execute();
    $requete->bind_result($surface, $matchId, $debutMs);

    $lignes = [];
    while ($requete->fetch()) {
        $lignes[] = ['match_id' => $matchId, 'debutMs' => $debutMs];
    }
    $requete->close();
    return $lignes;
});

envoyerReponseMatchActif($code, $corps);
