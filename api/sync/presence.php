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

try {
    $message = syncReadMessage();
    syncValidateEnvelope($message, 'Presence');

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

    $joueurId = syncToNullableInt($payload['joueurId'] ?? $payload['playerId'] ?? $message['aggregateId'] ?? null);
    if ($joueurId === null) {
        throw new SyncFunctionalException('missing payload.joueurId', 422);
    }

    if ($actionType === 'deleted') {
        $stmtDelete = mysqli_prepare($conn, 'DELETE FROM Presences WHERE matchId = ? AND joueurId = ?');
        if (!$stmtDelete) {
            throw new SyncTechnicalException('prepare presence delete failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmtDelete, 'ii', $gameComId, $joueurId);
        mysqli_stmt_execute($stmtDelete);
        mysqli_stmt_close($stmtDelete);
    } else {
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
        mysqli_stmt_store_result($stmtExisting);
        $exists = mysqli_stmt_num_rows($stmtExisting) > 0;
        mysqli_stmt_free_result($stmtExisting);
        mysqli_stmt_close($stmtExisting);

        if ($exists) {
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
            mysqli_stmt_close($stmtInsert);
        }
    }

    syncInboxMark($conn, (int)$claim['inboxId'], 'processed', 200, null);
    syncCounterAdd($counters, 'success');
    syncLogResult($message, $endpointName, 'processed', 200, $counters, null);

    syncRespond(200, ['ok' => true, 'duplicate' => false]);
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
    error_log('[sync_inbound] presence technical error: ' . $e->getMessage());

    syncRespond(500, ['ok' => false, 'error' => 'technical error']);
}
?>
