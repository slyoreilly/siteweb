<?php
namespace AcquittementsConnexions;
use Throwable;

if ($argc === 1) {
    foreach (['inactif', 'succes', 'rejet', 'auth', 'reessai', 'limite', 'exception'] as $cas) {
        passthru(escapeshellarg(PHP_BINARY) . ' -n ' . escapeshellarg(__FILE__) . ' ' . $cas, $code);
        if ($code !== 0) { exit($code); }
    }
    exit(0);
}
function verifier($condition, $message) {
    if (!$condition) { throw new \RuntimeException($message); }
}
class mysqli { public $ouverte = true; public $verrou = false; }
class SyncAckClient {
    public function sendOnce($charge) {
        verifier($GLOBALS['conn']->ouverte && $GLOBALS['conn']->verrou, 'Verrou absent durant HTTP');
        verifier($charge === ['entity'=>'Event', 'sourceEntityId'=>42, 'dedupeKey'=>'cle-test', 'upstreamId'=>'amont-42'], 'Charge ACK modifiée');
        if ($GLOBALS['cas'] === 'exception') { throw new \RuntimeException('réseau simulé'); }
        $resultats = ['succes'=>'success', 'rejet'=>'rejected', 'auth'=>'failed_auth', 'reessai'=>'technical', 'limite'=>'technical'];
        return ['result'=>$resultats[$GLOBALS['cas']], 'http_code'=>200, 'error'=>'simulé', 'latency_ms'=>4];
    }
    public function maxAttempts() { return 6; }
    public function backoffSecondsForAttempt($tentative) { return 10; }
}
function syncAckClient() { return new SyncAckClient(); }
function ouvrir() {
    verifier(!isset($GLOBALS['conn']) || $GLOBALS['conn'] === null, 'Nouvelle connexion prématurée');
    $GLOBALS['conn'] = new mysqli();
    $GLOBALS['ouvertures']++;
}
function mysqli_close($connexion) {
    verifier($connexion->ouverte && !$connexion->verrou, 'Fermeture avec verrou actif');
    $connexion->ouverte = false;
    $GLOBALS['fermetures']++;
}
function mysqli_prepare($connexion, $sql) { verifier($connexion->ouverte, 'SQL après fermeture'); return (object)['connexion'=>$connexion]; }
function mysqli_stmt_bind_param($stmt, $types, &...$valeurs) { return true; }
function mysqli_stmt_execute($stmt) { return true; }
function mysqli_stmt_get_result($stmt) {
    $lignes = $GLOBALS['cas'] === 'inactif' || $GLOBALS['fermetures'] > 0 ? [] : [[
        'syncInboxId'=>3, 'endpoint'=>'event', 'dedupeKey'=>'cle-test', 'source_entity_id'=>42,
        'upstream_id'=>'amont-42', 'ack_attempts'=>$GLOBALS['cas'] === 'limite' ? 5 : 1]];
    return (object)['lignes'=>$lignes];
}
function mysqli_fetch_assoc($res) { return array_shift($res->lignes); }
function mysqli_stmt_close($stmt) {}
function syncAcquireRowLock($connexion, $id) { $connexion->verrou = true; return true; }
function syncReleaseRowLock($connexion, $id) { $connexion->verrou = false; $GLOBALS['liberations']++; }
function syncToNullableInt($valeur) { return $valeur === null ? null : (int)$valeur; }
function syncToInt($valeur) { return (int)$valeur; }
function marquer($type, $connexion, $id, $tentatives) {
    verifier($connexion->ouverte && $connexion->verrou, 'Écriture sans verrou');
    $GLOBALS['marquages'][] = [$type, $id, $tentatives];
}
function syncInboxMarkAckSuccess($connexion, $id, $tentatives, $code) { marquer('succes', $connexion, $id, $tentatives); }
function syncInboxMarkAckRejected($connexion, $id, $tentatives, $code, $erreur) { marquer('rejet', $connexion, $id, $tentatives); }
function syncInboxMarkAckFailedAuth($connexion, $id, $tentatives, $code, $erreur) { marquer('auth', $connexion, $id, $tentatives); }
function syncInboxMarkAckMaxAttempts($connexion, $id, $tentatives, $code, $erreur) { marquer('limite', $connexion, $id, $tentatives); }
function syncInboxMarkAckRetry($connexion, $id, $tentatives, $code, $erreur, $date) {
    verifier(strtotime($date) >= time(), 'Réessai programmé dans le passé');
    marquer('reessai', $connexion, $id, $tentatives);
}
function sleep($secondes) {
    verifier($GLOBALS['conn'] === null && $secondes === 1, 'Connexion conservée pendant attente worker');
    $GLOBALS['attentes']++;
}
$cas = $argv[1];
$argv = ['ack_worker.php', '--loop'];
$ouvertures = $fermetures = $liberations = $attentes = 0;
$marquages = [];
$source = file_get_contents(__DIR__ . '/../../api/sync/ack_worker.php');
$source = str_replace('declare(strict_types=1);', 'declare(strict_types=1); namespace AcquittementsConnexions; use Throwable;', $source);
$source = str_replace("require __DIR__ . '/bootstrap.php';", 'ouvrir();', $source, $nombre);
verifier($nombre === 1, 'Bootstrap inattendu');
$source = str_replace("require __DIR__ . '/../../scriptsphp/defenvvar.php';", 'ouvrir();', $source, $nombre);
verifier($nombre === 1, 'Réouverture inattendue');
$source = str_replace('} while (true);', '} while ($GLOBALS[\'fermetures\'] < 2);', $source);
$source = str_replace('exit(0);', '', $source);
eval(substr($source, 5));
verifier($conn === null && $ouvertures === 2 && $fermetures === 2, 'Deux lots avec connexions distinctes attendus');
verifier($attentes >= 1, 'Attente inactive non exercée');
verifier($liberations === ($cas === 'inactif' ? 0 : 1), 'Verrou non libéré');
if ($cas === 'inactif' || $cas === 'exception') { verifier($marquages === [], 'Écriture inattendue'); }
else { verifier($marquages === [[$cas, 3, $cas === 'limite' ? 6 : 2]], 'Résultat ACK modifié'); }
echo 'OK worker ' . $cas . PHP_EOL;
