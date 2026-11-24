<?php

declare(strict_types=1);

/**
 * Tests unitaires de la logique pure de getEvenementsAvecVideos.
 *
 * N'exige aucune base : couvre la construction d'URL, la validation des
 * paramètres (périmètre obligatoire, bornes, codes) et la mise en forme de la
 * réponse (distinction match interne/externe, durée gatée, vidéo inexploitable).
 *
 * Exécution : php test/unit/evenements_avec_videos_test.php
 * Sans PHP CLI, ce test est simplement non exécuté (cf. autres tests du dépôt).
 */

require_once __DIR__ . '/../../api/lib/evenements_avec_videos.php';

$echecs = 0;
$verifie = static function (string $cas, $attendu, $obtenu) use (&$echecs): void {
    if ($attendu === $obtenu) {
        echo "OK   $cas\n";
        return;
    }
    $echecs++;
    echo "FAIL $cas\n  attendu: " . var_export($attendu, true) . "\n  obtenu : " . var_export($obtenu, true) . "\n";
};

// --- Construction d'URL : réécriture 5.39.81.14 -> syncstats.ca ---
$verifie(
    'url_rewrite_ip',
    'https://syncstats.ca/lookatthis/1794799921681_74.mp4',
    evAvVideosConstruireUrl('5.39.81.14', '1794799921681_74.mp4')
);
// Emplacement domaine conservé tel quel
$verifie(
    'url_domaine',
    'https://syncstats.ca/lookatthis/abc_12.mp4',
    evAvVideosConstruireUrl('syncstats.ca', 'abc_12.mp4')
);
// Protocole/slash résiduels neutralisés (robustesse données historiques)
$verifie(
    'url_nettoyage',
    'https://exemple.test/lookatthis/f_1.mp4',
    evAvVideosConstruireUrl('https://exemple.test/', 'f_1.mp4')
);
// Fichier ou hôte vide -> null (vidéo inexploitable)
$verifie('url_sans_fichier', null, evAvVideosConstruireUrl('syncstats.ca', ''));
$verifie('url_sans_hote', null, evAvVideosConstruireUrl('', 'f.mp4'));

// --- Validation des paramètres ---
// Périmètre obligatoire absent
[$ok, $code] = evAvVideosValiderParametres([]);
$verifie('perimetre_absent_ok', false, $ok);
$verifie('perimetre_absent_code', 'perimetre_insuffisant', $code);

// ligueId seul suffit ; valeurs par défaut
[$ok, $code, $p] = evAvVideosValiderParametres(['ligueId' => '107']);
$verifie('ligue_seule_ok', true, $ok);
$verifie('minCameras_defaut', EV_AV_VIDEOS_CAMERAS_DEFAUT, $p['minCameras']);
$verifie('limit_defaut', EV_AV_VIDEOS_LIMITE_DEFAUT, $p['limit']);

// limit borné au maximum
[$ok, $code, $p] = evAvVideosValiderParametres(['matchId' => '7356', 'limit' => '99999']);
$verifie('limit_borne', EV_AV_VIDEOS_LIMITE_MAX, $p['limit']);

// codes : dédupliqués et typés
[$ok, $code, $p] = evAvVideosValiderParametres(['ligueId' => '1', 'codes' => '0,1,0, 3 ']);
$verifie('codes_dedupe', [0, 1, 3], $p['codes']);

// matchId invalide
[$ok, $code, $p] = evAvVideosValiderParametres(['matchId' => 'abc']);
$verifie('matchId_invalide', 'parametre_invalide', $code);

// fenêtre chrono incohérente
[$ok, $code] = evAvVideosValiderParametres(['ligueId' => '1', 'minChronoMs' => '10', 'maxChronoMs' => '5']);
$verifie('chrono_incoherent', 'parametre_invalide', $code);

// --- Mise en forme de la réponse ---
$evenements = [
    ['event_id' => 241586, 'matchIdRef' => 'REF-7356', 'match_id' => 7356, 'chrono' => 1557685437257, 'code' => 0, 'souscode' => 0],
];
$videos = [
    ['reference' => 241586, 'videoId' => 87622, 'camId' => 44, 'nomFichier' => '1557685437828_44.mp4', 'emplacement' => '5.39.81.14', 'duree' => 8000],
    ['reference' => 241586, 'videoId' => 87864, 'camId' => 45, 'nomFichier' => '1557685438828_45.mp4', 'emplacement' => 'syncstats.ca', 'duree' => 0],
    // vidéo sans fichier -> exclue
    ['reference' => 241586, 'videoId' => 9, 'camId' => 46, 'nomFichier' => '', 'emplacement' => 'syncstats.ca', 'duree' => null],
];
$sortie = evAvVideosFormerReponse($evenements, $videos);

$verifie('reponse_nb', 1, count($sortie));
// Convention corrigée : match_id = interne Legacy, matchIdRef = externe de référence.
$verifie('reponse_match_interne', 7356, $sortie[0]['match']['idInterneLegacy']);
$verifie('reponse_match_externe', 'REF-7356', $sortie[0]['match']['idExterneReference']);
$verifie('reponse_chrono', 1557685437257, $sortie[0]['chronoMs']);
$verifie('reponse_nb_videos', 2, count($sortie[0]['videos'])); // la 3e est exclue
$verifie('reponse_cam1', 44, $sortie[0]['videos'][0]['camId']);
$verifie(
    'reponse_duree_declaree',
    ['valeur' => 8000, 'unite' => 'ms', 'source' => 'appareil_capture', 'verifiee' => false],
    $sortie[0]['videos'][0]['dureeDeclaree']
);
$verifie('reponse_duree_reelle_null', null, $sortie[0]['videos'][0]['dureeReelleFichier']);
$verifie('reponse_duree_zero_null', null, $sortie[0]['videos'][1]['dureeDeclaree']);
$verifie('reponse_url', 'https://syncstats.ca/lookatthis/1557685437828_44.mp4', $sortie[0]['videos'][0]['url']);

// Non-troncature : un événement retenu renvoie TOUTES ses vidéos découpées,
// y compris plusieurs vidéos pour une même caméra (ici 3 vidéos, 2 caméras).
$evTronc = [['event_id' => 500, 'matchIdRef' => 'R', 'match_id' => 5, 'chrono' => 1, 'code' => 0, 'souscode' => 0]];
$vidsTronc = [
    ['reference' => 500, 'videoId' => 1, 'camId' => 73, 'nomFichier' => 'a_73.mp4', 'emplacement' => 'syncstats.ca', 'duree' => null],
    ['reference' => 500, 'videoId' => 2, 'camId' => 74, 'nomFichier' => 'b_74.mp4', 'emplacement' => 'syncstats.ca', 'duree' => null],
    ['reference' => 500, 'videoId' => 3, 'camId' => 74, 'nomFichier' => 'c_74.mp4', 'emplacement' => 'syncstats.ca', 'duree' => null],
];
$sortieTronc = evAvVideosFormerReponse($evTronc, $vidsTronc);
$verifie('non_troncature_videos', 3, count($sortieTronc[0]['videos']));

echo "\n" . ($echecs === 0 ? "TOUS LES TESTS PASSENT\n" : "$echecs ÉCHEC(S)\n");
exit($echecs === 0 ? 0 : 1);
