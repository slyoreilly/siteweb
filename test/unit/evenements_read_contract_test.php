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

    echo "OK evenements_read_contract_test\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}

?>