<?php
declare(strict_types=1);
require_once __DIR__ . '/../../api/lib/publication_verification.php';
require_once __DIR__ . '/../../api/lib/publication_commande.php';

if (($argv[1] ?? '') === 'route') {
    $_SERVER['REQUEST_METHOD'] = $argv[2];
    putenv('LEGACY_MATCH_ACTIF_TOKEN=jeton-test-local');
    $_SERVER['HTTP_AUTHORIZATION'] = $argv[3];
    register_shutdown_function(static function () { echo '|HTTP=' . (http_response_code() ?: 200); });
    require __DIR__ . '/../../api/sync/publication-equipe.php';
    exit;
}

$total = 0;
function exigerPublication(bool $condition): void {
    global $total;
    if (!$condition) throw new RuntimeException('Contrat non respecté, vérification ' . ($total + 1));
    $total++;
}
function refuserPublication(callable $action): void {
    try { $action(); } catch (InvalidArgumentException|JsonException $e) { exigerPublication(true); return; }
    throw new RuntimeException('Refus attendu');
}
function lignesPublication(array $groupes): array {
    $lignes = [];
    foreach ($groupes as $type => $objets) foreach ($objets as $objet) {
        $lignes[] = ['type' => $type, 'donnees' => json_encode($objet, JSON_THROW_ON_ERROR)];
    }
    return $lignes;
}
$periode = ['debut' => '2026-01-01', 'fin' => '2026-12-31'];
$base = [
    'ligue' => [['id' => 1, 'nom' => 'Test']],
    'saison' => [['id' => 2, 'ligueId' => 1] + $periode],
    'equipe' => [['id' => 10, 'nom' => 'Cible']],
    'joueur' => [['id' => 7, 'nom' => 'Joueur test', 'numero' => '0', 'position' => 'C']],
    'equipeLigue' => [['equipeId' => 10, 'ligueId' => 1, 'permission' => 10] + $periode],
    'joueurEquipe' => [['id' => 70, 'joueurId' => 7, 'equipeId' => 10, 'permission' => 10] + $periode],
];
$verifier = static fn($g, $ids = [7], $equipe = 10) =>
    publicationConstruireVerification('test-local', 1, 2, $equipe, $ids, lignesPublication($g));
