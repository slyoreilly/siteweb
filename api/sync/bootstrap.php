<?php

declare(strict_types=1);

require __DIR__ . '/../../scriptsphp/defenvvar.php';

header('Content-Type: application/json; charset=utf-8');

class SyncFunctionalException extends RuntimeException
{
    private int $httpCode;

    public function __construct(string $message, int $httpCode = 422)
    {
        parent::__construct($message);
        $this->httpCode = $httpCode;
    }

    public function httpCode(): int
    {
        return $this->httpCode;
    }
}

class SyncTechnicalException extends RuntimeException
{
}

function syncRespond(int $httpCode, array $payload): void
{
    http_response_code($httpCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function syncGetHeader(string $headerName): ?string
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

    return null;
}

function syncValidateToken(): void
{
    $expectedToken = (string)(getenv('SYNC_INBOUND_TOKEN') ?: '');
    if ($expectedToken === '') {
        error_log('[sync_inbound] missing SYNC_INBOUND_TOKEN configuration');
        syncRespond(500, ['ok' => false, 'error' => 'server configuration error']);
    }

    $providedToken = syncGetHeader('X-Sync-Token');
    if ($providedToken === null || $providedToken === '') {
        syncRespond(401, ['ok' => false, 'error' => 'missing token']);
    }

    if (!hash_equals($expectedToken, $providedToken)) {
        syncRespond(403, ['ok' => false, 'error' => 'invalid token']);
    }
}

function syncReadMessage(): array
{
    $raw = file_get_contents('php://input');
    $decoded = json_decode((string)$raw, true);

    if (!is_array($decoded)) {
        throw new SyncFunctionalException('invalid JSON body', 400);
    }

    return $decoded;
}

function syncValidateEnvelope(array $message, string $expectedAggregateType): void
{
    $requiredFields = ['aggregateType', 'actionType', 'dedupeKey', 'payload'];
    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $message)) {
            throw new SyncFunctionalException("missing field: {$field}", 422);
        }
    }

    if (!is_string($message['aggregateType']) || $message['aggregateType'] === '') {
        throw new SyncFunctionalException('invalid aggregateType', 422);
    }

    if ($message['aggregateType'] !== $expectedAggregateType) {
        throw new SyncFunctionalException('aggregateType mismatch', 422);
    }

    $allowedActionTypes = ['created', 'updated', 'deleted'];
    if (!is_string($message['actionType']) || !in_array($message['actionType'], $allowedActionTypes, true)) {
        throw new SyncFunctionalException('invalid actionType', 422);
    }

    if (!is_string($message['dedupeKey']) || trim($message['dedupeKey']) === '') {
        throw new SyncFunctionalException('invalid dedupeKey', 422);
    }

    if (strlen($message['dedupeKey']) > 190) {
        throw new SyncFunctionalException('dedupeKey too long', 422);
    }

    if (!is_array($message['payload'])) {
        throw new SyncFunctionalException('invalid payload', 422);
    }
}

function syncToNullableInt($value): ?int
{
    if ($value === null || $value === '' || strtolower((string)$value) === 'null') {
        return null;
    }

    return is_numeric($value) ? (int)$value : null;
}

function syncToInt($value, int $default = 0): int
{
    return is_numeric($value) ? (int)$value : $default;
}

function syncToDateTimeFromIsoOrMs($value): string
{
    if (is_numeric($value)) {
        $ms = (int)round((float)$value);
        if ($ms > 0) {
            return date('Y-m-d H:i:s', (int)floor($ms / 1000));
        }
    }

    if (is_string($value) && trim($value) !== '') {
        $ts = strtotime($value);
        if ($ts !== false) {
            return date('Y-m-d H:i:s', $ts);
        }
    }

    return date('Y-m-d H:i:s');
}

