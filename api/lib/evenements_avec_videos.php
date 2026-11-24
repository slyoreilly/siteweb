<?php

declare(strict_types=1);

/**
 * Logique pure et testable de l'endpoint de lecture getEvenementsAvecVideos.
 *
 * Aucun accès base ici : construction d'URL, validation des paramètres et
 * mise en forme de la réponse uniquement, afin de pouvoir valider la logique
 * sans MySQL (voir test/unit/evenements_avec_videos_test.php).
 *
 * Portée fonctionnelle : vidéos DÉCOUPÉES attachées aux événements
 * (Video.type = 0, Video.reference = TableEvenement0.event_id).
 * Hors périmètre de cette première version : RAW (type = 10000) et clips
 * manuels (type = 5).
 *
 * Convention des identifiants de match (vérifiée dans api/upsertMatch.php) :
 *   - TableMatch.match_id   : clé primaire numérique Legacy = identifiant INTERNE
 *                             (AUTO_INCREMENT, retourné comme GameComId par upsertMatch).
 *   - TableMatch.matchIdRef : référence externe (matchLongId) fournie par le client ;
 *                             c'est la clé de liaison des événements
 *                             (TableEvenement0.match_event_id = matchIdRef).
 */

const EV_AV_VIDEOS_TYPE_DECOUPEE = 0;
const EV_AV_VIDEOS_LIMITE_DEFAUT = 50;
const EV_AV_VIDEOS_LIMITE_MAX = 200;
const EV_AV_VIDEOS_CAMERAS_DEFAUT = 1;
const EV_AV_VIDEOS_MAX_CODES = 20;

/**
 * Reproduit le mécanisme de construction d'URL fonctionnelle utilisé par la
 * PlateformeWeb et les pages ligue (ex. ligues/lasm/videos.html) :
 *   - l'hôte historique 5.39.81.14 est réécrit vers le domaine syncstats.ca
 *     (HTTPS valide) ;
 *   - le fichier est servi sous /lookatthis/ ;
 *   - nomFichier contient déjà l'extension (.mp4).
 *
 * On neutralise un éventuel protocole ou slash résiduel dans emplacement pour
 * rester robuste aux données historiques (ex. ancien emplacement syncstats.com).
 */
function evAvVideosConstruireUrl(?string $emplacement, ?string $nomFichier): ?string
{
    $nomFichier = trim((string)$nomFichier);
    if ($nomFichier === '') {
        return null;
    }

    $hote = trim((string)$emplacement);
    if ($hote === '5.39.81.14') {
        $hote = 'syncstats.ca';
    }
    // Retire un protocole éventuel puis les slashs de bord.
    $hote = preg_replace('#^https?://#i', '', $hote);
    $hote = trim((string)$hote, '/');
    if ($hote === '') {
        return null;
    }

    return 'https://' . $hote . '/lookatthis/' . ltrim($nomFichier, '/');
}

/**
 * Normalise et valide les paramètres de requête.
 *
 * @param array $get Tableau type $_GET.
 * @return array{0:bool,1:?string,2:array} [ok, codeErreur, parametres]
 */
