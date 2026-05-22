<?php
require '../scriptsphp/defenvvar.php';

define('MONITORING_INDEX_MAX_LIMIT', 100);
define('MONITORING_INDEX_DEFAULT_LIMIT', 100);
define('MONITORING_INDEX_ACTIVE_HOURS', 48);

$MONITORING_INDEX_ALLOWED_FILES = array(
    'BDLogFile.txt',
    'synccamlog.txt',
    'syncamlog.txt',
    'logDBSS.txt'
);

function monitoringIndexRespond($httpCode, $payload)
{
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function monitoringIndexGetHeader($headerName)
{
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));
    if (isset($_SERVER[$key])) {
        return trim((string)$_SERVER[$key]);
    }

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $name => $value) {
            if (strcasecmp($name, $headerName) === 0) {
                return trim((string)$value);
            }
        }
    }

    return '';
}

function monitoringIndexProvidedToken()
{
    $authorization = monitoringIndexGetHeader('Authorization');
    if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
        return trim($matches[1]);
    }

    $token = monitoringIndexGetHeader('X-Monitoring-Index-Token');
    if ($token !== '') {
        return $token;
    }

    return monitoringIndexGetHeader('X-Sync-Token');
}

function monitoringIndexValidateAuth()
{
    $expectedToken = (string)(getenv('MONITORING_FILES_INDEX_TOKEN') ?: getenv('SYNC_INBOUND_TOKEN') ?: '');
    if ($expectedToken === '') {
        error_log('[monitoring_files_index] missing token configuration');
        monitoringIndexRespond(500, array('ok' => false, 'error' => 'server configuration error'));
    }

    $providedToken = monitoringIndexProvidedToken();
    if ($providedToken === '') {
        monitoringIndexRespond(401, array('ok' => false, 'error' => 'missing token'));
    }

    if (!hash_equals($expectedToken, $providedToken)) {
        error_log('[monitoring_files_index] invalid token');
        monitoringIndexRespond(403, array('ok' => false, 'error' => 'invalid token'));
    }
}

function monitoringIndexParam($name, $default = '')
{
    if (isset($_GET[$name])) {
        return trim((string)$_GET[$name]);
    }
    return $default;
}

function monitoringIndexIntParam($name, $default, $min, $max)
{
    $value = monitoringIndexParam($name, (string)$default);
    if (!preg_match('/^[0-9]+$/', $value)) {
        monitoringIndexRespond(400, array('ok' => false, 'error' => 'invalid ' . $name));
    }

    $intValue = intval($value);
    if ($intValue < $min || $intValue > $max) {
        monitoringIndexRespond(400, array('ok' => false, 'error' => $name . ' out of range'));
    }

    return $intValue;
}

function monitoringIndexTelIdValide($telId)
{
    return preg_match('/^[A-Za-z0-9_.-]{1,80}$/', $telId) === 1 && strpos($telId, '..') === false;
}

function monitoringIndexDateFolders($days)
{
    $folders = array();
    $today = mktime(0, 0, 0, intval(date('n')), intval(date('j')), intval(date('Y')));

    for ($i = 0; $i < $days; $i++) {
        $ts = strtotime('-' . $i . ' day', $today);
        $folders[] = array(
            'date' => date('Y_n_j', $ts),
            'isoDate' => date('Y-m-d', $ts)
        );
    }

    return $folders;
}

function monitoringIndexBaseDir()
{
    return realpath(__DIR__ . '/../monitoring');
}

