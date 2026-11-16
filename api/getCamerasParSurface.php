<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/cameras_surface.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function envoyerReponseCamerasSurface(int $code, array $corps): void
{
    http_response_code($code);
    echo json_encode($corps);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    envoyerReponseCamerasSurface(405, ['etat' => 'methode_non_autorisee']);
    return;
}

$jetonAttendu = getenv('LEGACY_MATCH_ACTIF_TOKEN');
if ($jetonAttendu === false || trim($jetonAttendu) === '') {
    envoyerReponseCamerasSurface(503, ['etat' => 'service_non_configure']);
    return;
}

$autorisation = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/^Bearer ([^\s]+)$/iD', $autorisation, $jeton)
    || !hash_equals($jetonAttendu, $jeton[1])) {
    header('WWW-Authenticate: Bearer');
    envoyerReponseCamerasSurface(401, ['etat' => 'non_autorise']);
    return;
}

$arenaId = filter_var($_GET['arenaId'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
if ($arenaId === false) {
    envoyerReponseCamerasSurface(400, ['etat' => 'arena_id_invalide']);
    return;
}

[$code, $corps] = reponseCamerasSurface($arenaId, static function (int $arenaId): array {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $configuration = __DIR__ . '/../scriptsphp/defenvvar.php';
    if (!is_readable($configuration)) {
        throw new RuntimeException('Configuration de la base indisponible');
    }
    require $configuration;

    $requete = $conn->prepare(REQUETE_CAMERAS_SURFACE);
    $requete->bind_param('i', $arenaId);
    $requete->execute();
    $requete->bind_result($telId);

    $lignes = [];
    while ($requete->fetch()) {
        $lignes[] = $telId;
    }
    $requete->close();
    return $lignes;
});

envoyerReponseCamerasSurface($code, $corps);
