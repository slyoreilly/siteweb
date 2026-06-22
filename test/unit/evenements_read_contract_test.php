<?php

declare(strict_types=1);

require_once __DIR__ . '/../../api/lib/evenements_read_contract.php';

function assertSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            'ECHEC: ' . $message . ' | attendu=' . var_export($expected, true) . ' recu=' . var_export($actual, true)
        );
    }
}

function construireReponseEvenements(array $events): array
{
    return array(
        'ok' => true,
        'serverTime' => '2026-06-21T14:30:00+00:00',
        'heure' => 1782052200,
        'tempsDepart' => 1700,
        'events' => $events,
    );
}

try {
    $eventRow = array(
        'type' => 0,
        'eventId' => 123,
        'chrono' => 1700,
        'matchIdRef' => 'M1',
        'matchId' => 77,
        'ligueId' => 8,
        'arenaId' => 2,
        'eqDom' => 10,
        'eqVis' => 11,
        'date' => '2026-03-22',
        'code' => 2,
        'sousCode' => 1,
        'scoringEnd' => 10,
    );

    $eventItem = evenementsReadConstruireItem($eventRow);
    assertSame(123, $eventItem['EventComId'], 'EventComId present sur evenement');

    $clipRow = $eventRow;
    $clipRow['type'] = 5;
    $clipRow['eventId'] = 456;

    $clipItem = evenementsReadConstruireItem($clipRow);
    assertSame(null, $clipItem['EventComId'], 'EventComId null sur clip');

    $reponseSansEvenement = construireReponseEvenements(array());
    $jsonSansEvenement = json_encode($reponseSansEvenement, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $decodeSansEvenement = json_decode($jsonSansEvenement, true);

    assertSame(JSON_ERROR_NONE, json_last_error(), 'reponse sans evenement reste un JSON valide');
    assertSame(array(), $decodeSansEvenement['events'], 'reponse sans evenement conserve events vide');
    assertSame(1782052200, $decodeSansEvenement['heure'], 'champ heure present sans evenement');

    $reponseAvecEvenements = construireReponseEvenements(array($eventItem, $clipItem));
    $jsonAvecEvenements = json_encode($reponseAvecEvenements, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $decodeAvecEvenements = json_decode($jsonAvecEvenements, true);

    assertSame(JSON_ERROR_NONE, json_last_error(), 'reponse avec evenements reste un JSON valide');
    assertSame(2, count($decodeAvecEvenements['events']), 'reponse avec evenements conserve tous les elements');
    assertSame('2026-06-21T14:30:00+00:00', $decodeAvecEvenements['serverTime'], 'serverTime reste inchange');
    assertSame(1700, $decodeAvecEvenements['tempsDepart'], 'tempsDepart reste inchange');
    assertSame(1782052200, $decodeAvecEvenements['heure'], 'champ heure present avec evenements');

    $ancienClient = $decodeAvecEvenements;
    unset($ancienClient['heure']);
    assertSame(
        array('ok', 'serverTime', 'tempsDepart', 'events'),
        array_keys($ancienClient),
        'un ancien client peut ignorer heure et retrouver le contrat existant'
    );

    $sourceEndpoint = file_get_contents(__DIR__ . '/../../api/getEvenements.php');
    assertSame(
        true,
        strpos($sourceEndpoint, "'heure'       => time()") !== false,
        'endpoint expose heure depuis l horloge serveur'
    );

    echo "OK evenements_read_contract_test\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}

?>
