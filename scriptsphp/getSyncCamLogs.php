<?php
require '../scriptsphp/defenvvar.php';

define('SYNC_CAM_LOG_FILE', 'syncamlog.txt');
define('SYNC_CAM_LOG_MAX_BYTES', 262144);
define('SYNC_CAM_LOG_DEFAULT_TAIL_BYTES', 204800);

function syncCamLogJson($payload, $httpCode = 200)
{
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function syncCamLogParam($name, $default = '')
{
    if (isset($_POST[$name])) {
        return trim($_POST[$name]);
    }
    if (isset($_GET[$name])) {
        return trim($_GET[$name]);
    }
    return $default;
}

function syncCamLogAutorise()
{
    global $conn;

    $userId = isset($_COOKIE['userId']) ? trim($_COOKIE['userId']) : '';
    $ligueId = isset($_COOKIE['ligueId']) ? trim($_COOKIE['ligueId']) : '';

    if ($userId === '' || $ligueId === '' || !preg_match('/^[A-Za-z0-9_.@-]{1,100}$/', $userId) || !preg_match('/^[0-9]{1,10}$/', $ligueId)) {
        return false;
    }

    $userEsc = mysqli_real_escape_string($conn, $userId);
    $ligueEsc = mysqli_real_escape_string($conn, $ligueId);
    $sql = "SELECT AbonnementLigue.type
            FROM AbonnementLigue
            JOIN TableUser ON TableUser.noCompte = AbonnementLigue.userid
            WHERE AbonnementLigue.ligueid='{$ligueEsc}' AND TableUser.username='{$userEsc}'
            LIMIT 1";

    $result = mysqli_query($conn, $sql);
    if (!$result || mysqli_num_rows($result) === 0) {
        return false;
    }

    $row = mysqli_fetch_assoc($result);
    return isset($row['type']) && intval($row['type']) < 10;
}

function syncCamLogTelIdValide($telId)
{
    return preg_match('/^[A-Za-z0-9_.-]{1,80}$/', $telId) === 1 && strpos($telId, '..') === false;
}

function syncCamLogDateValide($date)
{
    return preg_match('/^[0-9]{4}[_-][0-9]{1,2}[_-][0-9]{1,2}$/', $date) === 1 && strpos($date, '..') === false;
}

function syncCamLogDateNormalisee($date)
{
    return str_replace('-', '_', $date);
}

function syncCamLogDateTimestamp($date)
{
    $parts = preg_split('/[_-]/', $date);
    if (count($parts) !== 3) {
        return 0;
    }

    return mktime(0, 0, 0, intval($parts[1]), intval($parts[2]), intval($parts[0]));
}

function syncCamLogBaseDir()
{
    return realpath(__DIR__ . '/../monitoring');
}

function syncCamLogPath($telId, $date)
{
    $baseDir = syncCamLogBaseDir();
    if ($baseDir === false) {
        return false;
    }

    if (!syncCamLogTelIdValide($telId) || !syncCamLogDateValide($date)) {
        return false;
    }

    $date = syncCamLogDateNormalisee($date);
    $file = $baseDir . DIRECTORY_SEPARATOR . $telId . DIRECTORY_SEPARATOR . $date . DIRECTORY_SEPARATOR . SYNC_CAM_LOG_FILE;
    $realFile = realpath($file);

    if ($realFile === false || strpos($realFile, $baseDir . DIRECTORY_SEPARATOR) !== 0 || !is_file($realFile)) {
        return false;
    }

    return $realFile;
}

function syncCamLogListDates($telId)
{
    $baseDir = syncCamLogBaseDir();
    if ($baseDir === false || !syncCamLogTelIdValide($telId)) {
        syncCamLogJson(array('ok' => false, 'message' => 'telId invalide.'), 400);
    }

    $deviceDir = realpath($baseDir . DIRECTORY_SEPARATOR . $telId);
    if ($deviceDir === false || strpos($deviceDir, $baseDir . DIRECTORY_SEPARATOR) !== 0 || !is_dir($deviceDir)) {
        syncCamLogJson(array('ok' => true, 'telId' => $telId, 'dates' => array()));
    }

    $dates = array();
    foreach (scandir($deviceDir) as $entry) {
        if ($entry === '.' || $entry === '..' || !syncCamLogDateValide($entry)) {
            continue;
        }

        $file = $deviceDir . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . SYNC_CAM_LOG_FILE;
        if (!is_file($file)) {
            continue;
        }

        $dates[] = array(
            'date' => $entry,
            'size' => filesize($file),
            'modifiedAt' => date('Y-m-d H:i:s', filemtime($file))
        );
    }

    usort($dates, function ($a, $b) {
        $cmp = syncCamLogDateTimestamp($b['date']) - syncCamLogDateTimestamp($a['date']);
        if ($cmp !== 0) {
            return $cmp;
        }
        return strcmp($b['modifiedAt'], $a['modifiedAt']);
    });

    syncCamLogJson(array(
        'ok' => true,
        'telId' => $telId,
        'dates' => $dates,
        'latestDate' => count($dates) > 0 ? $dates[0]['date'] : null
    ));
}

function syncCamLogReadRange($file, $offset, $length)
{
    $size = filesize($file);
    $offset = max(0, min(intval($offset), $size));
    $length = max(1, min(intval($length), SYNC_CAM_LOG_MAX_BYTES));
    $length = min($length, $size - $offset);

    $handle = fopen($file, 'rb');
    if (!$handle) {
        syncCamLogJson(array('ok' => false, 'message' => 'Lecture impossible.'), 500);
    }

    fseek($handle, $offset);
    $text = $length > 0 ? fread($handle, $length) : '';
    fclose($handle);

    syncCamLogJson(array(
        'ok' => true,
        'text' => $text,
        'size' => $size,
        'start' => $offset,
        'end' => $offset + strlen($text),
        'truncatedBefore' => $offset > 0,
        'truncatedAfter' => ($offset + strlen($text)) < $size
    ));
}

function syncCamLogTail($file, $bytes)
{
    $size = filesize($file);
    $bytes = max(1, min(intval($bytes), SYNC_CAM_LOG_MAX_BYTES));
    $start = max(0, $size - $bytes);
    syncCamLogReadRange($file, $start, $bytes);
}

if (!syncCamLogAutorise()) {
    syncCamLogJson(array('ok' => false, 'message' => 'Acces refuse.'), 403);
}

$action = syncCamLogParam('action', '');
$telId = syncCamLogParam('telId', '');
$date = syncCamLogParam('date', '');

if ($action === 'listDates') {
    syncCamLogListDates($telId);
}

if (!syncCamLogTelIdValide($telId) || !syncCamLogDateValide($date)) {
    syncCamLogJson(array('ok' => false, 'message' => 'Parametres invalides.'), 400);
}

$file = syncCamLogPath($telId, $date);
if ($file === false) {
    syncCamLogJson(array('ok' => false, 'message' => 'Log introuvable.'), 404);
}

if ($action === 'tailLog') {
    syncCamLogTail($file, syncCamLogParam('bytes', SYNC_CAM_LOG_DEFAULT_TAIL_BYTES));
}

if ($action === 'readLog') {
    syncCamLogReadRange($file, syncCamLogParam('offset', 0), syncCamLogParam('length', SYNC_CAM_LOG_DEFAULT_TAIL_BYTES));
}

if ($action === 'download') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . SYNC_CAM_LOG_FILE . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

syncCamLogJson(array('ok' => false, 'message' => 'Action inconnue.'), 400);
