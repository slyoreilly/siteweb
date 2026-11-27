<?php

declare(strict_types=1);

// Instrumentation temporaire : aucun en-tête ni secret dans les traces.
$debutRequeteMatchActif = microtime(true);
function tracerMatchActif(string $etape): void
{
    global $debutRequeteMatchActif;
    error_log(sprintf('[match-actif-diag-v1 debut=%.6f +%.3f ms] %s',
        $debutRequeteMatchActif, (microtime(true) - $debutRequeteMatchActif) * 1000, $etape));
}
tracerMatchActif('entree_script');

require_once __DIR__ . '/lib/match_actif_surface.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function envoyerReponseMatchActif(int $code, array $corps): void
{
    tracerMatchActif('avant_reponse_http code=' . $code);
    http_response_code($code);
    echo json_encode($corps);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    envoyerReponseMatchActif(405, ['etat' => 'methode_non_autorisee']);
    return;
}

require_once __DIR__ . '/../scriptsphp/chargeSecrets.php';
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

tracerMatchActif('authentification_terminee');

$arenaId = filter_var($_GET['arenaId'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
if ($arenaId === false) {
    envoyerReponseMatchActif(400, ['etat' => 'arena_id_invalide']);
    return;
}

[$code, $corps] = reponseMatchActifSurface($arenaId, static function (int $arenaId): array {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    tracerMatchActif('avant_defenvvar');
    require __DIR__ . '/../scriptsphp/defenvvar.php';
    tracerMatchActif('apres_defenvvar');

    return consulterMatchActifSurface($conn, $arenaId);
});

envoyerReponseMatchActif($code, $corps);
