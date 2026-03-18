<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (!($conn instanceof mysqli)) {
    fwrite(STDERR, "DB unavailable\n");
    exit(1);
}

$loop = in_array('--loop', $argv ?? [], true);
$sleepSeconds = 1;
$batchSize = 50;
$client = syncAckClient();

function processDueAckBatch(mysqli $conn, SyncAckClient $client, int $batchSize): int
{
    $sql = 'SELECT syncInboxId, endpoint, dedupeKey, source_entity_id, upstream_id, ack_attempts
            FROM sync_inbox
            WHERE ack_status = "retrying"
              AND ack_next_attempt_at IS NOT NULL
              AND ack_next_attempt_at <= NOW()
              AND upstream_id IS NOT NULL
            ORDER BY ack_next_attempt_at ASC
            LIMIT ?';

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log('[sync_ack_worker] prepare select failed: ' . mysqli_error($conn));
        return 0;
    }
    mysqli_stmt_bind_param($stmt, 'i', $batchSize);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $rows = [];
    while ($res && ($row = mysqli_fetch_assoc($res))) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);

    $processed = 0;

    foreach ($rows as $row) {
        $inboxId = (int)$row['syncInboxId'];

        if (!syncAcquireRowLock($conn, $inboxId)) {
            continue;
        }

        try {
            $endpoint = (string)$row['endpoint'];
            $entity = $endpoint === 'event' ? 'Event' : 'Presence';
            $sourceEntityId = syncToNullableInt($row['source_entity_id'] ?? null);
            $upstreamId = (string)$row['upstream_id'];
            $dedupeKey = (string)$row['dedupeKey'];

            $ackPayload = [
                'entity' => $entity,
                'sourceEntityId' => $sourceEntityId,
                'dedupeKey' => $dedupeKey,
                'upstreamId' => $upstreamId,
            ];

            $ack = $client->sendOnce($ackPayload);
            $attempts = syncToInt($row['ack_attempts'] ?? 0) + 1;

            if ($ack['result'] === 'success') {
                syncInboxMarkAckSuccess($conn, $inboxId, $attempts, (int)$ack['http_code']);
                error_log('[sync_ack_worker] ack_success ' . json_encode(['inboxId' => $inboxId, 'dedupeKey' => $dedupeKey, 'attempts' => $attempts, 'latency_ms' => $ack['latency_ms']]));
                $processed++;
                continue;
            }

            if ($ack['result'] === 'rejected') {
                syncInboxMarkAckRejected($conn, $inboxId, $attempts, (int)$ack['http_code'], $ack['error']);
                error_log('[sync_ack_worker] ack_rejected ' . json_encode(['inboxId' => $inboxId, 'dedupeKey' => $dedupeKey, 'attempts' => $attempts, 'error' => $ack['error']]));
                $processed++;
                continue;
            }

            if ($ack['result'] === 'failed_auth') {
                syncInboxMarkAckFailedAuth($conn, $inboxId, $attempts, (int)$ack['http_code'], $ack['error']);
                error_log('[sync_ack_worker] ack_failed_auth ' . json_encode(['inboxId' => $inboxId, 'dedupeKey' => $dedupeKey, 'attempts' => $attempts, 'error' => $ack['error']]));
                $processed++;
                continue;
            }

            if ($attempts >= $client->maxAttempts()) {
                syncInboxMarkAckMaxAttempts($conn, $inboxId, $attempts, (int)$ack['http_code'], $ack['error']);
                error_log('[sync_ack_worker] ack_max_attempts ' . json_encode(['inboxId' => $inboxId, 'dedupeKey' => $dedupeKey, 'attempts' => $attempts, 'error' => $ack['error']]));
                $processed++;
                continue;
            }

            $nextAt = date('Y-m-d H:i:s', time() + $client->backoffSecondsForAttempt($attempts));
            syncInboxMarkAckRetry($conn, $inboxId, $attempts, (int)$ack['http_code'], $ack['error'], $nextAt);
            error_log('[sync_ack_worker] ack_retry ' . json_encode(['inboxId' => $inboxId, 'dedupeKey' => $dedupeKey, 'attempts' => $attempts, 'next' => $nextAt, 'error' => $ack['error']]));
            $processed++;
        } catch (Throwable $e) {
            error_log('[sync_ack_worker] error ' . json_encode(['inboxId' => $inboxId, 'error' => $e->getMessage()]));
        } finally {
            syncReleaseRowLock($conn, $inboxId);
        }
    }

    return $processed;
}

do {
    $count = processDueAckBatch($conn, $client, $batchSize);
    if (!$loop) {
        break;
    }
    if ($count === 0) {
        sleep($sleepSeconds);
    }
} while (true);

exit(0);
?>
