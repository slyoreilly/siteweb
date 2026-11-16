<?php

declare(strict_types=1);

const REQUETE_CAMERAS_SURFACE = "
    SELECT DISTINCT appareil.telId
      FROM TableArena surface
      LEFT JOIN abonAppareilSurface association ON association.surfaceId = surface.arenaId
       AND (association.role > 99 OR association.role < 1)
      LEFT JOIN Telephones appareil ON appareil.telId = association.telId
       AND appareil.telId > 0
     WHERE surface.arenaId = ?
     ORDER BY appareil.telId";

function reponseCamerasSurface(int $arenaId, callable $consulter): array
{
    try {
        $lignes = $consulter($arenaId);
    } catch (Throwable $erreur) {
        error_log('[getCamerasParSurface] Échec de la consultation (' . get_class($erreur) . ')');
        return [500, ['etat' => 'erreur_consultation']];
    }

    if ($lignes === []) {
        return [404, ['etat' => 'surface_inexistante', 'arenaId' => $arenaId]];
    }

    $cameras = [];
    foreach ($lignes as $telId) {
        if ($telId !== null) {
            $cameras[] = (int) $telId;
        }
    }

    return [200, [
        'etat' => $cameras === [] ? 'aucune_camera' : 'cameras_associees',
        'arenaId' => $arenaId,
        'cameraIds' => $cameras,
    ]];
}
