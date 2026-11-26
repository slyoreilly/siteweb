<?php
declare(strict_types=1);

// Tests isolés : aucun accès SQL, aucun secret réel, aucun appel réseau.
$socle = realpath(__DIR__ . '/../../api/lib/publication_lecture.php');
$commun = 'require ' . var_export($socle, true) . '; register_shutdown_function(function () { echo "|HTTP=" . (http_response_code() ?: 200); });';
$total = 0;
function verifier(string $code, string $attendu): void
{
    global $commun, $total;
    $processus = proc_open([PHP_BINARY, '-r', $commun . $code], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $canaux);
    $sortie = stream_get_contents($canaux[1]);
    $erreurs = stream_get_contents($canaux[2]);
    fclose($canaux[1]); fclose($canaux[2]);
    $statut = proc_close($processus);
    if ($statut !== 0 || !str_contains($sortie, $attendu)) {
        throw new RuntimeException('Échec : ' . $attendu . ' ; sortie=' . $sortie . ' ; erreurs=' . $erreurs);
    }
    $total++;
}
verifier('$_SERVER["REQUEST_METHOD"]="POST"; publicationConnexion();', '|HTTP=405');
verifier('$_SERVER["REQUEST_METHOD"]="GET"; putenv("LEGACY_MATCH_ACTIF_TOKEN="); publicationConnexion();', '|HTTP=503');
verifier('$_SERVER["REQUEST_METHOD"]="GET"; putenv("LEGACY_MATCH_ACTIF_TOKEN=jeton-test"); publicationConnexion();', '|HTTP=401');
verifier('$_SERVER["REQUEST_METHOD"]="GET"; putenv("LEGACY_MATCH_ACTIF_TOKEN=jeton-test"); $_SERVER["HTTP_AUTHORIZATION"]="Bearer incorrect"; publicationConnexion();', '|HTTP=401');
verifier('$_GET["id"]="314"; echo publicationEntier("id");', '314|HTTP=200');
verifier('var_export(publicationEntier("id", true));', 'NULL|HTTP=200');
foreach (['0', '-1', '1.2', '2147483648', '', ['314']] as $invalide) {
    verifier('$_GET["id"]=' . var_export($invalide, true) . '; publicationEntier("id");', '|HTTP=400');
}
verifier('$j=publicationJoueur(["id"=>17,"nom"=>"Candidat","numeroChandail"=>0,"inscriptionId"=>19]); echo json_encode([$j["id"],$j["hcr"],$j["numeroChandail"],$j["inscriptionId"],$j["provenanceInscription"]]);', '[17,null,"0",19,"inconnu"]');
echo $total . " vérifications PHP réussies, sans connexion SQL.\n";
