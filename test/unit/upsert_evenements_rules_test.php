<?php

declare(strict_types=1);

require_once __DIR__ . '/../../api/lib/upsert_evenements_rules.php';

function assertTrue($condition, string $message): void
{
    if ($condition !== true) {
        throw new RuntimeException('ECHEC: ' . $message);
    }
}

function assertFalse($condition, string $message): void
{
    if ($condition !== false) {
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
    assertSame('plateforme', upsertEvenementsSourceNormalisee('  Plateforme  '), 'normalisation de source');
    assertSame('', upsertEvenementsSourceNormalisee(null), 'normalisation null');

    assertTrue(
        upsertEvenementsSourceAutoriseePourCreationLocale(array('source' => 'plateforme')),
        'source plateforme doit etre autorisee'
    );
    assertTrue(
        upsertEvenementsSourceAutoriseePourCreationLocale(array('source' => 'PLATEFORMEWEB')),
        'source plateformeweb doit etre autorisee'
    );
    assertFalse(
        upsertEvenementsSourceAutoriseePourCreationLocale(array('source' => 'mobile')),
        'source mobile ne doit pas etre autorisee'
    );
    assertFalse(
        upsertEvenementsSourceAutoriseePourCreationLocale(array()),
        'source absente ne doit pas etre autorisee'
    );

    echo "OK upsert_evenements_rules_test\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}

?>