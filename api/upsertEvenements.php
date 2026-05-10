<?php

declare(strict_types=1);

ini_set('display_errors', '0');

$upsertEvenementsNiveauBufferInitial = ob_get_level();
ob_start();

include_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'syncstatsconfig.php');
require '../scriptsphp/calculeMatch2.php';
require '../scriptsphp/defenvvar.php';
require_once __DIR__ . '/lib/upsert_evenements_rules.php';

if (ob_get_level() > $upsertEvenementsNiveauBufferInitial) {
    $upsertEvenementsSortieInitiale = ob_get_clean();
    if ($upsertEvenementsSortieInitiale !== '') {
        error_log('[upsertEvenements] output parasite supprime avant headers JSON | bytes=' . strlen($upsertEvenementsSortieInitiale));
    }
}

header('Content-Type: application/json; charset=utf-8');

const TABLE_IDEMPOTENCE_EVENEMENTS = 'sync_evenement_idempotence';

function upsertEvenementsTrouverMatchId(mysqli $conn, string $gameStringID): int
{
    $stmtMatch = mysqli_prepare($conn, 'SELECT match_id FROM TableMatch WHERE matchIdRef = ? LIMIT 1');
    if (!$stmtMatch) {
        return 0;
    }

    mysqli_stmt_bind_param($stmtMatch, 's', $gameStringID);
    mysqli_stmt_execute($stmtMatch);
    mysqli_stmt_bind_result($stmtMatch, $matchId);
    mysqli_stmt_fetch($stmtMatch);
    mysqli_stmt_close($stmtMatch);

    return (int)($matchId ?? 0);
}

function upsertEvenementsPremiereValeurNonVide(array $sources, array $cles): ?string
{
    foreach ($sources as $source) {
        if (!is_array($source)) {
            continue;
        }

        foreach ($cles as $cle) {
            if (!array_key_exists($cle, $source)) {
                continue;
            }

            $valeur = $source[$cle];
            if ($valeur === null || is_array($valeur) || is_object($valeur)) {
                continue;
            }

            $texte = trim((string)$valeur);
            if ($texte !== '') {
                return $texte;
            }
        }
    }

    return null;
}

function upsertEvenementsExtraireCleIdempotence(array $evenement, array $contexteRequete): array
{
    $identifiantTelephone = upsertEvenementsPremiereValeurNonVide(
        array($evenement, $contexteRequete),
        array('telID', 'TelID', 'telephoneId', 'TelephoneId', 'phoneId', 'deviceId', 'appareilId', 'AppareilID', 'username')
    );

    $identifiantEvenementLocal = upsertEvenementsPremiereValeurNonVide(
        array($evenement),
        array('id', 'eventLocalId', 'EventLocalId', 'eventLocId', 'EventLocId')
    );

    $identifiantInstance = upsertEvenementsPremiereValeurNonVide(
        array($evenement, $contexteRequete),
        array('deviceInstanceId', 'DeviceInstanceId', 'installationId', 'InstallationId', 'dbInstanceId', 'DbInstanceId')
    );

    if ($identifiantTelephone === null || $identifiantEvenementLocal === null || $identifiantInstance === null) {
        return array(
            'active' => false,
            'telephoneId' => null,
            'eventLocalId' => null,
            'instanceId' => null,
        );
    }

    return array(
        'active' => true,
        'telephoneId' => $identifiantTelephone,
        'eventLocalId' => $identifiantEvenementLocal,
        'instanceId' => $identifiantInstance,
    );
}

function upsertEvenementsInscrireIdempotence(mysqli $conn, string $telephoneId, string $eventLocalId, string $instanceId): ?bool
{
    $stmt = mysqli_prepare(
        $conn,
        'INSERT IGNORE INTO ' . TABLE_IDEMPOTENCE_EVENEMENTS . '
        (telephone_id, event_local_id, instance_id, createdAt, updatedAt)
        VALUES (?, ?, ?, NOW(), NOW())'
    );

    if (!$stmt) {
        error_log('[upsertEvenements] prepare idempotence insert impossible: ' . mysqli_error($conn));
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'sss', $telephoneId, $eventLocalId, $instanceId);
    $ok = mysqli_stmt_execute($stmt);
    $affectees = $ok ? mysqli_stmt_affected_rows($stmt) : -1;
    mysqli_stmt_close($stmt);

    if (!$ok) {
        error_log('[upsertEvenements] execute idempotence insert impossible: ' . mysqli_error($conn));
        return null;
    }

    return $affectees > 0;
}

