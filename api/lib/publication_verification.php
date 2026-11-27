<?php
declare(strict_types=1);
require_once __DIR__ . '/publication_lecture.php';

function publicationIdentifiantsExacts(mixed $valeur): array
{
    if (!is_string($valeur)) throw new InvalidArgumentException('joueurIds requis (chaine CSV, vide admis)');
    if ($valeur === '') return [];
    $ids = explode(',', $valeur);
    if (count($ids) > 100) throw new InvalidArgumentException('100 identifiants maximum');
    foreach ($ids as &$id) {
        if (!preg_match('/^[1-9][0-9]{0,9}$/D', $id) || (float)$id > 2147483647) {
            throw new InvalidArgumentException('joueurIds invalides');
        }
        $id = (int)$id;
    }
    unset($id);
    if (count(array_unique($ids)) !== count($ids)) throw new InvalidArgumentException('joueurIds repetes');
    sort($ids, SORT_NUMERIC);
    return $ids;
}

// Une instruction SELECT, sans limite nominative ni suppression des périodes.
// Les valeurs entières ont déjà été validées ; toutes restent liées.
function publicationRequeteVerification(int $ligue, int $saison, ?int $equipe, array $ids): array
{
    $liste = $ids ? implode(',', array_fill(0, count($ids), '?')) : 'NULL';
    $sql = "SELECT 'ligue' AS type, JSON_OBJECT('id', ID_Ligue, 'nom', Nom_Ligue) AS donnees
        FROM Ligue WHERE ID_Ligue = ?
        UNION ALL SELECT 'saison', JSON_OBJECT('id', saisonId, 'ligueId', ligueRef,
            'debut', premierMatch, 'fin', dernierMatch)
        FROM TableSaison WHERE ligueRef = ? AND saisonId = ?
        UNION ALL SELECT 'equipe', JSON_OBJECT('id', equipe_id, 'nom', nom_equipe)
        FROM TableEquipe WHERE equipe_id = ?
        UNION ALL SELECT 'joueur', JSON_OBJECT('id', joueur_id, 'nom', NomJoueur,
            'numero', NumeroJoueur, 'position', position)
        FROM TableJoueur WHERE joueur_id IN ($liste)
        UNION ALL SELECT 'joueurEquipe', JSON_OBJECT('id', abonJouEq, 'joueurId', joueurId,
            'equipeId', equipeId, 'permission', permission, 'debut', debutAbon, 'fin', finAbon)
        FROM abonJoueurEquipe WHERE joueurId IN ($liste)
        UNION ALL SELECT 'equipeLigue', JSON_OBJECT('equipeId', equipeId, 'ligueId', ligueId,
            'permission', permission, 'debut', debutAbon, 'fin', finAbon)
        FROM abonEquipeLigue WHERE equipeId = ? OR equipeId IN
            (SELECT equipeId FROM abonJoueurEquipe WHERE joueurId IN ($liste))
        UNION ALL SELECT 'joueurLigue', JSON_OBJECT('id', abonJouLig, 'joueurId', joueurId,
            'ligueId', ligueId, 'permission', permission, 'debut', debutAbon, 'fin', finAbon)
        FROM abonJoueurLigue WHERE joueurId IN ($liste)";
    $valeurs = [$ligue, $ligue, $saison, $equipe, ...$ids, ...$ids, $equipe, ...$ids, ...$ids];
    return [$sql, str_repeat('i', count($valeurs)), $valeurs];
}

function publicationDatesExploitables(array $periode): bool
{
    foreach (['debut', 'fin'] as $borne) {
        $date = $periode[$borne] ?? null;
        if (!is_string($date) || !preg_match('/^[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}(?: [0-9]{2}:[0-9]{2}:[0-9]{2})?$/D', $date)) return false;
        $analyse = date_parse($date);
        if ($analyse['error_count'] || $analyse['warning_count']) return false;
    }
    return $periode['debut'] <= $periode['fin'];
}

function publicationIntersection(array ...$periodes): bool
{
    foreach ($periodes as $periode) if (!publicationDatesExploitables($periode)) return false;
    $debuts = array_map(static fn($p) => strlen($p['debut']) === 10 ? $p['debut'] . ' 00:00:00' : $p['debut'], $periodes);
    $fins = array_map(static fn($p) => strlen($p['fin']) === 10 ? $p['fin'] . ' 00:00:00' : $p['fin'], $periodes);
    return max($debuts) <= min($fins);
}