$r = $verifier($base);
exigerPublication($r['valide'] && $r['lectureComplete'] && !$r['ecrituresAutorisees']);
exigerPublication($r['identites'][0]['inscriptionsCibles'] === [70]);
exigerPublication(!$r['identites'][0]['autresAffiliations']);
exigerPublication($verifier($base)['revision'] === $r['revision']);
$inverse = array_reverse(lignesPublication($base));
exigerPublication(publicationConstruireVerification('test-local', 1, 2, 10, [7], $inverse)['revision'] === $r['revision']);
foreach ([20, 30] as $autre) {
    $g = $base;
    $g['joueurEquipe'][] = ['id' => $autre, 'joueurId' => 7, 'equipeId' => $autre, 'permission' => 10] + $periode;
    $g['equipeLigue'][] = ['equipeId' => $autre, 'ligueId' => $autre === 20 ? 1 : 9, 'permission' => 10] + $periode;
    $copie = $g;
    exigerPublication($verifier($g)['identites'][0]['autresAffiliations']);
    exigerPublication($g === $copie); // Aucune affiliation modifiée par la vérification.
    exigerPublication($verifier($g)['revision'] !== $r['revision']);
}
$g = $base;
$g['equipeLigue'][] = ['equipeId' => 10, 'ligueId' => 9, 'permission' => 10] + $periode;
exigerPublication($verifier($g)['identites'][0]['autresAffiliations']);
$g = $base; $g['joueurEquipe'][] = $g['joueurEquipe'][0];
exigerPublication(!$verifier($g)['valide']);
$g = $base; $g['equipeLigue'][] = $g['equipeLigue'][0];
exigerPublication(!$verifier($g)['valide']);
$g = $base; $g['joueurEquipe'][0]['debut'] = null;
exigerPublication(!$verifier($g)['valide']);
$g = $base; $g['equipeLigue'][0]['debut'] = null;
exigerPublication(!$verifier($g)['valide']);
foreach ([null, '2026-02-30', '0000-00-00', '2027-12-31'] as $debut) {
    $g = $base; $g['saison'][0]['debut'] = $debut;
    exigerPublication(!$verifier($g)['valide']);
}
$g = $base; $g['saison'] = [];
exigerPublication(!$verifier($g)['valide']);
$g = $base; $g['ligue'] = [];
exigerPublication(!$verifier($g)['valide']);
exigerPublication(!$verifier($base, [7, 8])['valide']);
$g = $base; unset($g['equipe'], $g['equipeLigue'], $g['joueurEquipe']);
exigerPublication($verifier($g, [7], null)['valide']); // Préparation de création, sans création réelle.
$g = $base; $g['joueur'][0]['numero'] = '99';
exigerPublication($verifier($g)['revision'] !== $r['revision']);
exigerPublication(publicationIntersection(['debut' => '2026-12-31', 'fin' => '2027-01-01'], $periode));
exigerPublication(!publicationIntersection(['debut' => '2027-01-01', 'fin' => '2027-12-31'], $periode));
exigerPublication(publicationIdentifiantsExacts('9,7') === [7, 9]);
exigerPublication(publicationIdentifiantsExacts('') === []);
foreach ([null, [], '0', '-1', '7,7', '2147483648', '7,', ' 7', '01', implode(',', range(1, 101))] as $ids) {
    refuserPublication(static fn() => publicationIdentifiantsExacts($ids));
}
foreach ([[], [7], range(1, 100)] as $ids) {
    [$sql, $types, $valeurs] = publicationRequeteVerification(1, 2, 10, $ids);
    exigerPublication(substr_count($sql, '?') === count($valeurs) && strlen($types) === count($valeurs));
    exigerPublication(!preg_match('/\b(INSERT|UPDATE|DELETE|LIMIT|COMMIT)\b/i', $sql));
}
// Exemple de sérialisation .NET : le hachage conserve les échappements Unicode et espaces.
$plan = '{"Version":"publication-equipe-v1","NomEquipe":"\\u00C9quipe"}';
$commande = ['cleIdempotence' => 'test', 'empreinte' => strtoupper(hash('sha256', $plan)), 'planJson' => $plan];
exigerPublication(publicationDecoderCommande(json_encode($commande))[1]['NomEquipe'] === 'Équipe');
$commande['planJson'] .= ' ';
refuserPublication(static fn() => publicationDecoderCommande(json_encode($commande)));
$commande['empreinte'] = strtolower($commande['empreinte']);
refuserPublication(static fn() => publicationDecoderCommande(json_encode($commande)));
$commande['planJson'] = '{"Version":"publication-equipe-v2"}';
$commande['empreinte'] = strtoupper(hash('sha256', $commande['planJson']));
refuserPublication(static fn() => publicationDecoderCommande(json_encode($commande)));
refuserPublication(static fn() => publicationDecoderCommande('{'));
$commande = ['cleIdempotence' => str_repeat('é', 128),
    'empreinte' => strtoupper(hash('sha256', $plan)), 'planJson' => $plan];
exigerPublication(publicationDecoderCommande(json_encode($commande))[0]['cleIdempotence'] === str_repeat('é', 128));
$commande['cleIdempotence'] .= 'a';
refuserPublication(static fn() => publicationDecoderCommande(json_encode($commande)));
foreach ([['POST', '', 401], ['GET', 'Bearer faux', 401], ['POST', 'Bearer jeton-test-local', 503],
    ['GET', 'Bearer jeton-test-local', 503], ['DELETE', '', 405]] as [$methode, $autorisation, $statut]) {
    $processus = proc_open([PHP_BINARY, '-n', __FILE__, 'route', $methode, $autorisation],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $canaux);
    $sortie = stream_get_contents($canaux[1]); $erreurs = stream_get_contents($canaux[2]);
    fclose($canaux[1]); fclose($canaux[2]);
    exigerPublication(proc_close($processus) === 0 && str_ends_with($sortie, '|HTTP=' . $statut) && $erreurs === '');
    if ($statut === 503) exigerPublication(str_contains($sortie, 'ECRITURES_BLOQUEES'));
}
echo "$total vérifications destinataire réussies, sans base ni réseau.\n";
