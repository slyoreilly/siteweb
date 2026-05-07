<?php

declare(strict_types=1);

require __DIR__ . '/../../scriptsphp/defenvvar.php';
require_once __DIR__ . '/SyncAckClient.php';

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

    return syncNormalizeNullStrings($decoded);
}

function syncNormalizeNullStrings($value)
{
    if (is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = syncNormalizeNullStrings($v);
        }
        return $value;
    }

    if (is_string($value) && strtolower(trim($value)) === 'null') {
        return null;
    }

    return $value;
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

function syncNeedAck(string $actionType): bool
{
    return in_array($actionType, ['created', 'updated'], true);
}

function syncSourceEntityIdFromMessage(array $message): ?int
{
    $payload = $message['payload'] ?? [];
    if (!is_array($payload)) {
        return syncToNullableInt($message['aggregateId'] ?? null);
    }

    return syncToNullableInt(
        $payload['id']
        ?? $payload['sourceEntityId']
        ?? $payload['GameLocId']
        ?? $message['aggregateId']
        ?? null
    );
}

function syncAckClient(): SyncAckClient
{
    return SyncAckClient::fromEnv();
}

function syncCalculeUnMatchUrl(): string
{
    $workEnv = (string)(getenv('WORK_ENV') ?: 'development');
    if ($workEnv === 'production') {
        return 'https://syncstats.com/scriptsphp/calculeUnMatch.php';
    }

    return 'http://vieuxsite.sm.syncstats.ca/scriptsphp/calculeUnMatch.php';
}

function syncRelanceCalculeUnMatch(int $noMatchId): void
{
    if ($noMatchId <= 0) {
        return;
    }

    $url = syncCalculeUnMatchUrl();
    $postData = http_build_query(['noMatchId' => $noMatchId]);
    $ch = curl_init($url);

    if ($ch === false) {
        error_log('[sync_inbound] calculeUnMatch init failed | match=' . $noMatchId);
        return;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($postData),
        ],
    ]);

    $result = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($result === false || $httpCode !== 200) {
        error_log('[sync_inbound] calculeUnMatch error | match=' . $noMatchId . ' | http=' . $httpCode . ' | err=' . $curlErr);
        return;
    }

    if (trim((string)$result) === '') {
        error_log('[sync_inbound] calculeUnMatch empty response | match=' . $noMatchId);
    }
}

