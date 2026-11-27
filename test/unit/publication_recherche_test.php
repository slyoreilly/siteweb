<?php
declare(strict_types=1);

// Exécuter avec php -n : doublure mysqli, aucun secret, réseau ou accès SQL.
// Les requêtes réelles sont exercées séparément par publication_recherche_sql_test.py.
if (isset($argv[1])) {
    if (class_exists('mysqli')) throw new RuntimeException('Utiliser php -n sans extension mysqli.');
    class mysqli {}
    $cas = json_decode(base64_decode($argv[1]), true, 512, JSON_THROW_ON_ERROR);
    $_GET = $cas['parametres'];
    $socle = file_get_contents(__DIR__ . '/../../api/lib/publication_lecture.php');
    $socle = str_replace(['function publicationConnexion(', 'function publicationLire('],
        ['function publicationConnexionOriginale(', 'function publicationLireOriginale('], $socle);
    eval(substr($socle, 5));
    function publicationConnexion(): mysqli { return new mysqli(); }
    function publicationLire(mysqli $conn, string $sql, string $types = '', array $valeurs = []): array
    {
        global $cas;
        if (strlen($types) !== count($valeurs) || substr_count($sql, '?') !== count($valeurs)) {
            throw new RuntimeException('Paramètres SQL incohérents');
        }
        if (str_contains($sql, 'SELECT NOW()')) return [['instant' => '2026-06-15 12:00:00']];
        if (str_contains($sql, 'FROM TableSaison')) return $cas['saisons'] ?? [
            ['premierMatch' => '2026-01-01', 'dernierMatch' => '2026-12-31']];
        if (str_starts_with($sql, 'SELECT equipeId')) return ($cas['horsContexte'] ?? false) ? [] : [['equipeId' => 10]];
        if (str_contains($sql, 'SELECT DISTINCT aje.joueurId')) return $cas['equipes'] ?? [];
        if (isset($cas['motifAttendu']) && end($valeurs) !== $cas['motifAttendu']) {
            throw new RuntimeException('Motif LIKE incorrect');
        }
        $lignes = [];
        for ($i = 1; $i <= ($cas['nombre'] ?? 1); $i++) {
            $ligne = ['id' => (string)$i, 'nom' => "Élodie O'Neil %_!", 'numeroChandail' => '0', 'position' => 'C'];
            if (str_contains($sql, 'contexte.presentDansEquipeCible')) {
                $ligne['presentDansEquipeCible'] = isset($_GET['equipeId']) ? '1' : '0';
            }
            $lignes[] = $ligne;
        }
        return $lignes;
    }
    register_shutdown_function(function () { echo '|HTTP=' . (http_response_code() ?: 200); });
    $route = file_get_contents(__DIR__ . '/../../api/getPublicationRechercheJoueurs.php');
    $route = str_replace("require_once __DIR__ . '/lib/publication_lecture.php';", '', $route);
    eval(substr($route, 5));
    exit;
}

$total = 0;
function consulter(array $cas, int $statut = 200, ?string $etat = null): array
{
    global $total;
    $processus = proc_open([PHP_BINARY, '-n', __FILE__, base64_encode(json_encode($cas, JSON_THROW_ON_ERROR))],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $canaux);
    $sortie = stream_get_contents($canaux[1]);
    $erreurs = stream_get_contents($canaux[2]);
    fclose($canaux[1]); fclose($canaux[2]);
    $code = proc_close($processus);
    if ($code !== 0 || !str_ends_with($sortie, '|HTTP=' . $statut)) {
        throw new RuntimeException('Statut incorrect : ' . $sortie . ' ' . $erreurs);
    }
    $corps = json_decode(explode('|HTTP=', $sortie)[0], true, 512, JSON_THROW_ON_ERROR);
    if ($etat !== null && ($corps['etat'] ?? null) !== $etat) throw new RuntimeException('État incorrect');
    $total++;
    return $corps;
}
function exiger(bool $condition): void
{
    if (!$condition) throw new RuntimeException('Contrat JSON incorrect');
}
$global = consulter(['parametres' => ['nom' => " O'Neil %_! "], 'motifAttendu' => "%O'Neil !%!_!!%"]);
$joueur = $global['elements'][0];
exiger($global['lectureComplete'] === true && $joueur === [
    'id' => 1, 'prenom' => null, 'nom' => "Élodie O'Neil %_!", 'hcr' => null,
    'numeroChandail' => '0', 'position' => 'C', 'inscriptionId' => null,
    'provenanceInscription' => 'inconnu', 'debutAbon' => null, 'finAbon' => null,
    'equipesDansLigue' => [], 'presentDansEquipeCible' => false]);
$parametres = ['nom' => 'Joueur', 'ligueId' => '1', 'saisonId' => '2', 'equipeId' => '10'];
$contextuel = consulter(['parametres' => $parametres, 'equipes' => [
    ['joueurId' => '1', 'id' => '20', 'nom' => 'Autre'], ['joueurId' => '1', 'id' => '10', 'nom' => 'Cible']]]);
exiger($contextuel['elements'][0]['presentDansEquipeCible'] === true);
exiger($contextuel['elements'][0]['equipesDansLigue'] === [['id' => 20, 'nom' => 'Autre'], ['id' => 10, 'nom' => 'Cible']]);
foreach ([0, 50, 51] as $nombre) {
    foreach ([['nom' => 'Joueur'], $parametres] as $contexte) {
        $corps = consulter(['parametres' => $contexte, 'nombre' => $nombre]);
        exiger(count($corps['elements']) === min(50, $nombre) && $corps['lectureComplete'] === ($nombre <= 50));
    }
}
$sansCible = consulter(['parametres' => ['nom' => 'Joueur', 'ligueId' => '1']]);
exiger($sansCible['elements'][0]['presentDansEquipeCible'] === false);
consulter(['parametres' => $parametres, 'horsContexte' => true], 422, 'equipe_hors_contexte');
foreach ([[], [['premierMatch' => null, 'dernierMatch' => null]],
    [['premierMatch' => '2026-12-31', 'dernierMatch' => '2026-01-01']]] as $saisons) {
    consulter(['parametres' => $parametres, 'saisons' => $saisons], 422, 'saison_inexploitable');
}
foreach (['ligueId', 'saisonId', 'equipeId'] as $cle) {
    foreach (['0', '-1', '2147483648', ['1']] as $invalide) {
        consulter(['parametres' => array_replace($parametres, [$cle => $invalide])], 400, 'parametre_invalide');
    }
}
foreach (['', 'ab', str_repeat('a', 121), ['abc']] as $nom) {
    consulter(['parametres' => ['nom' => $nom]], 400, 'parametre_invalide');
}
foreach (['saisonId', 'equipeId'] as $cle) {
    consulter(['parametres' => ['nom' => 'Joueur', $cle => '1']], 400, 'parametre_invalide');
}
echo $total . " scénarios PHP réussis, sans connexion SQL.\n";
