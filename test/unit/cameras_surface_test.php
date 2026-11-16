<?php

declare(strict_types=1);

require_once __DIR__ . '/../../api/lib/cameras_surface.php';

$cas = [
    [[], [404, ['etat' => 'surface_inexistante', 'arenaId' => 7]]],
    [[null], [200, ['etat' => 'aucune_camera', 'arenaId' => 7, 'cameraIds' => []]]],
    [[null, '12', 23], [200, ['etat' => 'cameras_associees', 'arenaId' => 7, 'cameraIds' => [12, 23]]]],
    [null, [500, ['etat' => 'erreur_consultation']]],
];

foreach ($cas as [$lignes, $attendu]) {
    $reponse = reponseCamerasSurface(7, static function (int $arenaId) use ($lignes): array {
        if ($arenaId !== 7 || $lignes === null) {
            throw new RuntimeException('Erreur simulée');
        }
        return $lignes;
    });
    if ($reponse !== $attendu) {
        throw new RuntimeException('Réponse inattendue : ' . json_encode($reponse));
    }
}

echo "Tests du contrat réussis.\n";
