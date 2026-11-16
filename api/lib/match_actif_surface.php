<?php

declare(strict_types=1);

// Les événements sont rattachés à la référence textuelle du match.
const REQUETE_MATCH_ACTIF_SURFACE = "
    SELECT a.arenaId, m.match_id,
           (SELECT MIN(e.chrono)
              FROM TableEvenement0 e
             WHERE e.match_event_id = m.matchIdRef
               AND e.code = 10 AND e.souscode = 0 AND e.chrono > 0) AS debutMs
      FROM TableArena a
      LEFT JOIN TableMatch m ON m.arenaId = a.arenaId
       AND COALESCE(m.statut, '') NOT IN ('30', 'F')
       AND (
           m.statut = '10'
           OR EXISTS (
               SELECT 1 FROM TableEvenement0 e
                WHERE e.match_event_id = m.matchIdRef AND e.code = 10 AND e.souscode = 0
           )
       )
       AND NOT EXISTS (
           SELECT 1 FROM TableEvenement0 e
            WHERE e.match_event_id = m.matchIdRef AND e.code = 10 AND e.souscode = 10
       )
     WHERE a.arenaId = ?
     LIMIT 2";

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
    if (count($lignes) > 1) {
        return [409, ['etat' => 'plusieurs_matchs_actifs', 'arenaId' => $arenaId]];
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
