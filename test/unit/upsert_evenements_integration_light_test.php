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

    $casCreationLocaleMobile = upsertEvenementsDecisionCreationLocale(array(
        'EventComId' => '',
        'source' => 'mobile'
    ));
    assertTrue($casCreationLocaleMobile['eventComIdVide'], 'EventComId vide attendu');
    assertTrue($casCreationLocaleMobile['autorise'], 'EventComId vide autorise sans contrainte source');
    assertSame(
        'event_com_id_vide_autorise',
        $casCreationLocaleMobile['raison'],
        'raison attendue sans contrainte source'
    );

    $casSansSource = upsertEvenementsDecisionCreationLocale(array(
        'EventComId' => ''
    ));
    assertTrue($casSansSource['autorise'], 'EventComId vide sans source autorise');

    echo "OK upsert_evenements_integration_light_test\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}

?>