<?php

declare(strict_types=1);

// Même horloge SQL pour tous les lots, sans conversion de fuseau.
const REQUETE_SURFACE_MATCH_ACTIF = "
    SELECT CURRENT_TIMESTAMP FROM TableArena WHERE arenaId = ?";

// LIMIT 20 borne seulement un lot, jamais le nombre total de candidats.
const REQUETE_CANDIDATS_MATCH_ACTIF = "
    SELECT match_id, matchIdRef, statut = '10' AS demarreParStatut, `date`
      FROM TableMatch
     WHERE arenaId = ? AND `date` <= ?
       AND COALESCE(statut, '') NOT IN ('30', 'F')";
const SUITE_CANDIDATS_MATCH_ACTIF = "
       AND (`date` < ? OR (`date` = ? AND match_id < ?))";
const ORDRE_CANDIDATS_MATCH_ACTIF = "
     ORDER BY `date` DESC, match_id DESC LIMIT 20";

// Une seule consultation des événements de code 10 pour chaque candidat visité.
const REQUETE_EVENEMENTS_MATCH_ACTIF = "
    SELECT MAX(souscode = 0), MAX(souscode = 10),
           MIN(CASE WHEN souscode = 0 AND chrono > 0 THEN chrono END)
      FROM TableEvenement0
     WHERE match_event_id = ? AND code = 10";

function candidatsMatchActifSurface(mysqli $conn, int $arenaId, string $maintenant): Generator
{
    $datePrecedente = null;
    $idPrecedent = null;
    do {
        $sql = REQUETE_CANDIDATS_MATCH_ACTIF;
        if ($datePrecedente !== null) {
            $sql .= SUITE_CANDIDATS_MATCH_ACTIF;
        }
        if (function_exists('tracerMatchActif')) tracerMatchActif('avant_lot_candidats curseur=' . ($idPrecedent ?? 'premier'));
        $requete = $conn->prepare($sql . ORDRE_CANDIDATS_MATCH_ACTIF);
        try {
            if ($datePrecedente === null) {
                $requete->bind_param('is', $arenaId, $maintenant);
            } else {
                $requete->bind_param('isssi', $arenaId, $maintenant,
                    $datePrecedente, $datePrecedente, $idPrecedent);
            }
            $requete->execute();
            $requete->bind_result($matchId, $reference, $demarreParStatut, $date);
            $lot = [];
            while ($requete->fetch()) {
                $lot[] = ['match_id' => $matchId, 'matchIdRef' => $reference,
                    'demarreParStatut' => $demarreParStatut, 'date' => $date];
            }
        } finally {
            // Libérer le résultat avant les lectures d'événements sur la même connexion.
            $requete->close();
        }
        if (function_exists('tracerMatchActif')) tracerMatchActif('apres_lot_candidats nombre=' . count($lot));
        foreach ($lot as $candidat) {
            yield $candidat;
            $datePrecedente = $candidat['date'];
            $idPrecedent = $candidat['match_id'];
        }
    } while (count($lot) === 20);
}

function premierMatchOuvertSurface(iterable $candidats, callable $consulterEvenements): ?array
{
    foreach ($candidats as $candidat) {
        if (function_exists('tracerMatchActif')) tracerMatchActif('candidat ' . json_encode([
            'match_id' => $candidat['match_id'], 'matchIdRef' => $candidat['matchIdRef']]));
        $evenements = $consulterEvenements($candidat['matchIdRef']);
        if ((int)$evenements['termine'] === 1) {
            continue;
        }
        if ((int)$candidat['demarreParStatut'] === 1 || (int)$evenements['demarre'] === 1) {
            return ['match_id' => $candidat['match_id'], 'debutMs' => $evenements['debutMs']];
        }
    }
    return null;
}

function consulterMatchActifSurface(mysqli $conn, int $arenaId): array
{
    if (function_exists('tracerMatchActif')) tracerMatchActif('avant_lecture_surface arenaId=' . $arenaId);
    $surface = $conn->prepare(REQUETE_SURFACE_MATCH_ACTIF);
    try {
        $surface->bind_param('i', $arenaId);
        $surface->execute();
        $surface->bind_result($maintenant);
        if (!$surface->fetch()) {
            return [];
        }
    } finally {
        $surface->close();
    }
    if (function_exists('tracerMatchActif')) tracerMatchActif('apres_lecture_surface');

    if (function_exists('tracerMatchActif')) tracerMatchActif('avant_preparation_evenements');
    $evenements = $conn->prepare(REQUETE_EVENEMENTS_MATCH_ACTIF);
    if (function_exists('tracerMatchActif')) tracerMatchActif('apres_preparation_evenements');
    try {
        $match = premierMatchOuvertSurface(
            candidatsMatchActifSurface($conn, $arenaId, $maintenant),
            static function (?string $reference) use ($evenements): array {
                if (function_exists('tracerMatchActif')) tracerMatchActif('avant_consultation_evenements reference=' . json_encode($reference));
                $evenements->bind_param('s', $reference);
                $evenements->execute();
                $evenements->store_result();
                $evenements->bind_result($demarre, $termine, $debutMs);
                $evenements->fetch();
                $evenements->free_result();
                if (function_exists('tracerMatchActif')) tracerMatchActif('apres_consultation_evenements reference=' . json_encode($reference));
                return ['demarre' => $demarre, 'termine' => $termine, 'debutMs' => $debutMs];
            }
        );
    } finally {
        $evenements->close();
    }
    if (function_exists('tracerMatchActif')) tracerMatchActif('match_selectionne match_id=' . ($match['match_id'] ?? 'aucun'));
    return [$match ?? ['match_id' => null, 'debutMs' => null]];
}

function reponseMatchActifSurface(int $arenaId, callable $consulter): array
{
    try {
        $lignes = $consulter($arenaId);
    } catch (Throwable $erreur) {
        error_log('[getMatchActifParSurface] ' . $erreur->getMessage());
        return [500, ['etat' => 'erreur_consultation']];
    }

    if (!$lignes) {
        return [404, ['etat' => 'surface_inexistante', 'arenaId' => $arenaId]];
    }
    $match = $lignes[0];
    if ($match['match_id'] === null) {
        return [200, ['etat' => 'aucun_match_actif', 'arenaId' => $arenaId, 'match' => null]];
    }

    return [200, [
        'etat' => 'match_actif',
        'arenaId' => $arenaId,
        'match' => [
            'matchId' => (int)$match['match_id'],
            'debutMs' => $match['debutMs'] === null ? null : (int)$match['debutMs'],
        ],
    ]];
}
