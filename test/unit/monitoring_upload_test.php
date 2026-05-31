<?php

require __DIR__ . '/../../syncscript/monitoringUpload.php';

function assertTrue($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "ECHEC: " . $message . PHP_EOL);
        exit(1);
    }
}

function assertSameValue($expected, $actual, $message)
{
    if ($expected !== $actual) {
        fwrite(STDERR, "ECHEC: " . $message . PHP_EOL);
        fwrite(STDERR, "Attendu: " . var_export($expected, true) . PHP_EOL);
        fwrite(STDERR, "Recu: " . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

function supprimerRecursivement($path)
{
    if (!file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        unlink($path);
        return;
    }

    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        supprimerRecursivement($path . DIRECTORY_SEPARATOR . $item);
    }

    rmdir($path);
}

function fichierUpload($name, $content)
{
    $tmp = tempnam(sys_get_temp_dir(), 'monitoring-upload-');
    file_put_contents($tmp, $content);

    return array(
        'name' => $name,
        'tmp_name' => $tmp,
        'size' => strlen($content),
        'type' => 'text/plain',
        'error' => UPLOAD_ERR_OK
    );
}

$baseDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'monitoring-upload-test-' . uniqid('', true);
$dateTs = strtotime('2026-05-31 12:00:00 UTC');
$dateDir = '2026_5_31';
mkdir($baseDir, 0777, true);

try {
    $syncCamLog = fichierUpload('synccamlog.txt', "contenu sync cam\n");
    $result = monitoringUploadTraiter(json_encode(array('telId' => 'CAM_TEST')), $syncCamLog, $baseDir, $dateTs);
    assertTrue($result['ok'], 'synccamlog.txt doit etre rapporte en succes');
    assertSameValue(200, monitoringUploadHttpCode($result), 'le succes doit retourner HTTP 200');
    $syncCamLogPath = $baseDir . DIRECTORY_SEPARATOR . 'CAM_TEST' . DIRECTORY_SEPARATOR . $dateDir . DIRECTORY_SEPARATOR . 'synccamlog.txt';
    assertTrue(is_file($syncCamLogPath), 'synccamlog.txt doit etre ecrit');
    assertSameValue("contenu sync cam\n", file_get_contents($syncCamLogPath), 'le contenu de synccamlog.txt doit etre exact');

    $bdLog = fichierUpload('BDLogFile.txt', "contenu bd log\n");
    $result = monitoringUploadTraiter(json_encode(array('telId' => 'CAM_TEST')), $bdLog, $baseDir, $dateTs);
    assertTrue($result['ok'], 'BDLogFile.txt doit etre rapporte en succes');
    $bdLogPath = $baseDir . DIRECTORY_SEPARATOR . 'CAM_TEST' . DIRECTORY_SEPARATOR . $dateDir . DIRECTORY_SEPARATOR . 'BDLogFile.txt';
    assertTrue(is_file($bdLogPath), 'BDLogFile.txt doit etre ecrit');
    assertSameValue("contenu bd log\n", file_get_contents($bdLogPath), 'le contenu de BDLogFile.txt doit etre exact');

    file_put_contents($baseDir . DIRECTORY_SEPARATOR . 'CAM_BLOQUE', 'bloque la creation du dossier');
    $result = monitoringUploadTraiter(json_encode(array('telId' => 'CAM_BLOQUE')), fichierUpload('synccamlog.txt', 'x'), $baseDir, $dateTs);
    assertTrue(!$result['ok'], 'un echec mkdir doit etre rapporte en erreur');
    assertSameValue('monitoring_directory_create_failed', $result['error']['code'], 'l erreur mkdir doit etre structuree');
    assertSameValue(500, monitoringUploadHttpCode($result), 'l echec mkdir doit retourner HTTP 500');

    $conflictDir = $baseDir . DIRECTORY_SEPARATOR . 'CAM_ECRITURE' . DIRECTORY_SEPARATOR . $dateDir . DIRECTORY_SEPARATOR . 'BDLogFile.txt';
    mkdir($conflictDir, 0777, true);
    $result = monitoringUploadTraiter(json_encode(array('telId' => 'CAM_ECRITURE')), fichierUpload('BDLogFile.txt', 'x'), $baseDir, $dateTs);
    assertTrue(!$result['ok'], 'un echec file_put_contents doit etre rapporte en erreur');
    assertSameValue('monitoring_file_write_failed', $result['error']['code'], 'l erreur file_put_contents doit etre structuree');
    assertSameValue(500, monitoringUploadHttpCode($result), 'l echec file_put_contents doit retourner HTTP 500');

    echo "OK monitoring_upload_test: succes reel ecrit synccamlog.txt et BDLogFile.txt; echecs mkdir/file_put_contents retournes en erreur." . PHP_EOL;
} finally {
    supprimerRecursivement($baseDir);
    if (isset($syncCamLog['tmp_name'])) {
        @unlink($syncCamLog['tmp_name']);
    }
    if (isset($bdLog['tmp_name'])) {
        @unlink($bdLog['tmp_name']);
    }
}

?>
