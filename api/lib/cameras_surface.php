<?php

declare(strict_types=1);

const REQUETE_CAMERAS_SURFACE = "
    SELECT DISTINCT camera.camId
      FROM TableArena surface
      LEFT JOIN StatutCam camera ON camera.telId > 0
       AND camera.camId > 0
       AND camera.dernierMaJ >= UTC_TIMESTAMP() - INTERVAL 7 DAY
       AND camera.dernierMaJ <= UTC_TIMESTAMP()
       AND (
           camera.arenaId = surface.arenaId
           OR EXISTS (
               SELECT 1
                 FROM abonAppareilSurface association
                WHERE association.surfaceId = surface.arenaId
                  AND association.telId = camera.telId
                  AND (association.role > 99 OR association.role < 1)
           )
       )
     WHERE surface.arenaId = ?
     ORDER BY camera.camId";

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
    foreach ($lignes as $camId) {
        if ($camId !== null) {
            $cameras[] = (int) $camId;
        }
    }

    return [200, [
        'etat' => $cameras === [] ? 'aucune_camera' : 'cameras_associees',
        'arenaId' => $arenaId,
        'cameraIds' => $cameras,
    ]];
}
