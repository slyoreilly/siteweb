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

// Vérifier directement le parcours PHP, y compris son arrêt immédiat.
$verifierSelection = static function (array $candidats, array $evenements, ?array $attendu, array $visitesAttendues): void {
    $visites = [];
    $obtenu = premierMatchOuvertSurface($candidats, static function (?string $reference) use ($evenements, &$visites): array {
        $visites[] = $reference;
        if (!array_key_exists($reference, $evenements)) {
            throw new RuntimeException('Lecture inattendue après le premier match ouvert');
        }
        return $evenements[$reference];
    });
    if ($obtenu !== $attendu || $visites !== $visitesAttendues) {
        throw new RuntimeException('Sélection séquentielle inattendue');
    }
};
$candidat = static function (int $id, int $demarre = 1): array {
    return ['match_id' => $id, 'matchIdRef' => (string)$id, 'demarreParStatut' => $demarre];
};
$sansEvenement = ['demarre' => null, 'termine' => null, 'debutMs' => null];
$avecDebut = ['demarre' => '1', 'termine' => '0', 'debutMs' => '1700000000100'];
$avecFin = ['demarre' => '1', 'termine' => '1', 'debutMs' => '1700000000100'];
$verifierSelection([], [], null, []);
$verifierSelection([$candidat(24053), $candidat(24052)], ['24053' => $avecDebut],
    ['match_id' => 24053, 'debutMs' => '1700000000100'], ['24053']);
$verifierSelection([$candidat(3), $candidat(2, 0), $candidat(1)],
    ['3' => $avecFin, '2' => $sansEvenement, '1' => $sansEvenement],
    ['match_id' => 1, 'debutMs' => null], ['3', '2', '1']);
$verifierSelection([$candidat(1, 0)], ['1' => $avecDebut],
    ['match_id' => 1, 'debutMs' => '1700000000100'], ['1']);
$verifierSelection([$candidat(1, 0)], ['1' => $sansEvenement], null, ['1']);
$verifierSelection([$candidat(1)], ['1' => $avecFin], null, ['1']);
$candidats = [];
$evenements = [];
$visitesAttendues = [];
for ($id = 65; $id >= 1; --$id) {
    $candidats[] = $candidat($id);
    $evenements[(string)$id] = $id === 1 ? $sansEvenement : $avecFin;
    $visitesAttendues[] = (string)$id;
}
$verifierSelection($candidats, $evenements, ['match_id' => 1, 'debutMs' => null], $visitesAttendues);

// Le générateur ne doit même pas être repris après le candidat gagnant.
$candidatsParesseux = (static function () use ($candidat): Generator {
    yield $candidat(1);
    throw new RuntimeException('Parcours poursuivi après sélection');
})();
if (premierMatchOuvertSurface($candidatsParesseux, static function (?string $reference) use ($sansEvenement): array {
    return $sansEvenement;
}) !== ['match_id' => 1, 'debutMs' => null]) {
    throw new RuntimeException('Sélection du générateur inattendue');
}

echo "OK\n";