function publicationConstruireVerification(string $instance, int $ligue, int $saison, ?int $equipe,
    array $ids, array $lignes): array
{
    $groupes = array_fill_keys(['ligue', 'saison', 'equipe', 'joueur', 'joueurEquipe', 'equipeLigue', 'joueurLigue'], []);
    foreach ($lignes as $ligne) {
        if (!array_key_exists($ligne['type'], $groupes)) throw new RuntimeException('Type SQL inattendu');
        $objet = json_decode($ligne['donnees'], true, 32, JSON_THROW_ON_ERROR);
        ksort($objet);
        $groupes[$ligne['type']][] = $objet;
    }
    // L'ordre du moteur et celui des IDs dans la requête ne changent pas la révision.
    foreach ($groupes as &$groupe) {
        usort($groupe, static fn($a, $b) => strcmp(json_encode($a, JSON_THROW_ON_ERROR), json_encode($b, JSON_THROW_ON_ERROR)));
    }
    unset($groupe);
    sort($ids, SORT_NUMERIC);
    $periode = count($groupes['saison']) === 1 ? $groupes['saison'][0] : [];
    $anomalies = [];
    if (count($groupes['ligue']) !== 1) $anomalies[] = 'LIGUE_ABSENTE_OU_MULTIPLE';
    if (!publicationDatesExploitables($periode)) $anomalies[] = 'SAISON_INEXPLOITABLE';
    $abonnements = array_values(array_filter($groupes['equipeLigue'], static fn($a) =>
        $a['equipeId'] === $equipe && $a['ligueId'] === $ligue && $a['permission'] !== null
        && $a['permission'] < 31 && publicationIntersection($a, $periode)));
    if ($equipe !== null && (count($groupes['equipe']) !== 1 || count($abonnements) !== 1)) {
        $anomalies[] = 'EQUIPE_HORS_CONTEXTE_OU_PERIODES_MULTIPLES';
    }
    foreach ($groupes['equipeLigue'] as $a) {
        if ($equipe !== null && $a['equipeId'] === $equipe && $a['ligueId'] === $ligue
            && $a['permission'] !== null && $a['permission'] < 31 && !publicationDatesExploitables($a)) {
            $anomalies[] = 'PERIODE_EQUIPE_INEXPLOITABLE';
        }
    }
    $identites = [];
    foreach ($ids as $id) {
        $joueurs = array_values(array_filter($groupes['joueur'], static fn($j) => $j['id'] === $id));
        if (count($joueurs) !== 1) $anomalies[] = 'IDENTITE_ABSENTE_OU_MULTIPLE:' . $id;
        $cibles = [];
        $autres = false;
        foreach ($groupes['joueurEquipe'] as $inscription) {
            if ($equipe !== null && $inscription['joueurId'] === $id && $inscription['equipeId'] === $equipe
                && $inscription['permission'] !== null && $inscription['permission'] < 31
                && !publicationDatesExploitables($inscription)) $anomalies[] = 'PERIODE_JOUEUR_INEXPLOITABLE:' . $id;
            if ($inscription['joueurId'] !== $id || $inscription['permission'] === null
                || $inscription['permission'] >= 31 || !publicationIntersection($inscription, $periode)) continue;
            if ($inscription['equipeId'] !== $equipe) $autres = true;
            else foreach ($abonnements as $abonnement) {
                if (publicationIntersection($inscription, $abonnement, $periode)) { $cibles[] = $inscription['id']; break; }
            }
            foreach ($groupes['equipeLigue'] as $a) {
                if ($a['equipeId'] === $inscription['equipeId'] && $a['ligueId'] !== $ligue
                    && $a['permission'] !== null && $a['permission'] < 31
                    && publicationIntersection($a, $inscription, $periode)) $autres = true;
            }
        }
        foreach ($groupes['joueurLigue'] as $a) {
            if ($a['joueurId'] === $id && $a['ligueId'] !== $ligue && $a['permission'] !== null
                && $a['permission'] < 31 && publicationIntersection($a, $periode)) $autres = true;
        }
        if (count($cibles) > 1) $anomalies[] = 'PERIODES_JOUEUR_MULTIPLES:' . $id;
        $identites[] = ['id' => $id, 'existe' => count($joueurs) === 1,
            'autresAffiliations' => $autres, 'inscriptionsCibles' => $cibles];
    }
    $revision = strtoupper(hash('sha256', json_encode(['verification-identifiants-v1', $instance,
        $ligue, $saison, $equipe, $ids, $groupes], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    return ['version' => 'verification-identifiants-v1', 'instance' => $instance,
        'ligueId' => $ligue, 'saisonId' => $saison, 'equipeId' => $equipe,
        'revision' => $revision, 'valide' => !$anomalies, 'lectureComplete' => true,
        'ecrituresAutorisees' => false, 'identites' => $identites,
        'anomalies' => $anomalies, 'donnees' => $groupes];
}
