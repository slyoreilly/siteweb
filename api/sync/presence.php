<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

syncValidateToken();

if (!($conn instanceof mysqli)) {
    syncRespond(500, ['ok' => false, 'error' => 'database unavailable']);
}

$endpointName = 'presence';
$counters = syncInitCounters();
$message = [];
$claim = null;
$businessProcessed = false;

try {
    $message = syncReadMessage();
    syncValidateEnvelope($message, 'Presence');

    $client = syncAckClient();
    $claim = syncInboxClaim($conn, $endpointName, $message);
    $actionType = (string)$message['actionType'];

    if ($claim['duplicate'] === true) {
        if (syncNeedAck($actionType) && !empty($claim['upstream_id']) && in_array((string)$claim['ack_status'], ['pending', 'retrying'], true)) {
            if (!syncAcquireRowLock($conn, (int)$claim['inboxId'])) {
                syncRespond(200, ['ok' => true, 'duplicate' => true, 'ack' => 'locked']);
            }

            try {
                $sourceEntityId = $claim['source_entity_id'] ?? syncSourceEntityIdFromMessage($message);
                $ackPayload = [
                    'entity' => 'Presence',
                    'sourceEntityId' => $sourceEntityId,
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

    $joueurId = syncToNullableInt($payload['joueurId'] ?? $payload['playerId'] ?? $message['aggregateId'] ?? null);
    if ($joueurId === null) {
        throw new SyncFunctionalException('missing payload.joueurId', 422);
    }

    $upstreamId = null;

    if ($actionType === 'deleted') {
        $stmtDelete = mysqli_prepare($conn, 'DELETE FROM Presences WHERE matchId = ? AND joueurId = ?');
        if (!$stmtDelete) {
            throw new SyncTechnicalException('prepare presence delete failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmtDelete, 'ii', $gameComId, $joueurId);
        mysqli_stmt_execute($stmtDelete);
        mysqli_stmt_close($stmtDelete);

        syncInboxMarkStatus($conn, (int)$claim['inboxId'], 'done', 200, null);
        syncInboxMarkAckSuccess($conn, (int)$claim['inboxId'], 0, 200);
        syncRespond(200, ['ok' => true, 'ack' => 'not_required']);
    }

    $positionId = syncToNullableInt($payload['positionId'] ?? null);
    $domVis = syncToNullableInt($payload['domVis'] ?? null);
    $statut = syncToNullableInt($payload['statut'] ?? null);
    $updatedBy = isset($payload['updatedBy']) && (string)$payload['updatedBy'] !== '' ? (string)$payload['updatedBy'] : 'sync_inbound';
    $updatedAt = syncToDateTimeFromIsoOrMs($payload['updatedAt'] ?? $message['createdAt'] ?? null);
    $numero = isset($payload['numero']) ? (string)$payload['numero'] : null;

    if ($domVis === null) {
        throw new SyncFunctionalException('missing payload.domVis', 422);
    }
    if ($statut === null) {
        throw new SyncFunctionalException('missing payload.statut', 422);
    }

    $hasNumeroColumn = false;
    $retNumero = mysqli_query($conn, "SHOW COLUMNS FROM Presences LIKE 'numero'");
    if ($retNumero && mysqli_num_rows($retNumero) > 0) {
        $hasNumeroColumn = true;
    }

    $stmtExisting = mysqli_prepare($conn, 'SELECT presenceId FROM Presences WHERE matchId = ? AND joueurId = ? LIMIT 1');
    if (!$stmtExisting) {
        throw new SyncTechnicalException('prepare presence select failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmtExisting, 'ii', $gameComId, $joueurId);
    mysqli_stmt_execute($stmtExisting);
    $resExisting = mysqli_stmt_get_result($stmtExisting);
    $existingRow = $resExisting ? mysqli_fetch_assoc($resExisting) : null;
    mysqli_stmt_close($stmtExisting);

    if ($existingRow) {
        $upstreamId = (string)$existingRow['presenceId'];

        if ($hasNumeroColumn) {
            $stmtUpdate = mysqli_prepare(
                $conn,
                'UPDATE Presences SET positionId = ?, numero = ?, domVis = ?, statut = ?, updatedBy = ?, updatedAt = ? WHERE matchId = ? AND joueurId = ?'
            );
            if (!$stmtUpdate) {
                throw new SyncTechnicalException('prepare presence update failed: ' . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmtUpdate, 'isiissii', $positionId, $numero, $domVis, $statut, $updatedBy, $updatedAt, $gameComId, $joueurId);
        } else {
            $stmtUpdate = mysqli_prepare(
                $conn,
                'UPDATE Presences SET positionId = ?, domVis = ?, statut = ?, updatedBy = ?, updatedAt = ? WHERE matchId = ? AND joueurId = ?'
            );
            if (!$stmtUpdate) {
                throw new SyncTechnicalException('prepare presence update failed: ' . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmtUpdate, 'iiissii', $positionId, $domVis, $statut, $updatedBy, $updatedAt, $gameComId, $joueurId);
        }

        if (!mysqli_stmt_execute($stmtUpdate)) {
            $err = mysqli_stmt_error($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);
            throw new SyncTechnicalException('presence update failed: ' . $err);
        }
        mysqli_stmt_close($stmtUpdate);
    } else {
        if ($hasNumeroColumn) {
            $stmtInsert = mysqli_prepare(
                $conn,
                'INSERT INTO Presences (matchId, joueurId, positionId, numero, domVis, statut, updatedBy, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            if (!$stmtInsert) {
                throw new SyncTechnicalException('prepare presence insert failed: ' . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmtInsert, 'iiisiiss', $gameComId, $joueurId, $positionId, $numero, $domVis, $statut, $updatedBy, $updatedAt);
        } else {
            $stmtInsert = mysqli_prepare(
                $conn,
                'INSERT INTO Presences (matchId, joueurId, positionId, domVis, statut, updatedBy, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            if (!$stmtInsert) {
                throw new SyncTechnicalException('prepare presence insert failed: ' . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmtInsert, 'iiiiiss', $gameComId, $joueurId, $positionId, $domVis, $statut, $updatedBy, $updatedAt);
        }

        if (!mysqli_stmt_execute($stmtInsert)) {
            $err = mysqli_stmt_error($stmtInsert);
            mysqli_stmt_close($stmtInsert);
            throw new SyncTechnicalException('presence insert failed: ' . $err);
        }

        $upstreamId = (string)mysqli_insert_id($conn);
        mysqli_stmt_close($stmtInsert);
    }

    $businessProcessed = true;
    $sourceEntityId = syncSourceEntityIdFromMessage($message);
    syncInboxSetAckContext($conn, (int)$claim['inboxId'], $sourceEntityId, $upstreamId);

    $ackPayload = [
        'entity' => 'Presence',
        'sourceEntityId' => $sourceEntityId,
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
    error_log('[sync_inbound] presence technical error: ' . $e->getMessage());
    syncRespond(500, ['ok' => false, 'error' => 'technical error']);
}
?>