function evAvVideosValiderParametres(array $get): array
{
    $params = [
        'matchId'     => null, // identifiant EXTERNE du match (TableMatch.match_id / GameComId)
        'ligueId'     => null,
        'minChronoMs' => null,
        'maxChronoMs' => null,
        'codes'       => null, // tableau d'entiers ou null
        'minCameras'  => EV_AV_VIDEOS_CAMERAS_DEFAUT,
        'limit'       => EV_AV_VIDEOS_LIMITE_DEFAUT,
    ];

    $entierPositif = static function ($valeur): ?int {
        $n = filter_var($valeur, FILTER_VALIDATE_INT);
        if ($n === false || $n < 1) {
            return null;
        }
        return $n;
    };

    if (isset($get['matchId']) && $get['matchId'] !== '') {
        $v = $entierPositif($get['matchId']);
        if ($v === null) {
            return [false, 'parametre_invalide', ['parametre' => 'matchId']];
        }
        $params['matchId'] = $v;
    }

    if (isset($get['ligueId']) && $get['ligueId'] !== '') {
        $v = $entierPositif($get['ligueId']);
        if ($v === null) {
            return [false, 'parametre_invalide', ['parametre' => 'ligueId']];
        }
        $params['ligueId'] = $v;
    }

    foreach (['minChronoMs', 'maxChronoMs'] as $champ) {
        if (isset($get[$champ]) && $get[$champ] !== '') {
            $n = filter_var($get[$champ], FILTER_VALIDATE_INT);
            if ($n === false || $n < 0) {
                return [false, 'parametre_invalide', ['parametre' => $champ]];
            }
            $params[$champ] = $n;
        }
    }

    if ($params['minChronoMs'] !== null && $params['maxChronoMs'] !== null
        && $params['minChronoMs'] > $params['maxChronoMs']) {
        return [false, 'parametre_invalide', ['parametre' => 'chrono']];
    }

    if (isset($get['codes']) && $get['codes'] !== '') {
        $brut = explode(',', (string)$get['codes']);
        if (count($brut) > EV_AV_VIDEOS_MAX_CODES) {
            return [false, 'parametre_invalide', ['parametre' => 'codes']];
        }
        $codes = [];
        foreach ($brut as $morceau) {
            $morceau = trim($morceau);
            if ($morceau === '') {
                continue;
            }
            $n = filter_var($morceau, FILTER_VALIDATE_INT);
            if ($n === false || $n < 0) {
                return [false, 'parametre_invalide', ['parametre' => 'codes']];
            }
            $codes[$n] = $n; // déduplication
        }
        if (!empty($codes)) {
            $params['codes'] = array_values($codes);
        }
    }

    if (isset($get['minCameras']) && $get['minCameras'] !== '') {
        $v = $entierPositif($get['minCameras']);
        if ($v === null) {
            return [false, 'parametre_invalide', ['parametre' => 'minCameras']];
        }
        $params['minCameras'] = $v;
    }

    if (isset($get['limit']) && $get['limit'] !== '') {
        $v = $entierPositif($get['limit']);
        if ($v === null) {
            return [false, 'parametre_invalide', ['parametre' => 'limit']];
        }
        $params['limit'] = min($v, EV_AV_VIDEOS_LIMITE_MAX);
    }

    // Périmètre de sélection obligatoire et suffisamment restrictif :
    // au moins un identifiant de match ou de ligue.
    if ($params['matchId'] === null && $params['ligueId'] === null) {
        return [false, 'perimetre_insuffisant', []];
    }

    return [true, null, $params];
}

/**
 * Construit la requête paramétrée de SÉLECTION des événements multivues.
 * `limit` et `minCameras` ne portent QUE sur la sélection d'événements.
 *
 * @param array $params Paramètres déjà validés par evAvVideosValiderParametres.
 * @return array{sql:string,types:string,args:array}
 */
function evAvVideosRequeteEvenements(array $params): array
{
    // Le type de vidéo est filtré dans la jointure (1er placeholder du SQL).
    $types  = 'i';
    $args   = [EV_AV_VIDEOS_TYPE_DECOUPEE];

    $where = [];
    if ($params['matchId'] !== null) {
        $where[] = 'm.match_id = ?';
        $types  .= 'i';
        $args[]  = $params['matchId'];
    }
    if ($params['ligueId'] !== null) {
        $where[] = 'm.ligueRef = ?';
        $types  .= 'i';
        $args[]  = $params['ligueId'];
    }
    if ($params['minChronoMs'] !== null) {
        $where[] = 'e.chrono >= ?';
        $types  .= 'i';
        $args[]  = $params['minChronoMs'];
    }
    if ($params['maxChronoMs'] !== null) {
        $where[] = 'e.chrono <= ?';
        $types  .= 'i';
        $args[]  = $params['maxChronoMs'];
    }
    if ($params['codes'] !== null && count($params['codes']) > 0) {
        $where[] = 'e.code IN (' . implode(',', array_fill(0, count($params['codes']), '?')) . ')';
        foreach ($params['codes'] as $c) {
            $types .= 'i';
            $args[] = $c;
        }
    }
    // Le périmètre obligatoire (matchId ou ligueId) garantit $where non vide ;
    // 1=1 reste une sécurité défensive.
    $clauseWhere = $where ? implode(' AND ', $where) : '1=1';

    $sql = '
        SELECT e.event_id, m.matchIdRef, m.match_id, e.chrono, e.code, e.souscode
          FROM TableEvenement0 e
          INNER JOIN TableMatch m ON e.match_event_id = m.matchIdRef
          INNER JOIN Video v ON v.reference = e.event_id AND v.type = ?
         WHERE ' . $clauseWhere . '
         GROUP BY e.event_id, m.matchIdRef, m.match_id, e.chrono, e.code, e.souscode
        HAVING COUNT(DISTINCT v.camId) >= ?
         ORDER BY e.chrono DESC
         LIMIT ?';
    $types .= 'ii';
    $args[] = $params['minCameras'];
    $args[] = $params['limit'];

    return ['sql' => $sql, 'types' => $types, 'args' => $args];
}