function upsertEvenementsSupprimerIdempotence(mysqli $conn, string $telephoneId, string $eventLocalId, string $instanceId): void
{
    $stmt = mysqli_prepare(
        $conn,
        'DELETE FROM ' . TABLE_IDEMPOTENCE_EVENEMENTS . ' WHERE telephone_id = ? AND event_local_id = ? AND instance_id = ?'
    );
    if (!$stmt) {
        return;
    }

    mysqli_stmt_bind_param($stmt, 'sss', $telephoneId, $eventLocalId, $instanceId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function upsertEvenementsAssocierEventComIdIdempotence(mysqli $conn, string $telephoneId, string $eventLocalId, string $instanceId, int $eventComId): void
{
    if ($eventComId <= 0) {
        return;
    }

    $stmt = mysqli_prepare(
        $conn,
        'UPDATE ' . TABLE_IDEMPOTENCE_EVENEMENTS . '
         SET event_com_id = ?, updatedAt = NOW()
         WHERE telephone_id = ? AND event_local_id = ? AND instance_id = ?'
    );

    if (!$stmt) {
        return;
    }

    mysqli_stmt_bind_param($stmt, 'isss', $eventComId, $telephoneId, $eventLocalId, $instanceId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function upsertEvenementsTrouverEventComIdParIdempotence(mysqli $conn, string $telephoneId, string $eventLocalId, string $instanceId): int
{
    $stmt = mysqli_prepare(
        $conn,
        'SELECT event_com_id
         FROM ' . TABLE_IDEMPOTENCE_EVENEMENTS . '
         WHERE telephone_id = ? AND event_local_id = ? AND instance_id = ?
         LIMIT 1'
    );

    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param($stmt, 'sss', $telephoneId, $eventLocalId, $instanceId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $eventComId);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return (int)($eventComId ?? 0);
}

function upsertEvenementsTrouverEventComIdParIdempotenceTelephoneEventLocal(mysqli $conn, string $telephoneId, string $eventLocalId): int
{
    $stmt = mysqli_prepare(
        $conn,
        'SELECT event_com_id
         FROM ' . TABLE_IDEMPOTENCE_EVENEMENTS . '
         WHERE telephone_id = ? AND event_local_id = ? AND event_com_id IS NOT NULL
         ORDER BY updatedAt DESC
         LIMIT 1'
    );
    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $telephoneId, $eventLocalId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $eventComId);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return (int)($eventComId ?? 0);
}

function upsertEvenementsIdempotenceExiste(mysqli $conn, string $telephoneId, string $eventLocalId, string $instanceId): bool
{
    $stmt = mysqli_prepare(
        $conn,
        'SELECT 1
         FROM ' . TABLE_IDEMPOTENCE_EVENEMENTS . '
         WHERE telephone_id = ? AND event_local_id = ? AND instance_id = ?
         LIMIT 1'
    );

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'sss', $telephoneId, $eventLocalId, $instanceId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $trouve);
    $aTrouve = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return $aTrouve ? true : false;
}

function upsertEvenementsTrouverEventComIdExistant(
    mysqli $conn,
    string $gameStringID,
    int $teamID,
    int $playerID,
    int $chrono,
    int $code,
    int $subcode,
    int $noSequence
): int {
    $stmtExiste = mysqli_prepare(
        $conn,
        'SELECT event_id
         FROM TableEvenement0
         WHERE match_event_id = ?
           AND equipe_event_id = ?
           AND joueur_event_ref = ?
           AND chrono = ?
           AND code = ?
           AND souscode = ?
           AND noSequence = ?
         ORDER BY event_id DESC
         LIMIT 1'
    );

    if (!$stmtExiste) {
        return 0;
    }

    mysqli_stmt_bind_param($stmtExiste, 'siiiiii', $gameStringID, $teamID, $playerID, $chrono, $code, $subcode, $noSequence);
    mysqli_stmt_execute($stmtExiste);
    mysqli_stmt_bind_result($stmtExiste, $eventComIdExistant);
    $aTrouve = mysqli_stmt_fetch($stmtExiste);
    mysqli_stmt_close($stmtExiste);

    if (!$aTrouve || empty($eventComIdExistant)) {
        return 0;
    }

    return (int)$eventComIdExistant;
}

function upsertEvenementsLoggerAmbiguIgnore(
    int $id,
    string $gameStringID,
    int $teamID,
    int $playerID,
    int $chrono,
    int $code,
    int $subcode,
    int $noSequence,
    int $eventComId,
    array $cleIdempotence,
    ?bool $idempotenceInseree,
    string $source
): void {
    error_log('[EVENT_AMBIGU_IGNORE] ' . json_encode(array(
        'localId' => $id,
        'gameStringID' => $gameStringID,
        'teamID' => $teamID,
        'playerID' => $playerID,
        'chrono' => $chrono,
        'code' => $code,
        'subcode' => $subcode,
        'noSequence' => $noSequence,
        'eventComIdTrouve' => $eventComId > 0 ? $eventComId : null,
        'idempotenceActive' => (bool)($cleIdempotence['active'] ?? false),
        'idempotenceInseree' => $idempotenceInseree,
        'source' => $source
    )));
}

function upsertEvenementsNettoyageProbabilisteIdempotence(mysqli $conn): void
{
    try {
        $tirage = random_int(1, 100);
    } catch (Throwable $e) {
        $tirage = 100;
    }

    if ($tirage !== 1) {
        return;
    }

    $sql = 'DELETE FROM ' . TABLE_IDEMPOTENCE_EVENEMENTS . ' WHERE createdAt < (NOW() - INTERVAL 3 DAY)';
    if (!mysqli_query($conn, $sql)) {
        error_log('[upsertEvenements] nettoyage idempotence impossible: ' . mysqli_error($conn));
    }
}

$heure = $_POST['heure'] ?? null;
$heureServeur = time() * 1000;

$evenements = null;
if (isset($_POST['evenements'])) {
    $evenements = json_decode((string)$_POST['evenements'], true);
}

$syncOK = array();
$noMatchId = 0;

if (is_array($evenements)) {
    upsertEvenementsNettoyageProbabilisteIdempotence($conn);

    foreach ($evenements as $evenement) {
        if (!is_array($evenement)) {
            continue;
        }

        $id = (int)($evenement['id'] ?? 0);
        $etatSync = (int)($evenement['etatSync'] ?? 0);

        if (isset($heure) && isset($evenement['chrono']) && is_numeric($evenement['chrono'])) {
            $evenement['chrono'] = (int)$evenement['chrono'] + $heureServeur - (int)$heure;
        }

        $validationChrono = upsertEvenementsValidationChrono($evenement);
        if ($validationChrono['ok'] !== true) {
            $syncOK[] = array(
                'id' => $id,
                'EventComId' => null,
                'etatSync' => $etatSync,
                'ok' => false,
                'error' => 'validation_echec',
                'reason' => $validationChrono['raison']
            );
            continue;
        }

        if ($etatSync === 10) {
            $eventComIdDel = (int)($evenement['EventComId'] ?? 0);
            if ($eventComIdDel <= 0) {
                $syncOK[] = array(
                    'id' => $id,
                    'EventComId' => null,
                    'etatSync' => 10,
                    'ok' => false,
                    'error' => 'delete_event_com_id_invalide'
                );
                continue;
            }

            $stmtDel = mysqli_prepare($conn, 'DELETE FROM TableEvenement0 WHERE event_id = ?');
            if (!$stmtDel) {
                $syncOK[] = array(
                    'id' => $id,
                    'EventComId' => $eventComIdDel,
                    'etatSync' => 10,
                    'ok' => false,
                    'error' => 'delete_prepare_failed'
                );
                continue;
            }

            mysqli_stmt_bind_param($stmtDel, 'i', $eventComIdDel);
            $deleteOK = mysqli_stmt_execute($stmtDel);
            $deleteAffected = mysqli_stmt_affected_rows($stmtDel);
            mysqli_stmt_close($stmtDel);

            $syncOK[] = array(
                'id' => $id,
                'EventComId' => $eventComIdDel,
                'etatSync' => 10,
                'ok' => $deleteOK ? true : false,
                'deleted' => $deleteAffected > 0
            );
            continue;
        }

        $gameStringID = (string)($evenement['GameStringID'] ?? '');
        $teamID = (int)($evenement['TeamID'] ?? 0);
        $playerID = (int)($evenement['PlayerComID'] ?? 0);
        $chrono = (int)($evenement['chrono'] ?? 0);
        $eventTypeID = (int)($evenement['EventTypeID'] ?? 0);
        $eventTypeDetailID = (int)($evenement['EventTypeDetailID'] ?? $evenement['eventTypeDetailId'] ?? $evenement['eventTypeDetailID'] ?? 0);
        $noSequence = (int)($evenement['noSequence'] ?? 0);

        $code = null;
        $subcode = null;

        if ($eventTypeDetailID > 0) {
            $stmt = mysqli_prepare($conn, 'SELECT Code, Subcode FROM EventTypeDetail WHERE EventTypeDetailId = ? LIMIT 1');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $eventTypeDetailID);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $detailCode, $detailSubcode);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                if ($detailCode !== null) {
                    $code = (int)$detailCode;
                    $subcode = ($detailSubcode === null) ? 0 : (int)$detailSubcode;
                }
            }
        }

        if ($code === null && isset($evenement['code']) && $evenement['code'] !== '') {
            $code = (int)$evenement['code'];
        }

        if ($subcode === null) {
            foreach (array('souscode', 'subcode', 'subCode', 'Subcode', 'sc') as $k) {
                if (isset($evenement[$k]) && $evenement[$k] !== '' && $evenement[$k] !== null) {
                    $subcode = (int)$evenement[$k];
                    break;
                }
            }
        }

        if ($code === null || $subcode === null) {
            $stmt = mysqli_prepare($conn, 'SELECT Code, Subcode FROM EventType WHERE EventTypeId = ? LIMIT 1');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $eventTypeID);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $typeCode, $typeSubcode);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);

                if ($code === null) {
                    $code = ($typeCode === null) ? 0 : (int)$typeCode;
                }
                if ($subcode === null) {
                    $subcode = ($typeSubcode === null) ? 0 : (int)$typeSubcode;
                }
            }
        }

        $eventComIdValue = $evenement['EventComId'] ?? null;
        $decisionCreationLocale = upsertEvenementsDecisionCreationLocale($evenement);
        $cleIdempotence = upsertEvenementsExtraireCleIdempotence($evenement, $_POST);

        if ($decisionCreationLocale['eventComIdVide'] === true) {
            $idempotenceInseree = null;
            if ($cleIdempotence['active'] === true) {
                $idempotenceInseree = upsertEvenementsInscrireIdempotence(
                    $conn,
                    (string)$cleIdempotence['telephoneId'],
                    (string)$cleIdempotence['eventLocalId'],
                    (string)$cleIdempotence['instanceId']
                );

                if ($idempotenceInseree === false) {
                    $telephoneIdIdem = (string)$cleIdempotence['telephoneId'];
                    $eventLocalIdIdem = (string)$cleIdempotence['eventLocalId'];
                    $instanceIdIdem = (string)$cleIdempotence['instanceId'];

                    $eventComId = upsertEvenementsTrouverEventComIdParIdempotence(
                        $conn,
                        $telephoneIdIdem,
                        $eventLocalIdIdem,
                        $instanceIdIdem
                    );

                    if ($eventComId > 0) {
                        $syncOK[] = array('id' => $id, 'EventComId' => $eventComId, 'etatSync' => 12, 'ok' => true, 'action' => 'reused');
                        continue;
                    }

                    $idempotenceExiste = upsertEvenementsIdempotenceExiste($conn, $telephoneIdIdem, $eventLocalIdIdem, $instanceIdIdem);
                    if ($idempotenceExiste === true) {
                        $eventComId = upsertEvenementsTrouverEventComIdParIdempotenceTelephoneEventLocal(
                            $conn,
                            $telephoneIdIdem,
                            $eventLocalIdIdem
                        );
                        if ($eventComId > 0) {
                            upsertEvenementsAssocierEventComIdIdempotence(
                                $conn,
                                $telephoneIdIdem,
                                $eventLocalIdIdem,
                                $instanceIdIdem,
                                $eventComId
                            );
                            error_log('[IDEMPOTENCE_INCOMPLETE] ' . json_encode(array(
                                'telephoneId' => $telephoneIdIdem,
                                'eventLocalId' => $eventLocalIdIdem,
                                'instanceId' => $instanceIdIdem,
                                'eventComId' => $eventComId,
                                'reason' => 'repaired_from_same_phone_event_local'
                            )));
                            $syncOK[] = array('id' => $id, 'EventComId' => $eventComId, 'etatSync' => 12, 'ok' => true, 'action' => 'reused');
                            continue;
                        }

                        error_log('[IDEMPOTENCE_INCOMPLETE] ' . json_encode(array(
                            'telephoneId' => $telephoneIdIdem,
                            'eventLocalId' => $eventLocalIdIdem,
                            'instanceId' => $instanceIdIdem,
                            'eventComId' => null,
                            'reason' => 'mapping_missing_after_insert_ignore'
                        )));
                        error_log('[IDEMPOTENCE_ERROR] ' . json_encode(array(
                            'telephoneId' => $telephoneIdIdem,
                            'eventLocalId' => $eventLocalIdIdem,
                            'instanceId' => $instanceIdIdem,
                            'eventComId' => null,
                            'reason' => 'missing_mapping'
                        )));
                        $syncOK[] = array('id' => $id, 'EventComId' => null, 'etatSync' => $etatSync, 'ok' => false, 'error' => 'idempotence_incomplete');
                        continue;
                    }

                    error_log('[IDEMPOTENCE_ERROR] ' . json_encode(array(
                        'telephoneId' => $telephoneIdIdem,
                        'eventLocalId' => $eventLocalIdIdem,
                        'instanceId' => $instanceIdIdem,
                        'eventComId' => null,
                        'reason' => 'key_not_found_after_insert_ignore'
                    )));
                    $syncOK[] = array('id' => $id, 'EventComId' => null, 'etatSync' => $etatSync, 'ok' => false, 'error' => 'idempotence_incomplete');
                    continue;
                }
            }

            if ($idempotenceInseree === null) {
                if ($cleIdempotence['active'] === true) {
                    error_log('[IDEMPOTENCE_ERROR] ' . json_encode(array(
                        'telephoneId' => (string)$cleIdempotence['telephoneId'],
                        'eventLocalId' => (string)$cleIdempotence['eventLocalId'],
                        'instanceId' => (string)$cleIdempotence['instanceId'],
                        'eventComId' => null,
                        'reason' => 'idempotence_insert_unknown_state'
                    )));
                    $syncOK[] = array('id' => $id, 'EventComId' => null, 'etatSync' => $etatSync, 'ok' => false, 'error' => 'idempotence_incomplete');
                    continue;
                }

                upsertEvenementsLoggerAmbiguIgnore(
                    $id,
                    $gameStringID,
                    $teamID,
                    $playerID,
                    $chrono,
                    $code,
                    $subcode,
                    $noSequence,
                    0,
                    $cleIdempotence,
                    $idempotenceInseree,
                    'idempotence_absente_no_fallback'
                );
                $syncOK[] = array('id' => $id, 'EventComId' => null, 'etatSync' => $etatSync, 'ok' => false, 'error' => 'ambiguous_event_match');
                continue;
            }

            $stmtInsert = mysqli_prepare(
                $conn,
                'INSERT INTO TableEvenement0
                 (match_event_id, equipe_event_id, joueur_event_ref, chrono, code, souscode, noSequence)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            if (!$stmtInsert) {
                $syncOK[] = array('id' => $id, 'EventComId' => null, 'etatSync' => 3, 'ok' => false, 'error' => 'insert_prepare_failed');
                continue;
            }

            mysqli_stmt_bind_param($stmtInsert, 'siiiiii', $gameStringID, $teamID, $playerID, $chrono, $code, $subcode, $noSequence);
            $insertOK = mysqli_stmt_execute($stmtInsert);

            if (!$insertOK) {
                $insertErr = mysqli_stmt_error($stmtInsert);
                mysqli_stmt_close($stmtInsert);
                if ($cleIdempotence['active'] === true && $idempotenceInseree === true) {
                    upsertEvenementsSupprimerIdempotence(
                        $conn,
                        (string)$cleIdempotence['telephoneId'],
                        (string)$cleIdempotence['eventLocalId'],
                        (string)$cleIdempotence['instanceId']
                    );
                }
                $syncOK[] = array('id' => $id, 'EventComId' => null, 'etatSync' => 3, 'ok' => false, 'error' => 'insert_failed', 'detail' => $insertErr);
                continue;
            }

            $eventComId = (int)mysqli_insert_id($conn);
            mysqli_stmt_close($stmtInsert);

            if ($eventComId <= 0) {
                if ($cleIdempotence['active'] === true && $idempotenceInseree === true) {
                    upsertEvenementsSupprimerIdempotence(
                        $conn,
                        (string)$cleIdempotence['telephoneId'],
                        (string)$cleIdempotence['eventLocalId'],
                        (string)$cleIdempotence['instanceId']
                    );
                }
                $syncOK[] = array('id' => $id, 'EventComId' => null, 'etatSync' => 3, 'ok' => false, 'error' => 'insert_no_event_com_id');
                continue;
            }

            if ($cleIdempotence['active'] === true) {
                upsertEvenementsAssocierEventComIdIdempotence(
                    $conn,
                    (string)$cleIdempotence['telephoneId'],
                    (string)$cleIdempotence['eventLocalId'],
                    (string)$cleIdempotence['instanceId'],
                    $eventComId
                );

                $eventComIdVerif = upsertEvenementsTrouverEventComIdParIdempotence(
                    $conn,
                    (string)$cleIdempotence['telephoneId'],
                    (string)$cleIdempotence['eventLocalId'],
                    (string)$cleIdempotence['instanceId']
                );
                if ($eventComIdVerif <= 0) {
                    error_log('[IDEMPOTENCE_ERROR] ' . json_encode(array(
                        'telephoneId' => (string)$cleIdempotence['telephoneId'],
                        'eventLocalId' => (string)$cleIdempotence['eventLocalId'],
                        'instanceId' => (string)$cleIdempotence['instanceId'],
                        'eventComId' => null,
                        'reason' => 'mapping_null_after_association'
                    )));
                }
            }

            $matchId = upsertEvenementsTrouverMatchId($conn, $gameStringID);
            if ($matchId > 0) {
                $noMatchId = $matchId;
            }

            $syncOK[] = array('id' => $id, 'EventComId' => $eventComId, 'etatSync' => 12, 'ok' => true, 'action' => 'created');
            continue;
        }

        $eventComId = (int)$eventComIdValue;
        if ($eventComId <= 0) {
            $syncOK[] = array('id' => $id, 'EventComId' => null, 'etatSync' => 3, 'ok' => false, 'error' => 'event_com_id_invalide');
            continue;
        }

        $stmtUpsert = mysqli_prepare(
            $conn,
            'INSERT INTO TableEvenement0
            (event_id, match_event_id, equipe_event_id, joueur_event_ref, chrono, code, souscode, noSequence)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                match_event_id = VALUES(match_event_id),
                equipe_event_id = VALUES(equipe_event_id),
                joueur_event_ref = VALUES(joueur_event_ref),
                chrono = VALUES(chrono),
                code = VALUES(code),
                souscode = VALUES(souscode),
                noSequence = VALUES(noSequence)'
        );

        if (!$stmtUpsert) {
            $syncOK[] = array('id' => $id, 'EventComId' => $eventComId, 'etatSync' => 3, 'ok' => false, 'error' => 'upsert_prepare_failed');
            continue;
        }

        mysqli_stmt_bind_param($stmtUpsert, 'isiiiiii', $eventComId, $gameStringID, $teamID, $playerID, $chrono, $code, $subcode, $noSequence);
        $upsertOK = mysqli_stmt_execute($stmtUpsert);
        if (!$upsertOK) {
            $upsertErr = mysqli_stmt_error($stmtUpsert);
            mysqli_stmt_close($stmtUpsert);
            $syncOK[] = array('id' => $id, 'EventComId' => $eventComId, 'etatSync' => 3, 'ok' => false, 'error' => 'upsert_failed', 'detail' => $upsertErr);
            continue;
        }
        mysqli_stmt_close($stmtUpsert);

        $matchId = upsertEvenementsTrouverMatchId($conn, $gameStringID);
        if ($matchId > 0) {
            $noMatchId = $matchId;
        }

        $syncOK[] = array('id' => $id, 'EventComId' => $eventComId, 'etatSync' => 12, 'ok' => true, 'action' => 'upserted');
    }
}

if ($noMatchId !== 0) {
    if ($workEnv === 'production') {
        $url = 'https://syncstats.com/scriptsphp/calculeUnMatch.php';
    } else {
        $url = 'http://vieuxsite.sm.syncstats.ca/scriptsphp/calculeUnMatch.php';
    }

    $postData = http_build_query(array('noMatchId' => (int)$noMatchId));
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($postData)
        ),
    ));

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($result === false || $httpCode !== 200) {
        error_log('calculeUnMatch CURL ERROR | match=' . $noMatchId . ' | http=' . $httpCode . ' | err=' . $curlErr, 0);
    } elseif (trim((string)$result) === '') {
        error_log('calculeUnMatch reponse vide | match=' . $noMatchId, 0);
    }
}

echo json_encode($syncOK);
