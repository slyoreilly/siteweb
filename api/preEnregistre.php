<?php
require '../scriptsphp/defenvvar.php';

session_start();

function toNullableInt($value)
{
    if ($value === null || $value === '') {
        return null;
    }

    if (is_int($value)) {
        return $value;
    }

    if (is_numeric($value)) {
        return intval($value);
    }

    return null;
}

function toNullablePositiveInt($value)
{
    $intValue = toNullableInt($value);
    if ($intValue === null || $intValue <= 0) {
        return null;
    }

    return $intValue;
}

function toNullableString($value)
{
    if ($value === null) {
        return null;
    }

    $stringValue = trim((string)$value);
    if ($stringValue === '') {
        return null;
    }

    return $stringValue;
}

function toSqlValue($conn, $value)
{
    if ($value === null) {
        return 'NULL';
    }

    if (is_int($value) || is_float($value)) {
        return (string)$value;
    }

    return "'" . mysqli_real_escape_string($conn, (string)$value) . "'";
}

function getVideoTableColumns($conn)
{
    $columns = array();
    $ret = mysqli_query($conn, 'SHOW COLUMNS FROM Video');
    if (!$ret) {
        return $columns;
    }

    while ($row = mysqli_fetch_assoc($ret)) {
        if (isset($row['Field'])) {
            $columns[$row['Field']] = true;
        }
    }

    return $columns;
}

$videosRaw = isset($_POST['videos']) ? $_POST['videos'] : '[]';
error_log('preEnregistre: ' . $videosRaw);

$params = json_decode($videosRaw, true);
if (!is_array($params)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Champ videos invalide'));    
    exit;
}

if (isset($params['videos']) && is_array($params['videos'])) {
    $params = $params['videos'];
}

$emplacementInput = isset($_POST['emplacement']) ? trim((string)$_POST['emplacement']) : '';
$defaultEmplacement = 'www.syncstats.com';
if ($emplacementInput !== '') {
    $emplacementTmp = parse_url($emplacementInput);
    if (is_array($emplacementTmp) && isset($emplacementTmp['host'])) {
        $defaultEmplacement = $emplacementTmp['host'] . (isset($emplacementTmp['path']) ? $emplacementTmp['path'] : '');
    } else {
        $defaultEmplacement = preg_replace('/^https?:\/\//i', '', $emplacementInput);
    }
}

$videoColumns = getVideoTableColumns($conn);
$syncOK = array();

