<?php

declare(strict_types=1);

require_once __DIR__ . '/../../api/lib/upsert_match_rules.php';

function assertTrue($condition, string $message): void
{
    if ($condition !== true) {
        throw new RuntimeException('ECHEC: ' . $message);
    }
}

function assertSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            'ECHEC: ' . $message . ' | attendu=' . var_export($expected, true) . ' recu=' . var_export($actual, true)
        );
    }
}

try {
    $n = upsertMatchNormaliser(array(
        'GameLocId' => '9',
        'GameComId' => '',
        'eqDom' => '1',
        'eqVis' => '2',
        'ligueId' => '7',
        'matchLongId' => '  2026-match-1  ',
        'scoreDom' => null,
        'scoreVis' => '3',
        'date' => 1700000000000,
        'arenaId' => '12',
        'cleValeur' => '{"x":1}',
        'etat' => '4',
        'dernierMAJ' => '1700000000999'
    ));

    assertSame(9, $n['GameLocId'], 'GameLocId normalise');
    assertSame(0, $n['GameComId'], 'GameComId normalise vide');
    assertSame('2026-match-1', $n['matchLongId'], 'matchLongId trim');
    assertSame(0, $n['scoreDom'], 'scoreDom fallback');
    assertSame(3, $n['scoreVis'], 'scoreVis parse');
    assertSame(12, $n['arenaId'], 'arenaId parse');
    assertSame(4, $n['etat'], 'etat parse');
    assertTrue(strlen($n['dateSql']) >= 19, 'dateSql format minimal');

    echo "OK upsert_match_rules_test\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}

?>