function syncResolveMatch(mysqli $conn, int $gameComId): ?array
{
    $stmt = mysqli_prepare($conn, 'SELECT match_id, matchIdRef FROM TableMatch WHERE match_id = ? LIMIT 1');
    if (!$stmt) {
        throw new SyncTechnicalException('prepare resolve match failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $gameComId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    return $row ?: null;
}

function syncInboxClaim(mysqli $conn, string $endpointName, array $message): array
{
    $messageId = syncToNullableInt($message['messageId'] ?? null);
    $aggregateId = syncToNullableInt($message['aggregateId'] ?? null);
    $payloadJson = json_encode($message['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stmt = mysqli_prepare(
        $conn,
        'INSERT INTO sync_inbox (endpoint, dedupeKey, messageId, aggregateType, aggregateId, actionType, payloadJson, status, retryCount, createdAt, updatedAt)
         VALUES (?, ?, ?, ?, ?, ?, ?, "processing", 0, NOW(), NOW())'
    );

    if (!$stmt) {
        throw new SyncTechnicalException('prepare inbox insert failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmt,
        'ssisiis',
        $endpointName,
        $message['dedupeKey'],
        $messageId,
        $message['aggregateType'],
        $aggregateId,
        $message['actionType'],
        $payloadJson
    );

    if (mysqli_stmt_execute($stmt)) {
        $id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return ['claimed' => true, 'duplicate' => false, 'inboxId' => $id, 'status' => 'processing'];
    }

    $errno = mysqli_errno($conn);
    mysqli_stmt_close($stmt);

    if ($errno !== 1062) {
        throw new SyncTechnicalException('sync_inbox insert failed: ' . mysqli_error($conn));
    }

    $stmtSelect = mysqli_prepare(
        $conn,
        'SELECT syncInboxId, status FROM sync_inbox WHERE endpoint = ? AND dedupeKey = ? LIMIT 1'
    );
    if (!$stmtSelect) {
        throw new SyncTechnicalException('prepare inbox select failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmtSelect, 'ss', $endpointName, $message['dedupeKey']);
    mysqli_stmt_execute($stmtSelect);
    $result = mysqli_stmt_get_result($stmtSelect);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmtSelect);

    if (!$row) {
        throw new SyncTechnicalException('sync_inbox row not found after duplicate');
    }

    $status = (string)$row['status'];
    $inboxId = (int)$row['syncInboxId'];

    if ($status === 'failed') {
        $stmtRetry = mysqli_prepare(
            $conn,
            'UPDATE sync_inbox SET status = "processing", retryCount = retryCount + 1, errorMessage = NULL, updatedAt = NOW() WHERE syncInboxId = ?'
        );
        if (!$stmtRetry) {
            throw new SyncTechnicalException('prepare inbox retry failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmtRetry, 'i', $inboxId);
        mysqli_stmt_execute($stmtRetry);
        mysqli_stmt_close($stmtRetry);

        return ['claimed' => true, 'duplicate' => false, 'inboxId' => $inboxId, 'status' => 'processing'];
    }

    return ['claimed' => false, 'duplicate' => true, 'inboxId' => $inboxId, 'status' => $status];
}

function syncInboxMark(mysqli $conn, int $inboxId, string $status, int $responseCode, ?string $errorMessage): void
{
    $processedAt = ($status === 'processed' || $status === 'rejected') ? 'NOW()' : 'NULL';
    $sql = "UPDATE sync_inbox SET status = ?, responseCode = ?, errorMessage = ?, processedAt = {$processedAt}, updatedAt = NOW() WHERE syncInboxId = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new SyncTechnicalException('prepare inbox update failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'sisi', $status, $responseCode, $errorMessage, $inboxId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function syncLogResult(array $message, string $endpointName, string $result, int $httpCode, array $counters, ?string $errorMessage = null): void
{
    $logData = [
        'scope' => 'sync_inbound',
        'endpoint' => $endpointName,
        'messageId' => syncToNullableInt($message['messageId'] ?? null),
        'dedupeKey' => $message['dedupeKey'] ?? null,
        'aggregateType' => $message['aggregateType'] ?? null,
        'actionType' => $message['actionType'] ?? null,
        'result' => $result,
        'httpCode' => $httpCode,
        'counters' => $counters,
        'error' => $errorMessage,
    ];

    error_log('[sync_inbound] ' . json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function syncInitCounters(): array
{
    return ['success' => 0, 'failure' => 0, 'duplicate' => 0];
}

function syncCounterAdd(array &$counters, string $counter): void
{
    if (!array_key_exists($counter, $counters)) {
        $counters[$counter] = 0;
    }
    $counters[$counter]++;
}
?>