for ($a = 0; $a < count($params); $a++) {
    $sourceVideo = $params[$a];
    if (isset($sourceVideo['video']) && is_array($sourceVideo['video'])) {
        $sourceVideo = $sourceVideo['video'];
    }

    if (!is_array($sourceVideo)) {
        continue;
    }

    $cutDocId = isset($sourceVideo['cutDocId']) ? (string)$sourceVideo['cutDocId'] : '';
    if ($cutDocId === '') {
        $cutDocId = isset($sourceVideo['nomFic']) ? (string)$sourceVideo['nomFic'] : (isset($sourceVideo['nomFichier']) ? (string)$sourceVideo['nomFichier'] : '');
    }

    $nomFichier = basename($cutDocId);
    if ($nomFichier === '') {
        continue;
    }

    $camIdInt = toNullableInt(isset($sourceVideo['camID']) ? $sourceVideo['camID'] : (isset($sourceVideo['camId']) ? $sourceVideo['camId'] : null));
    $camIdString = (string)($camIdInt !== null ? $camIdInt : '0');

    $cleValeur = null;
    if (isset($sourceVideo['cleValeur']) && is_array($sourceVideo['cleValeur'])) {
        $cleValeur = $sourceVideo['cleValeur'];
    } else if (isset($sourceVideo['cleValeur']) && is_string($sourceVideo['cleValeur'])) {
        $decodedCleValeur = json_decode($sourceVideo['cleValeur'], true);
        if (is_array($decodedCleValeur)) {
            $cleValeur = $decodedCleValeur;
        }
    }

    $nomMatch = toNullableString(isset($sourceVideo['nomMatch']) ? $sourceVideo['nomMatch'] : (isset($sourceVideo['matchId']) ? $sourceVideo['matchId'] : null));

    $typeValue = null;
    if (is_array($cleValeur) && array_key_exists('eventCode', $cleValeur)) {
        $typeValue = toNullableString($cleValeur['eventCode']);
    }
    if ($typeValue === null) {
        $typeValue = toNullableString(isset($sourceVideo['type']) ? $sourceVideo['type'] : (isset($sourceVideo['code']) ? $sourceVideo['code'] : null));
    }

    $mappedVideo = array(
        'videoId' => toNullablePositiveInt(isset($sourceVideo['videoId']) ? $sourceVideo['videoId'] : null),
        'nomFichier' => $nomFichier,
        'nomMatch' => $nomMatch,
        'camId' => $camIdInt,
        'proprioId' => null,
        'chrono' => toNullableInt(isset($sourceVideo['chrono']) ? $sourceVideo['chrono'] : null),
        'eval' => 0.0,
        'nbEval' => 0,
        'nbVues' => 0,
        'etat' => isset($sourceVideo['etatSync']) ? (string)$sourceVideo['etatSync'] : (isset($sourceVideo['etat']) ? (string)$sourceVideo['etat'] : null),
        'angleOk' => 0,
        'tagPrincipal' => is_array($cleValeur) && array_key_exists('tagPrincipal', $cleValeur) ? toNullableString($cleValeur['tagPrincipal']) : null,
        'autresTags' => is_array($cleValeur) && array_key_exists('autresTags', $cleValeur) ? toNullableString($cleValeur['autresTags']) : null,
        'type' => $typeValue,
        'reference' => toNullablePositiveInt(isset($sourceVideo['reference']) ? $sourceVideo['reference'] : null),
        'emplacement' => isset($sourceVideo['emplacement']) && $sourceVideo['emplacement'] !== '' ? (string)$sourceVideo['emplacement'] : $defaultEmplacement,
        'emplacementArchive' => null,
        'nomThumbnail' => null,
        'nomFic' => $nomFichier,
        'duree' => toNullableInt(isset($sourceVideo['duree']) ? $sourceVideo['duree'] : null),
        'camID' => $camIdString,
        'cv' => isset($sourceVideo['cleValeur']) ? (string)$sourceVideo['cleValeur'] : (isset($sourceVideo['cv']) ? (string)$sourceVideo['cv'] : '')
    );

    $dbData = array();
    foreach ($mappedVideo as $field => $value) {
        if (isset($videoColumns[$field])) {
            $dbData[$field] = $value;
        }
    }

    if (empty($dbData)) {
        continue;
    }

    $existingVideoId = null;
    if (isset($mappedVideo['videoId']) && $mappedVideo['videoId'] !== null) {
        $existingVideoId = $mappedVideo['videoId'];
    } else {
        $qExisting = "SELECT videoId FROM Video WHERE nomFichier='" . mysqli_real_escape_string($conn, $mappedVideo['nomFichier']) . "'";
        if ($mappedVideo['camId'] !== null) {
            $qExisting .= " AND camId='" . intval($mappedVideo['camId']) . "'";
        }
        if ($mappedVideo['nomMatch'] !== null) {
            $qExisting .= " AND nomMatch='" . mysqli_real_escape_string($conn, (string)$mappedVideo['nomMatch']) . "'";
        }
        $qExisting .= ' LIMIT 0,1';
        $retExisting = mysqli_query($conn, $qExisting);
        if ($retExisting && mysqli_num_rows($retExisting) > 0) {
            $rowExisting = mysqli_fetch_assoc($retExisting);
            $existingVideoId = isset($rowExisting['videoId']) ? intval($rowExisting['videoId']) : null;
        }
    }

    if ($existingVideoId !== null && isset($videoColumns['videoId'])) {
        $setParts = array();
        foreach ($dbData as $field => $value) {
            if ($field === 'videoId') {
                continue;
            }
            $setParts[] = $field . '=' . toSqlValue($conn, $value);
        }

        if (!empty($setParts)) {
            $qUpdate = 'UPDATE Video SET ' . implode(',', $setParts) . " WHERE videoId='" . intval($existingVideoId) . "'";
            mysqli_query($conn, $qUpdate);
        }
    } else {
        $insertFields = array();
        $insertValues = array();
        foreach ($dbData as $field => $value) {
            $insertFields[] = $field;
            $insertValues[] = toSqlValue($conn, $value);
        }
        $qInsert = 'INSERT INTO Video (' . implode(',', $insertFields) . ') VALUES (' . implode(',', $insertValues) . ')';
        mysqli_query($conn, $qInsert);
    }

    $syncOK[] = array(
        'nomFic' => $mappedVideo['nomFic'],
        'etat' => 'insert',
        'chrono' => $mappedVideo['chrono']
    );
}

echo json_encode($syncOK);
?>
