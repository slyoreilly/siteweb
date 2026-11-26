<?php

declare(strict_types=1);

// La date SQL du match détermine la priorité, pas le chrono des événements.
// CURRENT_TIMESTAMP utilise le fuseau de la session SQL Legacy.
// Le MIN des événements n'est calculé que pour le match sélectionné.
// Les sondes scalaires LIMIT 1 restent ciblées sur la référence du candidat.
const REQUETE_MATCH_ACTIF_SURFACE = "
    SELECT a.arenaId, m.match_id,
           (SELECT MIN(e.chrono)
              FROM TableEvenement0 e
             WHERE e.match_event_id = m.matchIdRef
               AND e.code = 10 AND e.souscode = 0 AND e.chrono > 0) AS debutMs
      FROM TableArena a
      LEFT JOIN TableMatch m ON m.match_id = (
          SELECT candidat.match_id
            FROM TableMatch candidat
           WHERE candidat.arenaId = a.arenaId
             AND candidat.date <= CURRENT_TIMESTAMP
             AND COALESCE(candidat.statut, '') NOT IN ('30', 'F')
             AND (
                 candidat.statut = '10'
                 OR (
                     SELECT 1 FROM TableEvenement0 e
                      WHERE e.match_event_id = candidat.matchIdRef AND e.code = 10 AND e.souscode = 0
                      LIMIT 1
                 ) = 1
             )
             AND (
                 SELECT 1 FROM TableEvenement0 e
                  WHERE e.match_event_id = candidat.matchIdRef AND e.code = 10 AND e.souscode = 10
                  LIMIT 1
             ) IS NULL
           ORDER BY candidat.date DESC, candidat.match_id DESC
           LIMIT 1
      )
     WHERE a.arenaId = ?";

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