/**
 * Construit la requête paramétrée des vidéos découpées des événements retenus.
 * AUCUNE limite : toutes les vidéos type=0 des événements sont renvoyées
 * (pas de troncature des listes).
 *
 * @param int[] $eventIds
 * @return array{sql:string,types:string,args:array}
 */
function evAvVideosRequeteVideos(array $eventIds, bool $colonneDuree): array
{
    $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
    $champs = 'reference, videoId, camId, nomFichier, emplacement'
        . ($colonneDuree ? ', duree' : '');
    $sql = '
        SELECT ' . $champs . '
          FROM Video
         WHERE type = ? AND reference IN (' . $placeholders . ')
         ORDER BY reference, camId';
    $types = 'i' . str_repeat('i', count($eventIds));
    $args  = array_merge([EV_AV_VIDEOS_TYPE_DECOUPEE], $eventIds);

    return ['sql' => $sql, 'types' => $types, 'args' => $args];
}

/**
 * Met en forme la réponse à partir des lignes brutes déjà filtrées en base.
 *
 * @param array $evenements Lignes événements : event_id, matchIdRef, match_id,
 *                          chrono, code, souscode.
 * @param array $videos     Lignes vidéos type=0 : reference, videoId, camId,
 *                          nomFichier, emplacement, duree.
 * @return array
 */
function evAvVideosFormerReponse(array $evenements, array $videos): array
{
    // Indexe les vidéos par référence (event_id).
    $parReference = [];
    foreach ($videos as $v) {
        $ref = (int)$v['reference'];
        $url = evAvVideosConstruireUrl(
            isset($v['emplacement']) ? (string)$v['emplacement'] : null,
            isset($v['nomFichier']) ? (string)$v['nomFichier'] : null
        );
        if ($url === null) {
            continue; // vidéo inexploitable (pas de fichier/hôte) : exclue
        }

        // Durée DÉCLARÉE par l'appareil de capture : indicative et NON vérifiée.
        // Ce n'est PAS la durée réelle du fichier. Le Backend obtiendra la durée
        // réelle par inspection audiovisuelle. Exposée seulement si > 0.
        $dureeDeclaree = null;
        if (isset($v['duree']) && $v['duree'] !== null && $v['duree'] !== '') {
            $d = filter_var($v['duree'], FILTER_VALIDATE_INT);
            if ($d !== false && $d > 0) {
                $dureeDeclaree = [
                    'valeur'   => $d,
                    'unite'    => 'ms',
                    'source'   => 'appareil_capture',
                    'verifiee' => false,
                ];
            }
        }

        $parReference[$ref][] = [
            'videoId'       => isset($v['videoId']) ? (int)$v['videoId'] : null,
            'camId'         => isset($v['camId']) && $v['camId'] !== null ? (int)$v['camId'] : null,
            'nomFichier'    => isset($v['nomFichier']) ? (string)$v['nomFichier'] : null,
            'url'           => $url,
            'dureeReelleFichier' => null, // à renseigner par le moteur audiovisuel du Backend
            'dureeDeclaree' => $dureeDeclaree,
        ];
    }

    $sortie = [];
    foreach ($evenements as $e) {
        $eventId = (int)$e['event_id'];
        $sortie[] = [
            'eventId' => $eventId,
            'match'   => [
                // interne : clé primaire numérique Legacy (TableMatch.match_id,
                // AUTO_INCREMENT retourné par upsertMatch).
                'idInterneLegacy' => isset($e['match_id']) && $e['match_id'] !== null ? (int)$e['match_id'] : null,
                // externe : référence fournie par le client / SyncRef
                // (TableMatch.matchIdRef = matchLongId), clé de liaison des événements.
                'idExterneReference' => isset($e['matchIdRef']) ? $e['matchIdRef'] : null,
            ],
            'chronoMs' => isset($e['chrono']) ? (int)$e['chrono'] : null,
            'code'     => isset($e['code']) ? (int)$e['code'] : null,
            'sousCode' => isset($e['souscode']) ? (int)$e['souscode'] : null,
            'videos'   => isset($parReference[$eventId]) ? $parReference[$eventId] : [],
        ];
    }

    return $sortie;
}