function monitoringIndexBaseUrl()
{
    $host = isset($_SERVER['HTTP_HOST']) ? trim((string)$_SERVER['HTTP_HOST']) : 'syncstats.com';
    $host = preg_replace('/[^A-Za-z0-9.\-:]/', '', $host);
    if ($host === '') {
        $host = 'syncstats.com';
    }

    $https = isset($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
    $scheme = $https ? 'https' : 'http';

    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && preg_match('/^https?$/', $_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $scheme = strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']);
    }

    return $scheme . '://' . $host;
}

function monitoringIndexFileUrl($baseUrl, $telId, $date, $fileName)
{
    return $baseUrl . '/monitoring/' . rawurlencode($telId) . '/' . rawurlencode($date) . '/' . rawurlencode($fileName);
}

function monitoringIndexFilesForDate($baseDir, $baseUrl, $telId, $date, $allowedFiles)
{
    $result = array();
    $deviceDir = $baseDir . DIRECTORY_SEPARATOR . $telId . DIRECTORY_SEPARATOR . $date;
    $realDir = realpath($deviceDir);

    if ($realDir === false || strpos($realDir, $baseDir . DIRECTORY_SEPARATOR) !== 0 || !is_dir($realDir)) {
        return $result;
    }

    foreach ($allowedFiles as $fileName) {
        $path = $realDir . DIRECTORY_SEPARATOR . $fileName;
        if (!is_file($path) || !is_readable($path)) {
            continue;
        }

        $size = @filesize($path);
        $modifiedAt = @filemtime($path);
        if ($size === false || $modifiedAt === false) {
            error_log('[monitoring_files_index] unreadable metadata | telId=' . $telId . ' | date=' . $date . ' | file=' . $fileName);
            continue;
        }

        $result[] = array(
            'name' => $fileName,
            'size' => intval($size),
            'modifiedAt' => gmdate('Y-m-d\TH:i:s\Z', $modifiedAt),
            'url' => monitoringIndexFileUrl($baseUrl, $telId, $date, $fileName)
        );
    }

    return $result;
}

function monitoringIndexFetchCameras($conn, $limitPlusOne, $offset, $telId)
{
    if ($telId !== '') {
        $sql = "SELECT telId, camId, userId, DATE_FORMAT(dernierMaJ, '%Y-%m-%d %H:%i:%s') AS dernierMaJ
                FROM StatutCam
                WHERE telId = ? AND dernierMaJ >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                ORDER BY dernierMaJ DESC
                LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception('prepare failed: ' . mysqli_error($conn));
        }
        $activeHours = MONITORING_INDEX_ACTIVE_HOURS;
        mysqli_stmt_bind_param($stmt, 'siii', $telId, $activeHours, $limitPlusOne, $offset);
    } else {
        $sql = "SELECT telId, camId, userId, DATE_FORMAT(dernierMaJ, '%Y-%m-%d %H:%i:%s') AS dernierMaJ
                FROM StatutCam
                WHERE dernierMaJ >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                ORDER BY dernierMaJ DESC
                LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception('prepare failed: ' . mysqli_error($conn));
        }
        $activeHours = MONITORING_INDEX_ACTIVE_HOURS;
        mysqli_stmt_bind_param($stmt, 'iii', $activeHours, $limitPlusOne, $offset);
    }

    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new Exception('execute failed: ' . $error);
    }

    $rows = array();

    mysqli_stmt_bind_result($stmt, $rowTelId, $rowCamId, $rowUserId, $rowDernierMaJ);
    while (mysqli_stmt_fetch($stmt)) {
        $rows[] = array(
            'telId' => $rowTelId,
            'camId' => $rowCamId,
            'userId' => $rowUserId,
            'dernierMaJ' => $rowDernierMaJ
        );
    }
    mysqli_stmt_close($stmt);

    return $rows;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    monitoringIndexRespond(405, array('ok' => false, 'error' => 'method not allowed'));
}

monitoringIndexValidateAuth();

$days = monitoringIndexIntParam('days', 2, 1, 2);
$limit = monitoringIndexIntParam('limit', MONITORING_INDEX_DEFAULT_LIMIT, 1, MONITORING_INDEX_MAX_LIMIT);
$offset = monitoringIndexIntParam('offset', 0, 0, 1000000);
$telId = monitoringIndexParam('telId', '');

if ($telId !== '' && !monitoringIndexTelIdValide($telId)) {
    monitoringIndexRespond(400, array('ok' => false, 'error' => 'invalid telId'));
}

$baseDir = monitoringIndexBaseDir();
if ($baseDir === false) {
    error_log('[monitoring_files_index] monitoring directory not found');
    monitoringIndexRespond(500, array('ok' => false, 'error' => 'monitoring directory unavailable'));
}

try {
    $rows = monitoringIndexFetchCameras($conn, $limit + 1, $offset, $telId);
} catch (Exception $e) {
    error_log('[monitoring_files_index] DB error | ' . $e->getMessage());
    monitoringIndexRespond(500, array('ok' => false, 'error' => 'database error'));
}

$hasMore = count($rows) > $limit;
if ($hasMore) {
    array_pop($rows);
}

$baseUrl = monitoringIndexBaseUrl();
$dateFolders = monitoringIndexDateFolders($days);
$items = array();
$filesCount = 0;
global $MONITORING_INDEX_ALLOWED_FILES;

foreach ($rows as $row) {
    $rowTelId = isset($row['telId']) ? trim((string)$row['telId']) : '';
    if ($rowTelId === '' || !monitoringIndexTelIdValide($rowTelId)) {
        error_log('[monitoring_files_index] skipped invalid telId from StatutCam');
        continue;
    }

    $dates = array();
    foreach ($dateFolders as $dateFolder) {
        $files = monitoringIndexFilesForDate($baseDir, $baseUrl, $rowTelId, $dateFolder['date'], $MONITORING_INDEX_ALLOWED_FILES);
        $filesCount += count($files);
        $dates[] = array(
            'date' => $dateFolder['date'],
            'isoDate' => $dateFolder['isoDate'],
            'files' => $files
        );
    }

    $items[] = array(
        'telId' => $rowTelId,
        'camId' => isset($row['camId']) ? (string)$row['camId'] : null,
        'userId' => isset($row['userId']) ? (string)$row['userId'] : null,
        'dernierMaJ' => isset($row['dernierMaJ']) ? (string)$row['dernierMaJ'] : null,
        'dates' => $dates
    );
}

error_log('[monitoring_files_index] ok | items=' . count($items) . ' | files=' . $filesCount . ' | days=' . $days . ' | limit=' . $limit . ' | offset=' . $offset . ($telId !== '' ? ' | telId=' . $telId : ''));

monitoringIndexRespond(200, array(
    'ok' => true,
    'generatedAt' => gmdate('Y-m-d\TH:i:s\Z'),
    'days' => $days,
    'limit' => $limit,
    'offset' => $offset,
    'hasMore' => $hasMore,
    'activeHours' => MONITORING_INDEX_ACTIVE_HOURS,
    'allowedFiles' => $MONITORING_INDEX_ALLOWED_FILES,
    'items' => $items
));
?>
