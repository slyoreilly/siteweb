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
class mysqli { public $ouverte = true; public $charset; public $connect_errno = 1203; }
class ResultatFictif {
    public $lignes;
    public function __construct($lignes = []) { $this->lignes = $lignes; }
}
$tempsFictif = 0;
$pausesLongPoll = array();
$tentativesLongPoll = array();
$connexionsCreees = array();
$ouvertures = 0;
$attentes = 0;
$appelsHttp = 0;
$requetes = [];
$demandesTraitees = [];
$sondes = 0;
function mysqli_connect($hote, $utilisateur, $motDePasse, $base, $port = null) {
    global $conn, $ouvertures;
    if ($GLOBALS['cas'] === 'sync_init_exception') { throw new \RuntimeException('échec initial simulé'); }
    if ($GLOBALS['cas'] === 'sync_init_exit') {
        register_shutdown_function(function () { verifier(http_response_code() === 503 && $GLOBALS['conn'] === null, 'Repli initial incorrect'); });
        http_response_code(500);
        exit;
    }
    $GLOBALS['configurationInitiale'] = array($hote, $utilisateur, $motDePasse, $base, $port);
    verifier($conn === null, 'Ouverture multiple sans fermeture');
    $ouvertures++;
    $connexion = new mysqli();
    $GLOBALS['connexionsCreees'][] = $connexion;
    return $connexion;
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
function hrtime($numerique = false) { return $GLOBALS['tempsFictif'] * 1000000000; }
function usleep($microsecondes) {
    verifier($GLOBALS['conn'] === null, 'Connexion conservée pendant une pause ou une reprise');
    verifier($microsecondes > 0, 'Attente agressive');
    $GLOBALS['tempsFictif'] += $microsecondes / 1000000;
    $GLOBALS['pausesLongPoll'][] = $microsecondes / 1000000;
    $GLOBALS['attentes']++;
}
function mysqli_character_set_name($connexion) { return $connexion->charset; }
function mysqli_init() {
    $connexion = new mysqli();
    $GLOBALS['connexionsCreees'][] = $connexion;
    return $connexion;
}
function mysqli_options($connexion, $option, $valeur) {
    verifier($valeur > 0 && ($option !== MYSQLI_OPT_CONNECT_TIMEOUT || $valeur === 2), 'Timeout de connexion incorrect');
    return true;
}
function mysqli_real_connect($connexion, $hote, $utilisateur, $motDePasse, $base, $port) {
    verifier(array($hote, $utilisateur, $motDePasse, $base, $port) === $GLOBALS['configurationInitiale'], 'Configuration historique non conservée');
    $GLOBALS['ouvertures']++;
    $GLOBALS['tentativesLongPoll'][] = $GLOBALS['tempsFictif'];
    $numero = count($GLOBALS['tentativesLongPoll']);
    $cas = $GLOBALS['cas'];
    if (strpos($cas, 'epuisement') !== false || strpos($cas, 'saturation') !== false || (strpos($cas, 'reprise') !== false && $numero === 1)) {
        if (strpos($cas, 'exception') !== false) { throw new \RuntimeException('saturation simulée', 1203); }
        return false;
    }
    return true;
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
    if ((strpos($cas, '_tardif') !== false || strpos($cas, '_reprise') !== false) && $GLOBALS['tempsFictif'] < 15
        && (strpos($sqlNormalise, 'SELECT * FROM (') === 0 || strpos($sqlNormalise, 'SELECT e.event_id') === 0)) {
        return new ResultatFictif();
    }
    if ($avecDemandes) {
        if (strpos($sqlNormalise, 'SELECT demandeId, eventId, typeEvenement, chronoDemande') === 0) {
            if ($cas === 'sync2_demandes_apres_pause' && $GLOBALS['tempsFictif'] < 15) { return new ResultatFictif(); }
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
            return new ResultatFictif([['arenaId'=>1, 'eq_dom'=>'11', 'eq_vis'=>'12', 'ligueRef'=>4]]);
        }
        if (strpos($sqlNormalise, 'SELECT demandeId, eventId, typeEvenement, chronoVideo') === 0) {
            return new ResultatFictif([
                ['demandeId'=>7, 'eventId'=>101, 'typeEvenement'=>'0', 'chronoVideo'=>$GLOBALS['demandesTraitees'][7], 'cameraId'=>9],
                ['demandeId'=>8, 'eventId'=>102, 'typeEvenement'=>'5', 'chronoVideo'=>$GLOBALS['demandesTraitees'][8], 'cameraId'=>9]]);
        }
    }
    if (strpos($cas, 'sync2_') === 0 && (strpos($cas, 'epuisement') !== false || strpos($cas, 'saturation') !== false)
        && strpos($sqlNormalise, 'SELECT * FROM Ligue') === 0) {
        return new ResultatFictif([['ID_Ligue'=>4, 'Nom_Ligue'=>'Ligue test', 'cleValeur'=>'{}']]);
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
            if (in_array($cas, ['sync2_demandes_differees', 'sync2_demandes_sans_match', 'sync2_demandes_apres_pause'], true) || strpos($cas, 'sync2_demandes_seules') === 0) { return new ResultatFictif(); }
            $base = ['matchIdRef'=>'match-test', 'eq_dom'=>'11', 'eq_vis'=>'12', 'ligueRef'=>4, 'match_id'=>42, 'arenaId'=>1, 'date'=>'2026-11-25 12:00:00', 'LeagueId'=>4, 'ActivationFlags'=>3, 'ActivationArgs'=>'{"camera":9}', 'defaultDuration'=>12];
            if (strpos($cas, 'sync_donnees') === 0) { return new ResultatFictif([$base + [0=>1700000000000, 1=>'match-test']]); }
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
foreach (['CURLOPT_NOBODY', 'CURLOPT_RETURNTRANSFER', 'CURLOPT_CONNECTTIMEOUT', 'CURLOPT_TIMEOUT', 'CURLINFO_HTTP_CODE', 'MYSQLI_OPT_CONNECT_TIMEOUT', 'MYSQLI_OPT_READ_TIMEOUT'] as $i => $nom) { if (!defined($nom)) { define($nom, $i + 1); } }
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
foreach (['sync', 'sync1', 'sync2'] as $variante) {
    foreach (['donnees_tardif', 'donnees_reprise', 'donnees_reprise_exception', 'epuisement', 'epuisement_exception', 'saturation_courte', 'expiration', 'expiration_courte', 'expiration_plafonnee'] as $scenario) {
        $fichiers[$variante . '_' . $scenario] = $fichiers[$variante];
    }
}
$fichiers['sync_init_exit'] = $fichiers['sync'];
$fichiers['sync_init_exception'] = $fichiers['sync'];
$fichiers['sync2_demandes_sans_match'] = $fichiers['sync2'];
$fichiers['sync2_demandes_seules'] = $fichiers['sync2'];
$fichiers['sync2_demandes_seules_saturation'] = $fichiers['sync2'];
$fichiers['sync2_demandes_apres_pause'] = $fichiers['sync2'];
$_POST = ['timeout'=>40000, 'deviceId'=>1, 'userId'=>'test', 'lastUpdate'=>'2026-11-25', 'username'=>'test', 'recentSync'=>0, 'arenaId'=>1, 'heure'=>time()*1000, 'matchId'=>'null'];
if ($cas === 'stream') { $_POST['timeout'] = 10000; }
if (strpos($cas, 'sync2_demandes') === 0 && $cas !== 'sync2_demandes_sans_match') { $_POST['nomMatch'] = '42'; }
if (strpos($cas, '_tardif') !== false || strpos($cas, '_reprise') !== false || strpos($cas, '_epuisement') !== false || strpos($cas, '_expiration') !== false) { $_POST['timeout'] = 60000; }
if (strpos($cas, '_saturation_courte') !== false) { $_POST['timeout'] = 40000; }
if (strpos($cas, '_expiration_courte') !== false) { $_POST['timeout'] = 30000; }
if (strpos($cas, '_expiration_plafonnee') !== false) { $_POST['timeout'] = 120000; }
if ($cas === 'sync2_demandes_apres_pause') { $_POST['timeout'] = 60000; }
$conn = null;
$helper = file_get_contents(__DIR__ . '/../../syncscript/attenteLongPollCam.php');
$helper = str_replace('<?php', '<?php namespace OccupationConnexions; use Throwable; use RuntimeException;', $helper);
$environnementSource = file_get_contents(__DIR__ . '/../../scriptsphp/defenvvar.php');
$environnementSource = preg_replace("~require_once __DIR__ \. '/chargeSecrets\.php';~", '', $environnementSource, 1, $nombre);
verifier($nombre === 1, 'Chargement privé inattendu');
$environnementSource = str_replace('<?php', '<?php namespace OccupationConnexions;', $environnementSource);
$helper = str_replace("@require __DIR__ . '/../scriptsphp/defenvvar.php';", "eval('?>' . base64_decode('" . base64_encode($environnementSource) . "'));", $helper);
eval('?>' . $helper);
$source = file_get_contents(__DIR__ . '/../../' . $fichiers[$cas]);
$source = str_replace("require_once __DIR__ . '/attenteLongPollCam.php';", '', $source);
$source = preg_replace("~require (?:__DIR__ \. '/\.\./scriptsphp/defenvvar\.php'|'\.\./scriptsphp/defenvvar\.php');~", 'ouvrirConnexionFictive();', $source);
$source = str_replace('<?php', '<?php namespace OccupationConnexions;', $source);
$source = str_replace('sleep(5)', 'attenteFictive(5)', $source);
ob_start();
eval('?>' . $source);
$sortie = trim(ob_get_clean());
$reponse = json_decode($sortie, true);
verifier(is_array($reponse), 'Réponse JSON invalide : ' . $sortie);
verifier($conn === null, 'Connexion restante après traitement');
foreach ($connexionsCreees as $connexionCreee) {
    verifier(!$connexionCreee->ouverte, 'Connexion échouée ou utile non libérée');
}
if (strpos($cas, 'sync') === 0) {
    foreach (array('match', 'chronoBut', 'video', 'matchPeriode', 'matchs') as $cle) {
        verifier(isset($reponse[$cle]) && is_array($reponse[$cle]), 'Tableau consommateur absent : ' . $cle);
    }
    verifier(isset($reponse['heure']) && is_int($reponse['heure']), 'Heure serveur incompatible');
    if (strpos($cas, 'sync1') === 0 || strpos($cas, 'sync2') === 0) {
        foreach ($reponse['matchPeriode'] as $matchRecu) {
            foreach (array('match_id', 'eqDom', 'eqVis', 'ligueId', 'arenaId') as $cle) {
                verifier(isset($matchRecu[$cle]) && is_numeric($matchRecu[$cle]), 'Identifiant attendu par JSONObject.getInt absent : ' . $cle);
            }
            verifier(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $matchRecu['date']) === 1, 'Date de match incompatible');
            foreach (array('videos', 'periodes', 'abons') as $cle) {
                verifier(is_array($matchRecu[$cle]), 'Tableau de match incompatible : ' . $cle);
            }
            foreach ($matchRecu['videos'] as $videoRecue) {
                foreach (array('reference', 'type', 'chrono', 'ligueId', 'equipe') as $cle) {
                    verifier(isset($videoRecue[$cle]) && is_numeric($videoRecue[$cle]), 'Métadonnée vidéo incompatible : ' . $cle);
                }
            }
        }
    }

    if (strpos($cas, 'sync2') === 0) {
        verifier(isset($reponse['ligues']) && is_array($reponse['ligues']), 'Ligues incompatibles avec getTemps');
    }
}
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
} elseif (strpos($cas, '_epuisement') !== false || strpos($cas, '_saturation_courte') !== false || $cas === 'sync_init_exception') {
    verifier(http_response_code() === 503 && $reponse['matchPeriode'] === [], 'Échec non contrôlé ou réponse partielle');
    verifier(count($tentativesLongPoll) <= 3 && $tempsFictif <= 30, 'Reprises non bornées');
    if (strpos($cas, '_epuisement') !== false) {
        verifier($tentativesLongPoll === [5, 7, 12], 'Temporisation de saturation incorrecte');
        verifier($pausesLongPoll === [5, 2, 5], 'Attente inutile ou agressive pendant saturation');
    }
    if (strpos($cas, 'sync2_') === 0) { verifier(count($reponse['ligues']) === 1 && $reponse['ligues'][0]['ligueId'] === 4, 'Ligues déjà lues perdues sur reconnexion'); }
} elseif (strpos($cas, '_expiration') !== false || $cas === 'sync2_demandes_sans_match' || $cas === 'sync2_demandes_apres_pause') {
    verifier(http_response_code() === 200 && $reponse['matchPeriode'] === [], 'Expiration incorrecte');
    verifier($tempsFictif === ($cas === 'sync2_demandes_sans_match' ? 10 : (strpos($cas, '_expiration_courte') !== false ? 5 : 30)), 'Budget réel incorrect');
    verifier($demandesTraitees === [], 'Demande non transmissible marquée traitée');
    if ($cas === 'sync2_demandes_apres_pause') {
        verifier(count(array_filter($requetes, function ($sql) { return strpos($sql, 'FROM DemandeAjoutVideo WHERE progression=1') !== false; })) === 1, 'Recherche de demandes ajoutée dans la boucle sans arbitrage');
    }
} elseif (strpos($cas, '_donnees') !== false || strpos($cas, 'sync2_demandes') === 0) {
    $attentesAttendues = strpos($cas, '_tardif') !== false ? 3 : (strpos($cas, '_reprise') !== false ? 4 : 0);
    verifier($attentes === $attentesAttendues && $ouvertures === $attentesAttendues + 1, 'Connexions incorrectes avec données');
    if (strpos($cas, 'sync_donnees') === 0) {
        verifier(array_column($reponse['video'], 'reference') === [101, 102], 'Événement/clip Legacy perdus');
        verifier($reponse['matchs'][0]['abons'] === [['role'=>1, 'telId'=>'remote-test']], 'Abonnements Legacy perdus');
        verifier($reponse['matchPeriode'][0]['periodes'][0]['noPer'] === 2, 'Période Legacy perdue');
    } elseif (!($cas === 'sync2_demandes_differees' || strpos($cas, 'sync2_demandes_seules') === 0)) {
        verifier(array_column($reponse['matchPeriode'][0]['videos'], 'reference') === [101, 102], 'Événement/clip perdus');
        verifier($reponse['matchPeriode'][0]['periodes'][0]['noPer'] === 2, 'Période perdue');
        verifier($reponse['matchPeriode'][0]['arena'] === 'Aréna test / Glace 1', 'Aréna perdu');
        verifier($reponse['matchPeriode'][0]['abons'] === [['role'=>1, 'telId'=>'remote-test']], 'Abonnements perdus');
    }
    if (strpos($cas, 'sync2') === 0 && !($cas === 'sync2_demandes_differees' || strpos($cas, 'sync2_demandes_seules') === 0)) {
        verifier($reponse['ligues'][0]['sportId'] === 5, 'Configuration du sport perdue');
        verifier($reponse['matchPeriode'][0]['videos'][0]['ActivationArgs'] === '{"camera":9}', 'Configuration caméra perdue');
        verifier($reponse['matchPeriode'][0]['videos'][0]['defaultDuration'] === 12, 'Durée caméra perdue');
    }
    if (strpos($cas, '_reprise') !== false) {
        verifier($tempsFictif === 17 && http_response_code() === 200, 'Reprise sans conservation du long poll');
        verifier(array_slice($pausesLongPoll, 0, 2) === [5, 2], 'Reprise trop agressive');
    }
    if (strpos($cas, '_tardif') !== false) { verifier($tempsFictif === 15, 'Événement retardé après détection'); }
    if (strpos($cas, 'sync2_demandes') === 0) {
        verifier(array_keys($demandesTraitees) === [7, 8], 'Demande hors aréna traitée ou demande perdue');
        verifier($demandesTraitees[8] - $demandesTraitees[7] === 1000, 'Décalage des demandes modifié');
        verifier(count(array_filter($requetes, function ($sql) { return strpos($sql, 'FROM DemandeAjoutVideo WHERE progression=1') !== false; })) === 1, 'Sélection initiale des demandes répétée');
        verifier(count($reponse['matchPeriode']) === (($cas === 'sync2_demandes_differees' || strpos($cas, 'sync2_demandes_seules') === 0) ? 2 : 3), 'Demandes absentes de la réponse');
        $indexDemande = ($cas === 'sync2_demandes_differees' || strpos($cas, 'sync2_demandes_seules') === 0) ? 0 : 1;
        verifier($reponse['matchPeriode'][$indexDemande]['videos'][0]['reference'] === 101 && $reponse['matchPeriode'][$indexDemande + 1]['videos'][0]['type'] === 5, 'Demandes événement/clip altérées');
        verifier($reponse['matchPeriode'][$indexDemande]['videos'][0]['chrono'] === $demandesTraitees[7], 'Chrono de demande perdu');
    }
} else {
    verifier($attentes === 2 && $ouvertures === 2, 'Reconnexion entre deux lectures incorrecte');
    if ($cas === 'stream') { verifier($reponse === ['lastUpdate'=>'2026-11-25'], 'Contrat Stream modifié'); }
    else { verifier($reponse['matchPeriode'] === [] && $reponse['matchs'] === [], 'Contrat sync vide modifié'); }
}
echo 'OK ' . $cas . PHP_EOL;
