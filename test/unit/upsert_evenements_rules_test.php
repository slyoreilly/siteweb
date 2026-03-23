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
    $decisionCreationOK = upsertEvenementsDecisionCreationLocale(array('EventComId' => '', 'source' => 'plateforme'));
    assertTrue($decisionCreationOK['autorise'], 'creation locale autorisee');
    assertSame('event_com_id_vide_autorise', $decisionCreationOK['raison'], 'raison creation autorisee');

    $decisionCreationSansSource = upsertEvenementsDecisionCreationLocale(array('EventComId' => ''));
    assertTrue($decisionCreationSansSource['autorise'], 'creation locale sans source autorisee');
    assertSame('event_com_id_vide_autorise', $decisionCreationSansSource['raison'], 'raison creation sans source');

    $v1 = upsertEvenementsValidationChrono(array('chrono' => 100));
    assertTrue($v1['ok'], 'chrono positif valide');

    $v2 = upsertEvenementsValidationChrono(array('chrono' => -1));
    assertFalse($v2['ok'], 'chrono negatif invalide');
    assertSame('chrono_negatif', $v2['raison'], 'raison chrono negatif');

    $v3 = upsertEvenementsValidationChrono(array());
    assertFalse($v3['ok'], 'chrono absent invalide');
    assertSame('chrono_absent', $v3['raison'], 'raison chrono absent');

    echo "OK upsert_evenements_rules_test\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}

?>
