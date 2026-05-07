<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

syncValidateToken();

if (!($conn instanceof mysqli)) {
    syncRespond(500, ['ok' => false, 'error' => 'database unavailable']);
}

$endpointName = 'event';
$counters = syncInitCounters();
$message = [];
$claim = null;
$businessProcessed = false;

try {
    $message = syncReadMessage();
    syncValidateEnvelope($message, 'Event');

    $client = syncAckClient();
    $claim = syncInboxClaim($conn, $endpointName, $message);
    $actionType = (string)$message['actionType'];

    $sourceEntityIdFromMessage = syncToNullableInt($message['aggregateId'] ?? null);
    if ($sourceEntityIdFromMessage === null) {
        throw new SyncFunctionalException('missing aggregateId', 422);
    }

    if ($claim['duplicate'] === true) {
        if (syncNeedAck($actionType) && !empty($claim['upstream_id']) && in_array((string)$claim['ack_status'], ['pending', 'retrying'], true)) {
            if (!syncAcquireRowLock($conn, (int)$claim['inboxId'])) {
                syncRespond(200, ['ok' => true, 'duplicate' => true, 'ack' => 'locked']);
            }

            try {
                $sourceEntityId = $sourceEntityIdFromMessage;
                syncInboxSetAckContext($conn, (int)$claim['inboxId'], $sourceEntityId, (string)$claim['upstream_id']);
                $ackPayload = [
                    'entity' => 'Event',
                    'sourceEntityId' => $sourceEntityId,
                    'sourceEventId' => $sourceEntityId,
                    'dedupeKey' => (string)$message['dedupeKey'],
                    'upstreamId' => (string)$claim['upstream_id'],
                ];

                $ack = $client->sendOnce($ackPayload);
                $attempts = (int)$claim['ack_attempts'] + 1;
                $counters['ack_latency_ms'] = $ack['latency_ms'];

                if ($ack['result'] === 'success') {
                    syncInboxMarkAckSuccess($conn, (int)$claim['inboxId'], $attempts, (int)$ack['http_code']);
                    syncCounterInc($counters, 'ack_success_count');
                    syncLogResult($message, $endpointName, 'duplicate_ack_success', 200, $counters, null, $ack);
                    syncRespond(200, ['ok' => true, 'duplicate' => true, 'ack' => 'success']);
                }

                if ($ack['result'] === 'rejected') {
                    syncInboxMarkAckRejected($conn, (int)$claim['inboxId'], $attempts, (int)$ack['http_code'], $ack['error']);
                    syncCounterInc($counters, 'ack_rejected_count');
                    syncLogResult($message, $endpointName, 'duplicate_ack_rejected', 200, $counters, $ack['error'], $ack);
                    syncRespond(200, ['ok' => true, 'duplicate' => true, 'ack' => 'rejected']);
                }

                if ($ack['result'] === 'failed_auth') {
                    syncInboxMarkAckFailedAuth($conn, (int)$claim['inboxId'], $attempts, (int)$ack['http_code'], $ack['error']);
                    syncCounterInc($counters, 'ack_failed_auth_count');
                    syncLogResult($message, $endpointName, 'duplicate_ack_failed_auth', 500, $counters, $ack['error'], $ack);
                    syncRespond(500, ['ok' => false, 'duplicate' => true, 'error' => 'ack failed_auth']);
                }

                if ($attempts >= $client->maxAttempts()) {
                    syncInboxMarkAckMaxAttempts($conn, (int)$claim['inboxId'], $attempts, (int)$ack['http_code'], $ack['error']);
                    syncCounterInc($counters, 'ack_rejected_count');
                    syncLogResult($message, $endpointName, 'duplicate_ack_max_attempts', 500, $counters, $ack['error'], $ack);
                    syncRespond(500, ['ok' => false, 'duplicate' => true, 'error' => 'ack max attempts reached']);
                }

                $nextAt = date('Y-m-d H:i:s', time() + $client->backoffSecondsForAttempt($attempts));
                syncInboxMarkAckRetry($conn, (int)$claim['inboxId'], $attempts, (int)$ack['http_code'], $ack['error'], $nextAt);
                syncCounterInc($counters, 'ack_retry_count');
                syncLogResult($message, $endpointName, 'duplicate_ack_retrying', 500, $counters, $ack['error'], $ack);
                syncRespond(500, ['ok' => false, 'duplicate' => true, 'error' => 'ack technical failure']);
            } finally {
                syncReleaseRowLock($conn, (int)$claim['inboxId']);
            }
        }

        syncRespond(200, ['ok' => true, 'duplicate' => true]);
    }

    $payload = $message['payload'];

    $gameComId = syncToNullableInt($payload['GameComId'] ?? $payload['gameComId'] ?? null);
    if ($gameComId === null) {
        throw new SyncFunctionalException('missing payload.GameComId', 422);
    }

    $match = syncResolveMatch($conn, $gameComId);
    if ($match === null) {
        throw new SyncFunctionalException('unknown GameComId', 422);
    }

    $matchIdRef = (string)$match['matchIdRef'];
    $eventId = syncToNullableInt($payload['EventComId'] ?? null);

    if ($actionType === 'deleted') {
        if ($eventId === null) {
            throw new SyncFunctionalException('missing event id for delete', 422);
        }

        $stmtDelete = mysqli_prepare($conn, 'DELETE FROM TableEvenement0 WHERE event_id = ?');
        if (!$stmtDelete) {
            throw new SyncTechnicalException('prepare event delete failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmtDelete, 'i', $eventId);
        mysqli_stmt_execute($stmtDelete);
        mysqli_stmt_close($stmtDelete);

        syncInboxMarkStatus($conn, (int)$claim['inboxId'], 'done', 200, null);
        syncInboxMarkAckSuccess($conn, (int)$claim['inboxId'], 0, 200);
        syncRespond(200, ['ok' => true, 'ack' => 'not_required']);
    }

    $teamId = syncToInt($payload['TeamID'] ?? $payload['teamId'] ?? $payload['equipe_event_id'] ?? 0);
    $playerId = syncToInt($payload['PlayerComID'] ?? $payload['playerId'] ?? $payload['joueur_event_ref'] ?? 0);
    $code = syncToInt($payload['code'] ?? $payload['Code'] ?? 0);
    $subcode = syncToInt($payload['souscode'] ?? $payload['subcode'] ?? $payload['Subcode'] ?? 0);
    $chrono = syncToInt($payload['chrono'] ?? 0);
    $noSequence = syncToInt($payload['noSequence'] ?? 0);
    $eventTypeId = syncToNullableInt($payload['eventTypeId'] ?? $payload['EventTypeId'] ?? null);
    $parentEventId = syncToNullableInt($payload['parentEventId'] ?? $payload['parentId'] ?? $payload['ParentEventId'] ?? null);
    $isActiveRaw = $payload['isActive'] ?? null;

    $isActive = null;
    if (is_bool($isActiveRaw)) {
        $isActive = $isActiveRaw;
    } elseif (is_numeric($isActiveRaw)) {
        $isActive = ((int)$isActiveRaw) === 1;
    } elseif (is_string($isActiveRaw)) {
        $normalise = strtolower(trim($isActiveRaw));
        if (in_array($normalise, ['1', 'true', 'yes'], true)) {
            $isActive = true;
        } elseif (in_array($normalise, ['0', 'false', 'no'], true)) {
            $isActive = false;
        }
    }

    if ($isActive === false) {
        $code = 15;
    } elseif ($eventTypeId === 2 && $isActive === true) {
        $code = 1;
    }

    if ($eventTypeId === 2 && $parentEventId !== null) {
        $stmtParent = mysqli_prepare($conn, 'SELECT chrono FROM TableEvenement0 WHERE event_id = ? LIMIT 1');
        if (!$stmtParent) {
            throw new SyncTechnicalException('prepare parent chrono failed: ' . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmtParent, 'i', $parentEventId);
        mysqli_stmt_execute($stmtParent);
        $resParent = mysqli_stmt_get_result($stmtParent);
        $rowParent = $resParent ? mysqli_fetch_assoc($resParent) : null;
        mysqli_stmt_close($stmtParent);

        if (is_array($rowParent) && isset($rowParent['chrono']) && is_numeric($rowParent['chrono'])) {
            $chrono = (int)$rowParent['chrono'];
        } else {
            error_log('[sync_inbound] assistance parent chrono introuvable | parentEventId=' . $parentEventId . ' | aggregateId=' . $sourceEntityIdFromMessage);
        }
    }

    $upstreamId = null;
    $eventCreeDepuisSyncInbox = false;

    if ($eventId !== null) {
        $stmtExists = mysqli_prepare($conn, 'SELECT event_id FROM TableEvenement0 WHERE event_id = ? LIMIT 1');
        if (!$stmtExists) {
            throw new SyncTechnicalException('prepare event exists failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmtExists, 'i', $eventId);
        mysqli_stmt_execute($stmtExists);
        mysqli_stmt_store_result($stmtExists);
        $exists = mysqli_stmt_num_rows($stmtExists) > 0;
        mysqli_stmt_free_result($stmtExists);
        mysqli_stmt_close($stmtExists);
    } else {
        $exists = false;
    }

    if ($exists) {
        $upstreamId = (string)$eventId;
        $stmtUpdate = mysqli_prepare(
            $conn,
            'UPDATE TableEvenement0
             SET match_event_id = ?, equipe_event_id = ?, joueur_event_ref = ?, code = ?, souscode = ?, chrono = ?, noSequence = ?
             WHERE event_id = ?'
        );
        if (!$stmtUpdate) {
            throw new SyncTechnicalException('prepare event update failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmtUpdate, 'siiiiiii', $matchIdRef, $teamId, $playerId, $code, $subcode, $chrono, $noSequence, $eventId);
        if (!mysqli_stmt_execute($stmtUpdate)) {
            $err = mysqli_stmt_error($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);
            throw new SyncTechnicalException('event update failed: ' . $err);
        }
        mysqli_stmt_close($stmtUpdate);
    } else {
        $stmtInsert = mysqli_prepare(
            $conn,
            'INSERT INTO TableEvenement0 (match_event_id, equipe_event_id, joueur_event_ref, code, souscode, chrono, noSequence)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmtInsert) {
            throw new SyncTechnicalException('prepare event insert failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmtInsert, 'siiiiii', $matchIdRef, $teamId, $playerId, $code, $subcode, $chrono, $noSequence);
        if (!mysqli_stmt_execute($stmtInsert)) {
            $err = mysqli_stmt_error($stmtInsert);
            mysqli_stmt_close($stmtInsert);
            throw new SyncTechnicalException('event insert failed: ' . $err);
        }
        $eventId = (int)mysqli_insert_id($conn);
        $upstreamId = (string)$eventId;
        $eventCreeDepuisSyncInbox = true;
        mysqli_stmt_close($stmtInsert);
    }

    if ($actionType === 'created' && $eventCreeDepuisSyncInbox) {
        syncRelanceCalculeUnMatch((int)$gameComId);
    }

    $businessProcessed = true;
    $sourceEntityId = $sourceEntityIdFromMessage;
    syncInboxSetAckContext($conn, (int)$claim['inboxId'], $sourceEntityId, $upstreamId);

    $ackPayload = [
        'entity' => 'Event',
        'sourceEntityId' => $sourceEntityId,
        'sourceEventId' => $sourceEntityId,
        'dedupeKey' => (string)$message['dedupeKey'],
        'upstreamId' => $upstreamId,
    ];

    $ack = $client->sendOnce($ackPayload);
    $counters['ack_latency_ms'] = $ack['latency_ms'];
    $attempts = 1;

    if ($ack['result'] === 'success') {
        syncInboxMarkAckSuccess($conn, (int)$claim['inboxId'], $attempts, (int)$ack['http_code']);
        syncCounterInc($counters, 'ack_success_count');
        syncLogResult($message, $endpointName, 'done', 200, $counters, null, $ack);
        syncRespond(200, ['ok' => true, 'upstreamId' => $upstreamId]);
    }

    if ($ack['result'] === 'rejected') {
        syncInboxMarkAckRejected($conn, (int)$claim['inboxId'], $attempts, (int)$ack['http_code'], $ack['error']);
        syncCounterInc($counters, 'ack_rejected_count');
        syncLogResult($message, $endpointName, 'ack_rejected', 422, $counters, $ack['error'], $ack);
        syncRespond(422, ['ok' => false, 'error' => 'ack rejected']);
    }

    if ($ack['result'] === 'failed_auth') {
        syncInboxMarkAckFailedAuth($conn, (int)$claim['inboxId'], $attempts, (int)$ack['http_code'], $ack['error']);
        syncCounterInc($counters, 'ack_failed_auth_count');
        syncLogResult($message, $endpointName, 'ack_failed_auth', 500, $counters, $ack['error'], $ack);
        syncRespond(500, ['ok' => false, 'error' => 'ack failed_auth']);
    }

    $nextAt = date('Y-m-d H:i:s', time() + $client->backoffSecondsForAttempt($attempts));
    syncInboxMarkAckRetry($conn, (int)$claim['inboxId'], $attempts, (int)$ack['http_code'], $ack['error'], $nextAt);
    syncCounterInc($counters, 'ack_retry_count');
    syncLogResult($message, $endpointName, 'ack_retrying', 500, $counters, $ack['error'], $ack);
    syncRespond(500, ['ok' => false, 'error' => 'ack technical failure']);
} catch (SyncFunctionalException $e) {
    if (is_array($claim) && isset($claim['inboxId']) && !$businessProcessed) {
        syncInboxMarkStatus($conn, (int)$claim['inboxId'], 'rejected', $e->httpCode(), $e->getMessage());
    }
    syncLogResult($message, $endpointName, 'rejected', $e->httpCode(), $counters, $e->getMessage());
    syncRespond($e->httpCode(), ['ok' => false, 'error' => $e->getMessage()]);
} catch (Throwable $e) {
    if (is_array($claim) && isset($claim['inboxId']) && !$businessProcessed) {
        syncInboxMarkStatus($conn, (int)$claim['inboxId'], 'failed', 500, 'technical error');
    }
    syncLogResult($message, $endpointName, 'failed', 500, $counters, 'technical error');
    error_log('[sync_inbound] event technical error: ' . $e->getMessage());
    syncRespond(500, ['ok' => false, 'error' => 'technical error']);
}
?>

