<?php

define('MONITORING_UPLOAD_BASE_DIR', realpath(__DIR__ . '/../monitoring') ?: (__DIR__ . '/../monitoring'));

function monitoringUploadRespond($httpCode, $payload)
{
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function monitoringUploadErreur($code, $message, $details = array())
{
    return array(
        'ok' => false,
        'success' => false,
        'error' => array(
            'code' => $code,
            'message' => $message,
            'details' => $details
        )
    );
}

function monitoringUploadDerniereErreur()
{
    $error = error_get_last();
    if (!$error || !isset($error['message'])) {
        return '';
    }

    return (string)$error['message'];
}

function monitoringUploadTelIdValide($telId)
{
    return preg_match('/^[A-Za-z0-9_.-]{1,80}$/', $telId) === 1 && strpos($telId, '..') === false;
}

function monitoringUploadNomFichierValide($fileName)
{
    return preg_match('/^[A-Za-z0-9_.-]{1,120}$/', $fileName) === 1
        && strpos($fileName, '..') === false
        && basename($fileName) === $fileName;
}

function monitoringUploadDossierPourDate($telId, $timestamp = null)
{
    if ($timestamp === null) {
        $timestamp = time();
    }

    return $telId . '/' . date('Y_n_j', $timestamp);
}

function monitoringUploadCheminCible($baseDir, $dossier, $fileName)
{
    $baseDir = rtrim($baseDir, "/\\");
    return $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dossier) . DIRECTORY_SEPARATOR . $fileName;
}

function monitoringUploadAssurerDossier($targetDir)
{
    if (is_dir($targetDir)) {
        if (!is_writable($targetDir)) {
            return monitoringUploadErreur(
                'monitoring_directory_not_writable',
                'Le dossier monitoring cible existe, mais il n est pas inscriptible.',
                array('path' => $targetDir)
            );
        }

        return array('ok' => true);
    }

    clearstatcache();
    $created = @mkdir($targetDir, 0777, true);
    if (!$created || !is_dir($targetDir)) {
        return monitoringUploadErreur(
            'monitoring_directory_create_failed',
            'Le dossier monitoring cible n a pas pu etre cree.',
            array(
                'path' => $targetDir,
                'phpError' => monitoringUploadDerniereErreur()
            )
        );
    }

    if (!is_writable($targetDir)) {
        return monitoringUploadErreur(
            'monitoring_directory_not_writable',
            'Le dossier monitoring cible a ete cree, mais il n est pas inscriptible.',
            array('path' => $targetDir)
        );
    }

    return array('ok' => true);
}

function monitoringUploadLireFichierTemporaire($tmpName)
{
    if ($tmpName === '' || !is_file($tmpName) || !is_readable($tmpName)) {
        return monitoringUploadErreur(
            'uploaded_file_unreadable',
            'Le fichier temporaire recu est introuvable ou illisible.',
            array('tmpName' => $tmpName)
        );
    }

    $content = @file_get_contents($tmpName);
    if ($content === false) {
        return monitoringUploadErreur(
            'uploaded_file_read_failed',
            'Le fichier temporaire recu n a pas pu etre lu.',
            array('tmpName' => $tmpName, 'phpError' => monitoringUploadDerniereErreur())
        );
    }

    return array('ok' => true, 'content' => $content);
}

function monitoringUploadEcrireFichier($targetPath, $content)
{
    $bytes = @file_put_contents($targetPath, $content, LOCK_EX);
    if ($bytes === false) {
        return monitoringUploadErreur(
            'monitoring_file_write_failed',
            'Le fichier monitoring n a pas pu etre ecrit.',
            array(
                'path' => $targetPath,
                'phpError' => monitoringUploadDerniereErreur()
            )
        );
    }

    clearstatcache(true, $targetPath);
    if (!is_file($targetPath)) {
        return monitoringUploadErreur(
            'monitoring_file_missing_after_write',
            'L ecriture a semble reussir, mais le fichier monitoring est absent.',
            array('path' => $targetPath)
        );
    }

    return array('ok' => true, 'bytesWritten' => $bytes);
}

