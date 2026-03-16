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

try {
    $message = syncReadMessage();
    syncValidateEnvelope($message, 'Event');

    $claim = syncInboxClaim($conn, $endpointName, $message);
    if ($claim['duplicate'] === true) {
        syncCounterAdd($counters, 'duplicate');
        syncLogResult($message, $endpointName, 'duplicate', 200, $counters, null);
        syncRespond(200, ['ok' => true, 'duplicate' => true]);
    }

    $payload = $message['payload'];
    $actionType = (string)$message['actionType'];

    $gameComId = syncToNullableInt($payload['GameComId'] ?? $payload['gameComId'] ?? null);
    if ($gameComId === null) {
        throw new SyncFunctionalException('missing payload.GameComId', 422);
    }

    $match = syncResolveMatch($conn, $gameComId);
    if ($match === null) {
        throw new SyncFunctionalException('unknown GameComId', 422);
    }

    $matchIdRef = (string)$match['matchIdRef'];
    $eventId = syncToNullableInt($payload['EventComId'] ?? $payload['eventId'] ?? $payload['event_id'] ?? $message['aggregateId'] ?? null);

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
    } else {
        $teamId = syncToInt($payload['TeamID'] ?? $payload['teamId'] ?? $payload['equipe_event_id'] ?? 0);
        $playerId = syncToInt($payload['PlayerComID'] ?? $payload['playerId'] ?? $payload['joueur_event_ref'] ?? 0);
        $code = syncToInt($payload['code'] ?? $payload['Code'] ?? 0);
        $subcode = syncToInt($payload['souscode'] ?? $payload['subcode'] ?? $payload['Subcode'] ?? 0);
        $chrono = syncToInt($payload['chrono'] ?? 0);
        $noSequence = syncToInt($payload['noSequence'] ?? 0);

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
            $eventId = mysqli_insert_id($conn);
            mysqli_stmt_close($stmtInsert);
        }
    }

    syncInboxMark($conn, (int)$claim['inboxId'], 'processed', 200, null);
    syncCounterAdd($counters, 'success');
    syncLogResult($message, $endpointName, 'processed', 200, $counters, null);

    syncRespond(200, ['ok' => true, 'duplicate' => false, 'eventId' => $eventId]);
} catch (SyncFunctionalException $e) {
    if (isset($claim) && isset($claim['inboxId'])) {
        syncInboxMark($conn, (int)$claim['inboxId'], 'rejected', $e->httpCode(), $e->getMessage());
    }

    syncCounterAdd($counters, 'failure');
    syncLogResult($message, $endpointName, 'rejected', $e->httpCode(), $counters, $e->getMessage());
    syncRespond($e->httpCode(), ['ok' => false, 'error' => $e->getMessage()]);
} catch (Throwable $e) {
    if (isset($claim) && isset($claim['inboxId'])) {
        try {
            syncInboxMark($conn, (int)$claim['inboxId'], 'failed', 500, 'technical error');
        } catch (Throwable $inner) {
            error_log('[sync_inbound] failed to mark inbox as failed: ' . $inner->getMessage());
        }
    }

    syncCounterAdd($counters, 'failure');
    syncLogResult($message, $endpointName, 'failed', 500, $counters, 'technical error');
    error_log('[sync_inbound] event technical error: ' . $e->getMessage());

    syncRespond(500, ['ok' => false, 'error' => 'technical error']);
}
?>
