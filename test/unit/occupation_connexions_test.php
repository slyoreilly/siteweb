<?php
namespace OccupationConnexions;
// Exécuter avec php -n : doublures SQL/réseau, aucune connexion réelle.
if ($argc === 1) {
    foreach (['stream', 'stream_modifie', 'stream_differe', 'sync', 'sync1', 'sync2', 'sync_donnees', 'sync1_donnees', 'sync2_donnees', 'sync2_demandes', 'sync2_demandes_differees', 'appareils', 'appareils_timeout', 'environnement', 'environnement_exemple'] as $cas) {
        passthru(escapeshellarg(PHP_BINARY) . ' -n ' . escapeshellarg(__FILE__) . ' ' . $cas, $code);
        if ($code !== 0) { exit($code); }
    }
    exit(0);
}
function verifier($condition, $message) {
    if (!$condition) { throw new \RuntimeException($message); }
}
class mysqli { public $ouverte = true; public $charset; }
class ResultatFictif {
    public $lignes;
    public function __construct($lignes = []) { $this->lignes = $lignes; }
}
$ouvertures = 0;
$attentes = 0;
$appelsHttp = 0;
$requetes = [];
$demandesTraitees = [];
$sondes = 0;
function mysqli_connect($hote, $utilisateur, $motDePasse, $base, $port = null) {
    global $conn, $ouvertures;
    verifier($conn === null, 'Ouverture multiple sans fermeture');
    $ouvertures++;
    return new mysqli();
}
function mysqli_set_charset($connexion, $charset) { $connexion->charset = $charset; return true; }
// Les valeurs privées ne sont ni chargées ni exportées par les tests.
function getenv($nom) { return $nom === 'WORK_ENV' ? ($GLOBALS['environnementTest'] ?? 'development') : 'test'; }
function putenv($valeur) { return true; }
function ouvrirConnexionFictive() {
    global $conn;
    $nom = $GLOBALS['cas'] === 'environnement_exemple' ? 'defenvvar.sample.php' : 'defenvvar.php';
    $source = file_get_contents(__DIR__ . '/../../scriptsphp/' . $nom);
    $source = preg_replace("~require_once __DIR__ \\. '/chargeSecrets\\.php';~", '', $source, 1, $nombre);
    verifier($nombre === 1, 'Chargement des secrets inattendu');
    $source = str_replace('<?php', '<?php namespace OccupationConnexions;', $source);
    eval('?>' . $source);
    verifier($conn->charset === 'utf8', 'Charset perdu à la réouverture');
}
function mysqli_close($connexion) {
    verifier($connexion->ouverte, 'Double fermeture');
    $connexion->ouverte = false;
}
function mysqli_query($connexion, $sql) {
    verifier($connexion instanceof mysqli && $connexion->ouverte, 'SQL sur connexion fermée');
    $GLOBALS['requetes'][] = $sql;
    if ($sql === 'SELECT 1') { $GLOBALS['sondes']++; return new ResultatFictif([[1]]); }
    $cas = $GLOBALS['cas'];
    $avecDonnees = strpos($cas, '_donnees') !== false;
    $avecDemandes = strpos($cas, 'sync2_demandes') === 0;
    $sqlNormalise = preg_replace('/\s+/', ' ', trim($sql));
    if (($cas === 'stream_modifie' || ($cas === 'stream_differe' && $GLOBALS['attentes'] > 0)) && strpos($sql, 'FROM Stream') !== false) {
        return new ResultatFictif([['updatedAt'=>'2026-11-26', 'settings'=>'{"resolution":"1080p","active":true}', 'status'=>'actif']]);
    }
    if ($avecDemandes) {
        if (strpos($sqlNormalise, 'SELECT demandeId, eventId, typeEvenement, chronoDemande') === 0) {
            return new ResultatFictif([
                ['demandeId'=>7, 'eventId'=>101, 'typeEvenement'=>'0', 'chronoDemande'=>1700000000000, 'cameraId'=>9],
                ['demandeId'=>8, 'eventId'=>102, 'typeEvenement'=>'5', 'chronoDemande'=>1700000000001, 'cameraId'=>9],
                ['demandeId'=>9, 'eventId'=>103, 'typeEvenement'=>'0', 'chronoDemande'=>1700000000002, 'cameraId'=>10]]);
        }
        if (strpos($sqlNormalise, 'SELECT TableMatch.arenaId') === 0) {
            return new ResultatFictif([['arenaId'=>strpos($sql, "event_id='103'") !== false ? 2 : 1]]);
        }
        if (preg_match("/UPDATE DemandeAjoutVideo SET progression=2, chronoVideo='([0-9]+)'.*demandeId='([0-9]+)'/", $sqlNormalise, $captures)) {
            verifier(!isset($GLOBALS['demandesTraitees'][$captures[2]]), 'Demande mise à jour plusieurs fois');
            $GLOBALS['demandesTraitees'][$captures[2]] = (int)$captures[1];
            return true;
        }
        if (strpos($sqlNormalise, 'SELECT arenaId, eq_dom') === 0) {
            return new ResultatFictif([['arenaId'=>1, 'eq_dom'=>'Domicile', 'eq_vis'=>'Visiteurs', 'ligueRef'=>4]]);
        }
        if (strpos($sqlNormalise, 'SELECT demandeId, eventId, typeEvenement, chronoVideo') === 0) {
            return new ResultatFictif([
                ['demandeId'=>7, 'eventId'=>101, 'typeEvenement'=>'0', 'chronoVideo'=>$GLOBALS['demandesTraitees'][7], 'cameraId'=>9],
                ['demandeId'=>8, 'eventId'=>102, 'typeEvenement'=>'5', 'chronoVideo'=>$GLOBALS['demandesTraitees'][8], 'cameraId'=>9]]);
        }
    }
    if ($avecDonnees || $avecDemandes) {
        if (stripos($sqlNormalise, 'SELECT * From TableArena') === 0) {
            return new ResultatFictif([['nomArena'=>'Aréna test', 'nomGlace'=>'Glace 1']]);
        }
        if (stripos($sqlNormalise, 'SELECT telId, abonAppareilMatch.role') === 0) {
            return new ResultatFictif([['telId'=>'remote-test', 'role'=>1]]);
        }
        if (strpos($sqlNormalise, 'SELECT * FROM Ligue') === 0) {
            return new ResultatFictif([['ID_Ligue'=>4, 'Nom_Ligue'=>'Ligue test', 'cleValeur'=>'{"parametres":{"sport":"soccer"}}']]);
        }
        if (strpos($sqlNormalise, 'SELECT * FROM (') === 0 || strpos($sqlNormalise, 'SELECT e.event_id') === 0) {
            if ($cas === 'sync2_demandes_differees' && $GLOBALS['attentes'] === 0) { return new ResultatFictif(); }
            $base = ['matchIdRef'=>'match-test', 'eq_dom'=>'Domicile', 'eq_vis'=>'Visiteurs', 'ligueRef'=>4, 'match_id'=>42, 'arenaId'=>1, 'date'=>'2026-11-25', 'LeagueId'=>4, 'ActivationFlags'=>3, 'ActivationArgs'=>'{"camera":9}', 'defaultDuration'=>12];
            if ($cas === 'sync_donnees') { return new ResultatFictif([$base + [0=>1700000000000, 1=>'match-test']]); }
            return new ResultatFictif([
                $base + ['event_id'=>101, 'chrono'=>1700000000000, 'type'=>0, 'scoringEnd'=>1, 'code'=>0, 'souscode'=>0],
                $base + ['event_id'=>102, 'chrono'=>1700000001000, 'type'=>5, 'scoringEnd'=>2, 'code'=>5, 'souscode'=>0],
                $base + ['event_id'=>103, 'chrono'=>1700000002000, 'type'=>0, 'scoringEnd'=>0, 'code'=>11, 'souscode'=>2]]);
        }
        if (strpos($sqlNormalise, 'SELECT event_id,chrono,match_event_id') === 0) {
            return new ResultatFictif([
                ['event_id'=>101, 'chrono'=>1700000000000, 'match_event_id'=>'match-test', 'equipe_event_id'=>1, 'code'=>0, 'souscode'=>0, 'match_id'=>42, 'ligueRef'=>4],
                ['event_id'=>103, 'chrono'=>1700000002000, 'match_event_id'=>'match-test', 'equipe_event_id'=>0, 'code'=>11, 'souscode'=>2, 'match_id'=>42, 'ligueRef'=>4]]);
        }
        if (strpos($sqlNormalise, 'SELECT clipId,chrono,Clips.matchId') === 0) {
            return new ResultatFictif([['clipId'=>102, 'chrono'=>1700000001000, 'match_id'=>42, 'ligueRef'=>4, 'scoringEnd'=>2, 1=>1700000001000, 2=>'match-test']]);
        }
    }
    if (strpos($sql, 'FROM StatutCam') !== false) {
        return new ResultatFictif([['camId'=>1, 'memoire'=>5, 'batterie'=>90, 'codeEtat'=>'0', 'dernierMaJ'=>'2026-11-25']]);
    }
    if (strpos($sql, 'FROM Video') !== false) {
        return new ResultatFictif([['nomFichier'=>'absent.mp4', 'chrono'=>'1700000000000'], ['nomFichier'=>'present.mp4', 'chrono'=>'1699999999000']]);
    }
    if (strpos($sql, 'FROM StatutRemote') !== false) {
        return new ResultatFictif([['telId'=>'remote-test', 'codeEtat'=>'0', 'dernierModif'=>'2026-11-25', 'dernierMaJ'=>'2026-11-25', 'batterie'=>80, 'remoteId'=>2, 'role'=>1, 'memoire'=>12000000]]);
    }
    return new ResultatFictif();
}
function mysqli_fetch_array($resultat) { return array_shift($resultat->lignes); }
function mysqli_fetch_assoc($resultat) { return array_shift($resultat->lignes); }
function mysqli_num_rows($resultat) { return count($resultat->lignes); }
function mysqli_free_result($resultat) {}
function mysqli_real_escape_string($connexion, $valeur) { return $valeur; }
function attenteFictive($secondes) {
    global $conn, $attentes;
    verifier($conn === null, 'Connexion conservée pendant sleep');
    verifier($secondes === 5, 'Cadence modifiée');
    $attentes++;
    return 0;
}
foreach (['CURLOPT_NOBODY', 'CURLOPT_RETURNTRANSFER', 'CURLOPT_CONNECTTIMEOUT', 'CURLOPT_TIMEOUT', 'CURLINFO_HTTP_CODE'] as $i => $nom) { if (!defined($nom)) { define($nom, $i + 1); } }
function curl_init($url) { return (object)['url'=>$url, 'options'=>[]]; }
function curl_setopt($curl, $option, $valeur) { $curl->options[$option] = $valeur; }
function curl_exec($curl) {
    global $conn, $appelsHttp;
    verifier($conn === null, 'Connexion conservée pendant HTTP');
    verifier($curl->options[CURLOPT_CONNECTTIMEOUT] === 2 && $curl->options[CURLOPT_TIMEOUT] === 4, 'Délais HTTP manquants');
    $appelsHttp++;
    return $GLOBALS['cas'] === 'appareils_timeout' ? false : '';
}
function curl_getinfo($curl, $option) { return $GLOBALS['cas'] === 'appareils_timeout' ? 0 : (strpos($curl->url, 'present.mp4') !== false ? 200 : 404); }
function curl_close($curl) {}
function utf8_encode($texte) { return $texte; }
$cas = $argv[1];
if ($cas === 'environnement' || $cas === 'environnement_exemple') {
  foreach (['development', 'production'] as $environnementTest) {
    $ouvertures = $sondes = 0;
    $conn = null;
    ouvrirConnexionFictive();
    $premiere = $conn;
    ouvrirConnexionFictive();
    verifier($conn === $premiere && $ouvertures === 1 && $sondes === 1, 'Réutilisation historique perdue');
    for ($i = 0; $i < 3; $i++) {
        mysqli_close($conn);
        $conn = null;
        ouvrirConnexionFictive();
        verifier($conn !== $premiere, 'Ancien objet fermé réutilisé');
        $premiere = $conn;
    }
    verifier($ouvertures === 4 && $sondes === 1, 'SELECT 1 tenté sur un objet fermé');
    mysqli_close($conn);
    $conn = null;
    echo 'OK ' . $cas . ' ' . $environnementTest . ' : réutilisation puis trois réouvertures' . PHP_EOL;
  }
    exit(0);
}
$fichiers = ['stream_modifie'=>'scriptsphp/getStreamDeviceConfig.php', 'stream'=>'scriptsphp/getStreamDeviceConfig.php', 'sync'=>'syncscript/syncPushTempsCam.php', 'sync1'=>'syncscript/syncPushTempsCam.1.php', 'sync2'=>'syncscript/syncPushTempsCam.2.php', 'appareils'=>'scriptsphp/getAppareilsSurSurface.php'];
$fichiers['stream_differe'] = $fichiers['stream'];
foreach (['sync', 'sync1', 'sync2'] as $variante) { $fichiers[$variante . '_donnees'] = $fichiers[$variante]; }
$fichiers['sync2_demandes'] = $fichiers['sync2'];
$fichiers['sync2_demandes_differees'] = $fichiers['sync2'];
$fichiers['appareils_timeout'] = $fichiers['appareils'];
$_POST = ['timeout'=>40000, 'deviceId'=>1, 'userId'=>'test', 'lastUpdate'=>'2026-11-25', 'username'=>'test', 'recentSync'=>0, 'arenaId'=>1, 'heure'=>time()*1000, 'matchId'=>'null'];
if ($cas === 'stream') { $_POST['timeout'] = 10000; }
if (strpos($cas, 'sync2_demandes') === 0) { $_POST['nomMatch'] = '42'; }
$conn = null;
$source = file_get_contents(__DIR__ . '/../../' . $fichiers[$cas]);
$source = preg_replace("~require (?:__DIR__ \. '/\.\./scriptsphp/defenvvar\.php'|'\.\./scriptsphp/defenvvar\.php');~", 'ouvrirConnexionFictive();', $source);
$source = str_replace('<?php', '<?php namespace OccupationConnexions;', $source);
$source = str_replace('sleep(5)', 'attenteFictive(5)', $source);
ob_start();
eval('?>' . $source);
$sortie = trim(ob_get_clean());
$reponse = json_decode($sortie, true);
verifier(is_array($reponse), 'Réponse JSON invalide : ' . $sortie);
verifier($conn === null, 'Connexion restante après traitement');
if ($cas === 'appareils' || $cas === 'appareils_timeout') {
    verifier($ouvertures === 1 && $appelsHttp === 2, 'Nombre de connexions/appels HTTP inattendu');
    verifier($reponse['cams'][0]['vidDetect'] === date('c', 1700000000), 'Vidéo détectée modifiée');
    verifier($reponse['remotes'][0]['telId'] === 'remote-test' && $reponse['remotes'][0]['memoire'] === 12, 'Statut Remote perdu');
    if ($cas === 'appareils_timeout') { verifier(!isset($reponse['cams'][0]['vidLoade']), 'Vidéo déclarée disponible malgré timeout'); }
    else { verifier(strpos($reponse['cams'][0]['vidLoade'], '/present.mp4') !== false, 'Sélection vidéo modifiée'); }
} elseif ($cas === 'stream_modifie' || $cas === 'stream_differe') {
    $attentesAttendues = $cas === 'stream_differe' ? 1 : 0;
    verifier($attentes === $attentesAttendues && $ouvertures === $attentesAttendues + 1, 'Attente/réouverture incorrecte après configuration');
    verifier($reponse === ['lastUpdate'=>'2026-11-26', 'settings'=>'{"resolution":"1080p","active":true}', 'status'=>'actif'], 'Configuration Stream altérée');
} elseif (strpos($cas, '_donnees') !== false || strpos($cas, 'sync2_demandes') === 0) {
    $attentesAttendues = $cas === 'sync2_demandes_differees' ? 1 : 0;
    verifier($attentes === $attentesAttendues && $ouvertures === $attentesAttendues + 1, 'Connexions incorrectes avec données');
    if ($cas === 'sync_donnees') {
        verifier(array_column($reponse['video'], 'reference') === [101, 102], 'Événement/clip Legacy perdus');
        verifier($reponse['matchs'][0]['abons'] === [['role'=>1, 'telId'=>'remote-test']], 'Abonnements Legacy perdus');
        verifier($reponse['matchPeriode'][0]['periodes'][0]['noPer'] === 2, 'Période Legacy perdue');
    } else {
        verifier(array_column($reponse['matchPeriode'][0]['videos'], 'reference') === [101, 102], 'Événement/clip perdus');
        verifier($reponse['matchPeriode'][0]['periodes'][0]['noPer'] === 2, 'Période perdue');
        verifier($reponse['matchPeriode'][0]['arena'] === 'Aréna test / Glace 1', 'Aréna perdu');
        verifier($reponse['matchPeriode'][0]['abons'] === [['role'=>1, 'telId'=>'remote-test']], 'Abonnements perdus');
    }
    if (strpos($cas, 'sync2') === 0) {
        verifier($reponse['ligues'][0]['sportId'] === 5, 'Configuration du sport perdue');
        verifier($reponse['matchPeriode'][0]['videos'][0]['ActivationArgs'] === '{"camera":9}', 'Configuration caméra perdue');
        verifier($reponse['matchPeriode'][0]['videos'][0]['defaultDuration'] === 12, 'Durée caméra perdue');
    }
    if (strpos($cas, 'sync2_demandes') === 0) {
        verifier(array_keys($demandesTraitees) === [7, 8], 'Demande hors aréna traitée ou demande perdue');
        verifier($demandesTraitees[8] - $demandesTraitees[7] === 1000, 'Décalage des demandes modifié');
        verifier(count(array_filter($requetes, function ($sql) { return strpos($sql, 'FROM DemandeAjoutVideo WHERE progression=1') !== false; })) === 1, 'Sélection initiale des demandes répétée');
        verifier(count($reponse['matchPeriode']) === 3, 'Demandes absentes de la réponse');
        verifier($reponse['matchPeriode'][1]['videos'][0]['reference'] === 101 && $reponse['matchPeriode'][2]['videos'][0]['type'] === 5, 'Demandes événement/clip altérées');
        verifier($reponse['matchPeriode'][1]['videos'][0]['chrono'] === $demandesTraitees[7], 'Chrono de demande perdu');
    }
} else {
    verifier($attentes === 2 && $ouvertures === 2, 'Reconnexion entre deux lectures incorrecte');
    if ($cas === 'stream') { verifier($reponse === ['lastUpdate'=>'2026-11-25'], 'Contrat Stream modifié'); }
    else { verifier($reponse['matchPeriode'] === [] && $reponse['matchs'] === [], 'Contrat sync vide modifié'); }
}
echo 'OK ' . $cas . PHP_EOL;