function monitoringUploadTraiter($paramsJSON, $file, $baseDir = MONITORING_UPLOAD_BASE_DIR, $timestamp = null)
{
    $params = json_decode((string)$paramsJSON, true);
    if (!is_array($params)) {
        return monitoringUploadErreur(
            'invalid_params_json',
            'Le parametre params doit etre un objet JSON valide.',
            array('jsonError' => json_last_error_msg())
        );
    }

    $telId = isset($params['telId']) ? trim((string)$params['telId']) : '';
    if ($telId === '') {
        $telId = 'LostNfound';
    }

    if (!monitoringUploadTelIdValide($telId)) {
        return monitoringUploadErreur(
            'invalid_tel_id',
            'Le telId contient des caracteres non permis.',
            array('telId' => $telId)
        );
    }

    if (!isset($file['size']) || intval($file['size']) <= 0) {
        return monitoringUploadErreur(
            'empty_uploaded_file',
            'Le fichier recu est vide.',
            array('size' => isset($file['size']) ? intval($file['size']) : null)
        );
    }

    if (isset($file['error']) && intval($file['error']) !== UPLOAD_ERR_OK) {
        return monitoringUploadErreur(
            'uploaded_file_error',
            'PHP a signale une erreur de reception du fichier.',
            array('uploadError' => intval($file['error']))
        );
    }

    $fileName = isset($file['name']) ? (string)$file['name'] : '';
    if (!monitoringUploadNomFichierValide($fileName)) {
        return monitoringUploadErreur(
            'invalid_file_name',
            'Le nom du fichier monitoring contient des caracteres non permis.',
            array('fileName' => $fileName)
        );
    }

    $tmpName = isset($file['tmp_name']) ? (string)$file['tmp_name'] : '';
    $readResult = monitoringUploadLireFichierTemporaire($tmpName);
    if (!$readResult['ok']) {
        return $readResult;
    }

    $dossier = monitoringUploadDossierPourDate($telId, $timestamp);
    $targetPath = monitoringUploadCheminCible($baseDir, $dossier, $fileName);
    $targetDir = dirname($targetPath);

    $dirResult = monitoringUploadAssurerDossier($targetDir);
    if (!$dirResult['ok']) {
        return $dirResult + array('dossier' => $dossier, 'fileName' => $fileName);
    }

    $writeResult = monitoringUploadEcrireFichier($targetPath, $readResult['content']);
    if (!$writeResult['ok']) {
        return $writeResult + array('dossier' => $dossier, 'fileName' => $fileName);
    }

    return array(
        'ok' => true,
        'success' => true,
        'dossier' => $dossier,
        'fileName' => $fileName,
        'bytesWritten' => $writeResult['bytesWritten'],
        'path' => $targetPath
    );
}

function monitoringUploadHttpCode($result)
{
    if (isset($result['ok']) && $result['ok']) {
        return 200;
    }

    $code = isset($result['error']['code']) ? $result['error']['code'] : '';
    if (in_array($code, array('invalid_params_json', 'invalid_tel_id', 'empty_uploaded_file', 'uploaded_file_error', 'invalid_file_name', 'uploaded_file_unreadable', 'uploaded_file_read_failed'), true)) {
        return 400;
    }

    return 500;
}

function monitoringUploadExecuterRequete()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        monitoringUploadRespond(405, monitoringUploadErreur('method_not_allowed', 'La methode HTTP doit etre POST.'));
    }

    $paramsJSON = isset($_POST['params']) ? $_POST['params'] : '';
    $file = isset($_FILES['fichier']) ? $_FILES['fichier'] : array('size' => 0);
    $result = monitoringUploadTraiter($paramsJSON, $file);

    if (!$result['ok']) {
        error_log('[monitoring_upload] echec | ' . json_encode($result, JSON_UNESCAPED_SLASHES));
    } else {
        error_log('[monitoring_upload] succes | dossier=' . $result['dossier'] . ' | file=' . $result['fileName'] . ' | bytes=' . $result['bytesWritten']);
    }

    monitoringUploadRespond(monitoringUploadHttpCode($result), $result);
}

$scriptFilename = isset($_SERVER['SCRIPT_FILENAME']) ? realpath($_SERVER['SCRIPT_FILENAME']) : false;
if ($scriptFilename !== false && $scriptFilename === realpath(__FILE__)) {
    monitoringUploadExecuterRequete();
}

?>