function syncInboxClaim(mysqli $conn, string $endpointName, array $message): array
{
    $messageId = syncToNullableInt($message['messageId'] ?? null);
    $aggregateId = syncToNullableInt($message['aggregateId'] ?? null);
    $payloadJson = json_encode($message['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stmt = mysqli_prepare(
        $conn,
        'INSERT INTO sync_inbox (endpoint, dedupeKey, messageId, aggregateType, aggregateId, actionType, payloadJson, status, retryCount, ack_status, ack_attempts, createdAt, updatedAt)
         VALUES (?, ?, ?, ?, ?, ?, ?, "processing", 0, "pending", 0, NOW(), NOW())'
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
        return [
            'claimed' => true,
            'duplicate' => false,
            'inboxId' => $id,
            'status' => 'processing',
            'ack_status' => 'pending',
            'source_entity_id' => null,
            'upstream_id' => null,
            'ack_attempts' => 0,
        ];
    }

    $errno = mysqli_errno($conn);
    mysqli_stmt_close($stmt);

    if ($errno !== 1062) {
        throw new SyncTechnicalException('sync_inbox insert failed: ' . mysqli_error($conn));
    }

    $stmtSelect = mysqli_prepare(
        $conn,
        'SELECT syncInboxId, status, ack_status, source_entity_id, upstream_id, ack_attempts
         FROM sync_inbox WHERE endpoint = ? AND dedupeKey = ? LIMIT 1'
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

    return [
        'claimed' => false,
        'duplicate' => true,
        'inboxId' => (int)$row['syncInboxId'],
        'status' => (string)$row['status'],
        'ack_status' => (string)($row['ack_status'] ?? 'pending'),
        'source_entity_id' => syncToNullableInt($row['source_entity_id'] ?? null),
        'upstream_id' => isset($row['upstream_id']) ? (string)$row['upstream_id'] : null,
        'ack_attempts' => syncToInt($row['ack_attempts'] ?? 0),
    ];
}

function syncInboxMarkStatus(mysqli $conn, int $inboxId, string $status, int $responseCode, ?string $errorMessage): void
{
    $doneAt = $status === 'done' ? 'NOW()' : 'doneAt';
    $sql = "UPDATE sync_inbox SET status = ?, responseCode = ?, errorMessage = ?, doneAt = {$doneAt}, updatedAt = NOW() WHERE syncInboxId = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new SyncTechnicalException('prepare inbox update failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'sisi', $status, $responseCode, $errorMessage, $inboxId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function syncInboxSetAckContext(mysqli $conn, int $inboxId, ?int $sourceEntityId, string $upstreamId): void
{
    $stmt = mysqli_prepare($conn, 'UPDATE sync_inbox SET source_entity_id = ?, upstream_id = ?, updatedAt = NOW() WHERE syncInboxId = ?');
    if (!$stmt) {
        throw new SyncTechnicalException('prepare inbox ack context failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'isi', $sourceEntityId, $upstreamId, $inboxId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function syncInboxMarkAckSuccess(mysqli $conn, int $inboxId, int $attempts, int $httpCode): void
{
    $stmt = mysqli_prepare(
        $conn,
        'UPDATE sync_inbox
         SET ack_status = "success", ack_attempts = ?, ack_http_code = ?, ack_last_error = NULL, ack_next_attempt_at = NULL, ack_at = NOW(), status = "done", doneAt = NOW(), updatedAt = NOW()
         WHERE syncInboxId = ?'
    );
    if (!$stmt) {
        throw new SyncTechnicalException('prepare ack success update failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'iii', $attempts, $httpCode, $inboxId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function syncInboxMarkAckRetry(mysqli $conn, int $inboxId, int $attempts, ?int $httpCode, ?string $error, string $nextAttemptAt): void
{
    $stmt = mysqli_prepare(
        $conn,
        'UPDATE sync_inbox
         SET ack_status = "retrying", ack_attempts = ?, ack_http_code = ?, ack_last_error = ?, ack_next_attempt_at = ?, updatedAt = NOW()
         WHERE syncInboxId = ?'
    );
    if (!$stmt) {
        throw new SyncTechnicalException('prepare ack retry update failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'iissi', $attempts, $httpCode, $error, $nextAttemptAt, $inboxId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function syncInboxMarkAckRejected(mysqli $conn, int $inboxId, int $attempts, ?int $httpCode, ?string $error): void
{
    $stmt = mysqli_prepare(
        $conn,
        'UPDATE sync_inbox
         SET ack_status = "rejected", ack_attempts = ?, ack_http_code = ?, ack_last_error = ?, ack_next_attempt_at = NULL, updatedAt = NOW(), status = "rejected", errorMessage = ?
         WHERE syncInboxId = ?'
    );
    if (!$stmt) {
        throw new SyncTechnicalException('prepare ack rejected update failed: ' . mysqli_error($conn));
    }

    $err = $error ?? 'ack rejected';
    mysqli_stmt_bind_param($stmt, 'iissi', $attempts, $httpCode, $err, $err, $inboxId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function syncInboxMarkAckFailedAuth(mysqli $conn, int $inboxId, int $attempts, ?int $httpCode, ?string $error): void
{
    $stmt = mysqli_prepare(
        $conn,
        'UPDATE sync_inbox
         SET ack_status = "failed_auth", ack_attempts = ?, ack_http_code = ?, ack_last_error = ?, ack_next_attempt_at = NULL, updatedAt = NOW(), status = "failed_auth", errorMessage = ?
         WHERE syncInboxId = ?'
    );
    if (!$stmt) {
        throw new SyncTechnicalException('prepare ack failed_auth update failed: ' . mysqli_error($conn));
    }

    $err = $error ?? 'ack failed auth';
    mysqli_stmt_bind_param($stmt, 'iissi', $attempts, $httpCode, $err, $err, $inboxId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function syncInboxMarkAckMaxAttempts(mysqli $conn, int $inboxId, int $attempts, ?int $httpCode, ?string $error): void
{
    $stmt = mysqli_prepare(
        $conn,
        'UPDATE sync_inbox
         SET ack_status = "rejected", ack_attempts = ?, ack_http_code = ?, ack_last_error = ?, ack_next_attempt_at = NULL, updatedAt = NOW(), status = "rejected", errorMessage = ?
         WHERE syncInboxId = ?'
    );
    if (!$stmt) {
        throw new SyncTechnicalException('prepare ack max attempts update failed: ' . mysqli_error($conn));
    }

    $err = $error ?? 'ack max attempts reached';
    mysqli_stmt_bind_param($stmt, 'iissi', $attempts, $httpCode, $err, $err, $inboxId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function syncAcquireRowLock(mysqli $conn, int $inboxId): bool
{
    $lockName = 'sync_inbox_ack_' . $inboxId;
    $stmt = mysqli_prepare($conn, 'SELECT GET_LOCK(?, 0) AS l');
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 's', $lockName);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);

    return isset($row['l']) && (int)$row['l'] === 1;
}

function syncReleaseRowLock(mysqli $conn, int $inboxId): void
{
    $lockName = 'sync_inbox_ack_' . $inboxId;
    $stmt = mysqli_prepare($conn, 'SELECT RELEASE_LOCK(?)');
    if (!$stmt) {
        return;
    }
    mysqli_stmt_bind_param($stmt, 's', $lockName);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function syncCreatedToAckLatencyMs(array $message): ?int
{
    if (!isset($message['createdAt']) || !is_string($message['createdAt'])) {
        return null;
    }

    $ts = strtotime($message['createdAt']);
    if ($ts === false) {
        return null;
    }

    return (int)max(0, round((microtime(true) - $ts) * 1000));
}

function syncLogResult(
    array $message,
    string $endpointName,
    string $result,
    int $httpCode,
    array $counters,
    ?string $errorMessage = null,
    ?array $ack = null
): void {
    $logData = [
        'scope' => 'sync_inbound',
        'endpoint' => $endpointName,
        'messageId' => syncToNullableInt($message['messageId'] ?? null),
        'dedupeKey' => $message['dedupeKey'] ?? null,
        'aggregateType' => $message['aggregateType'] ?? null,
        'actionType' => $message['actionType'] ?? null,
        'result' => $result,
        'httpCode' => $httpCode,
        'metrics' => $counters,
        'ack' => $ack,
        'error' => $errorMessage,
    ];

    error_log('[sync_inbound] ' . json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function syncInitCounters(): array
{
    return [
        'ack_success_count' => 0,
        'ack_retry_count' => 0,
        'ack_rejected_count' => 0,
        'ack_failed_auth_count' => 0,
        'ack_latency_ms' => null,
    ];
}

function syncCounterInc(array &$counters, string $name): void
{
    if (!array_key_exists($name, $counters) || !is_numeric($counters[$name])) {
        $counters[$name] = 0;
    }
    $counters[$name] = (int)$counters[$name] + 1;
}
?>
