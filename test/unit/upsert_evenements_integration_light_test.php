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
    $casEventComIdPresent = upsertEvenementsDecisionCreationLocale(array(
        'EventComId' => 123,
        'source' => 'mobile'
    ));
    assertFalse($casEventComIdPresent['eventComIdVide'], 'EventComId present doit etre detecte');
    assertTrue($casEventComIdPresent['autorise'], 'EventComId present doit toujours etre autorise');
    assertSame('event_com_id_present', $casEventComIdPresent['raison'], 'raison attendue EventComId present');

    $casCreationLocaleValide = upsertEvenementsDecisionCreationLocale(array(
        'EventComId' => '',
        'source' => 'plateforme'
    ));
    assertTrue($casCreationLocaleValide['eventComIdVide'], 'EventComId vide attendu');
    assertTrue($casCreationLocaleValide['autorise'], 'source plateforme doit autoriser la creation locale');
    assertSame(
        'event_com_id_vide_source_autorisee',
        $casCreationLocaleValide['raison'],
        'raison attendue source autorisee'
    );

    $casCreationLocaleRefusee = upsertEvenementsDecisionCreationLocale(array(
        'EventComId' => null,
        'source' => 'mobile'
    ));
    assertTrue($casCreationLocaleRefusee['eventComIdVide'], 'EventComId vide attendu');
    assertFalse($casCreationLocaleRefusee['autorise'], 'source mobile doit etre refusee');
    assertSame(
        'event_com_id_vide_source_non_autorisee',
        $casCreationLocaleRefusee['raison'],
        'raison attendue source non autorisee'
    );

    $casSansSource = upsertEvenementsDecisionCreationLocale(array(
        'EventComId' => ''
    ));
    assertFalse($casSansSource['autorise'], 'EventComId vide sans source doit etre refuse');

    echo "OK upsert_evenements_integration_light_test\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}

?>