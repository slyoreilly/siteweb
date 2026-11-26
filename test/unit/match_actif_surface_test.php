<?php

declare(strict_types=1);

require_once __DIR__ . '/../../api/lib/match_actif_surface.php';

$match = ['match_id' => '123', 'debutMs' => '1700000000100'];
$cas = [
    [[$match], 200, 'match_actif', ['matchId' => 123, 'debutMs' => 1700000000100]],
    [[['match_id' => '123', 'debutMs' => null]], 200, 'match_actif', ['matchId' => 123, 'debutMs' => null]],
    [[['match_id' => null, 'debutMs' => null]], 200, 'aucun_match_actif', null],
    // La consultation fournit le résultat déjà ordonné : aucun conflit HTTP 409.
    [[$match, ['match_id' => '124', 'debutMs' => null]], 200, 'match_actif', ['matchId' => 123, 'debutMs' => 1700000000100]],
    [[], 404, 'surface_inexistante', null],
    [null, 500, 'erreur_consultation', null],
];

foreach ($cas as [$lignes, $codeAttendu, $etatAttendu, $matchAttendu]) {
    $attendu = ['etat' => $etatAttendu];
    if ($codeAttendu !== 500) {
        $attendu['arenaId'] = 42;
    }
    if ($codeAttendu === 200) {
        $attendu['match'] = $matchAttendu;
    }

    $obtenu = reponseMatchActifSurface(42, static function (int $arenaId) use ($lignes): array {
        if ($arenaId !== 42 || $lignes === null) {
            throw new RuntimeException('Erreur de consultation simulée');
        }
        return $lignes;
    });

    if ($obtenu !== [$codeAttendu, $attendu]) {
        throw new RuntimeException('Réponse inattendue pour ' . $etatAttendu);
    }
}

echo "OK\n";
