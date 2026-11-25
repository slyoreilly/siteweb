<?php
namespace MonitoringConnexions;
use Exception;

if ($argc === 1) {
    foreach (['non_vide', 'pagination', 'erreur_sql', 'refus_auth'] as $cas) {
        passthru(escapeshellarg(PHP_BINARY) . ' -n ' . escapeshellarg(__FILE__) . ' ' . $cas, $code);
        if ($code !== 0) { exit($code); }
    }
    exit(0);
}
function verifier($condition, $message) {
    if (!$condition) { throw new \RuntimeException($message); }
}
class ReponseTerminee extends \Exception {}
class mysqli { public $ouverte = true; }
class RequeteFictive { public $sorties = []; public $position = 0; public $fermee = false; }
function mysqli_prepare($connexion, $sql) {
    verifier($connexion->ouverte, 'Préparation sur connexion fermée');
    $GLOBALS['stmt'] = new RequeteFictive();
    return $GLOBALS['stmt'];
}
function mysqli_stmt_bind_param($stmt, $types, &...$valeurs) { return true; }
function mysqli_stmt_execute($stmt) { return $GLOBALS['cas'] !== 'erreur_sql'; }
function mysqli_stmt_error($stmt) { return 'erreur simulée'; }
function mysqli_stmt_bind_result($stmt, &...$sorties) { $stmt->sorties = &$sorties; }
function mysqli_stmt_fetch($stmt) {
    $lignes = [['cam-test', 9, 'compte-test', '2026-11-25 12:00:00'], ['cam-suivante', 10, 'autre-compte', '2026-11-25 11:00:00']];
    if ($stmt->position >= ($GLOBALS['cas'] === 'pagination' ? 2 : 1)) { return null; }
    foreach ($lignes[$stmt->position++] as $i => $valeur) { $stmt->sorties[$i] = $valeur; }
    return true;
}
function mysqli_stmt_close($stmt) { $stmt->fermee = true; }
function mysqli_close($connexion) {
    verifier($GLOBALS['stmt']->fermee, 'Statement encore utilisé à la fermeture');
    $connexion->ouverte = false;
}
function getenv($nom) { return 'jeton-test'; }
function realpath($chemin) {
    if (strpos($chemin, '/../monitoring') !== false) { return '/fictif/monitoring'; }
    verifier($GLOBALS['conn'] === null, 'Connexion conservée pendant inspection des dossiers');
    return $chemin;
}
function is_dir($chemin) { return true; }
function is_file($chemin) {
    verifier($GLOBALS['conn'] === null, 'Connexion conservée pendant inspection des fichiers');
    return basename($chemin) === 'synccamlog.txt';
}
function is_readable($chemin) { return true; }
function filesize($chemin) { return 123; }
function filemtime($chemin) { return 1700000000; }
$cas = $argv[1];
$_SERVER = ['REQUEST_METHOD'=>'GET', 'HTTP_HOST'=>'syncstats.test', 'HTTPS'=>'on', 'HTTP_X_MONITORING_INDEX_TOKEN'=>$cas === 'refus_auth' ? 'incorrect' : 'jeton-test'];
$_GET = ['days'=>'1', 'limit'=>$cas === 'pagination' ? '1' : '100'];
$conn = new mysqli();
$source = file_get_contents(__DIR__ . '/../../scriptsphp/getMonitoringFilesIndex.php');
$source = str_replace("require '../scriptsphp/defenvvar.php';", '', $source, $remplacements);
verifier($remplacements === 1, 'Inclusion inattendue');
$source = str_replace('<?php', '<?php namespace MonitoringConnexions; use Exception;', $source);
$source = str_replace('    exit;', '    throw new ReponseTerminee();', $source);
ob_start();
try { eval('?>' . $source); } catch (ReponseTerminee $e) {}
$sortie = ob_get_clean();
$reponse = json_decode($sortie, true);
verifier(is_array($reponse), 'Réponse JSON invalide : ' . $sortie);
if ($cas === 'refus_auth') {
    verifier(http_response_code() === 403 && !isset($stmt), 'Authentification modifiée');
} elseif ($cas === 'erreur_sql') {
    verifier(http_response_code() === 500 && $conn === null, 'Connexion non libérée sur erreur SQL');
} else {
    verifier(http_response_code() === 200 && $conn === null, 'Réponse ou fermeture incorrecte');
    verifier(count($reponse['items']) === 1 && $reponse['items'][0]['telId'] === 'cam-test', 'Caméra perdue');
    verifier($reponse['hasMore'] === ($cas === 'pagination'), 'Pagination modifiée');
    $fichier = $reponse['items'][0]['dates'][0]['files'][0];
    verifier($fichier['name'] === 'synccamlog.txt' && $fichier['size'] === 123, 'Métadonnées perdues');
    verifier(strpos($fichier['url'], 'https://syncstats.test/monitoring/cam-test/') === 0, 'URL de fichier modifiée');
}
echo 'OK monitoring ' . $cas . PHP_EOL;
