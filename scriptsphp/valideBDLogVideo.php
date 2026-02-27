<?php
require '../scriptsphp/defenvvar.php';
header('Content-Type: application/json; charset=utf-8');

$telId = isset($_POST['telId']) ? trim($_POST['telId']) : '';
$rowsRaw = isset($_POST['rows']) ? $_POST['rows'] : '[]';
$rows = json_decode($rowsRaw, true);

if (!is_array($rows)) {
    echo json_encode(array('ok' => false, 'message' => 'Paramètre rows invalide.'));
    exit;
}

$summary = array(
    'total' => count($rows),
    'found' => 0,
    'notFound' => 0,
    'allOk' => 0,
    'typeMismatch' => 0,
    'chronoMismatch' => 0,
    'referenceMismatch' => 0,
    'nomMatchMismatch' => 0,
    'baseSkipped' => 0,
    'syncErrSkipped' => 0
);

$details = array();

foreach ($rows as $row) {
    $nomFichier = isset($row['nomFichier']) ? trim($row['nomFichier']) : '';
    $nomMatch = isset($row['nomMatch']) ? trim($row['nomMatch']) : '';
    $typeVideo = isset($row['typeVideo']) ? strval($row['typeVideo']) : '';
    $reference = isset($row['reference']) ? strval($row['reference']) : '';
    $etatSync = isset($row['etatSync']) ? strval($row['etatSync']) : '';
    $chrono = isset($row['chrono']) ? intval($row['chrono']) : 0;
    $camId = $telId;

    if ($etatSync === '2323') {
        $summary['baseSkipped']++;
        $details[] = array(
            'nomFichier' => $nomFichier,
            'found' => true,
            'allOk' => true,
            'skippedBase' => true,
            'reason' => 'Vidéo de base (etatSync=2323), absence en table Video autorisée'
        );
        continue;
    }

    if ($nomFichier === '') {
        $summary['notFound']++;
        $details[] = array('nomFichier' => '', 'found' => false, 'reason' => 'nomFichier manquant',
            'expected' => array(
                'nomFichier' => $nomFichier,
                'camId' => $camId,
                'type' => $typeVideo,
                'reference' => $reference,
                'nomMatch' => $nomMatch,
                'chrono' => $chrono
            ));
        continue;
    }

    $nomFichierEsc = mysqli_real_escape_string($conn, $nomFichier);
    $telIdEsc = mysqli_real_escape_string($conn, $telId);

    $whereCam = "";
    if ($telIdEsc !== '') {
        $whereCam = " AND camId='" . $telIdEsc . "'";
    }

    $q = "SELECT videoId, nomFichier, camId, type, chrono, reference, nomMatch\n"
       . "FROM Video\n"
       . "WHERE nomFichier LIKE '" . $nomFichierEsc . "%'" . $whereCam . "\n"
       . "ORDER BY ABS(chrono-" . intval($chrono) . ") ASC\n"
       . "LIMIT 1";

    $r = mysqli_query($conn, $q);
    if (!$r || mysqli_num_rows($r) === 0) {
        if ($etatSync === '2626') {
            $summary['syncErrSkipped']++;
            $details[] = array(
                'nomFichier' => $nomFichier,
                'found' => false,
                'ignoredNotFound' => true,
                'etatSync' => $etatSync,
                'reason' => 'Vidéo erronée (etatSync=2626), absence en table Video tolérée',
                'sqlAbsence' => $q,
                'expected' => array(
                    'nomFichier' => $nomFichier,
                    'camId' => $camId,
                    'type' => $typeVideo,
                    'reference' => $reference,
                    'nomMatch' => $nomMatch,
                    'chrono' => $chrono
                )
            );
            continue;
        }

        $summary['notFound']++;
        $details[] = array(
            'nomFichier' => $nomFichier,
            'found' => false,
            'reason' => 'Aucune vidéo correspondante',
            'sqlAbsence' => $q,
            'expected' => array(
                'nomFichier' => $nomFichier,
                'camId' => $camId,
                'type' => $typeVideo,
                'reference' => $reference,
                'nomMatch' => $nomMatch,
                'chrono' => $chrono
            )
        );
        continue;
    }

    $db = mysqli_fetch_assoc($r);
    $summary['found']++;

    $dbType = strval($db['type']);
    $dbRef = strval($db['reference']);
    $dbNomMatch = strval($db['nomMatch']);
    $dbChrono = intval($db['chrono']);
    $deltaChrono = abs($dbChrono - $chrono);

    $typeOk = ($typeVideo === $dbType);
    $referenceOk = ($reference === $dbRef);
    $nomMatchOk = ($nomMatch === $dbNomMatch);
    $chronoOk = ($deltaChrono <= 10000);

    if (!$typeOk) $summary['typeMismatch']++;
    if (!$referenceOk) $summary['referenceMismatch']++;
    if (!$nomMatchOk) $summary['nomMatchMismatch']++;
    if (!$chronoOk) $summary['chronoMismatch']++;

    $allOk = $typeOk && $referenceOk && $nomMatchOk && $chronoOk;
    if ($allOk) {
        $summary['allOk']++;
    }

    $details[] = array(
        'nomFichier' => $nomFichier,
        'found' => true,
        'allOk' => $allOk,
        'checks' => array(
            'typeOk' => $typeOk,
            'referenceOk' => $referenceOk,
            'nomMatchOk' => $nomMatchOk,
            'chronoOk' => $chronoOk,
            'deltaChrono' => $deltaChrono
        ),
        'expected' => array(
            'nomFichier' => $nomFichier,
            'camId' => $camId,
            'type' => $typeVideo,
            'reference' => $reference,
            'nomMatch' => $nomMatch,
            'chrono' => $chrono
        ),
        'db' => array(
            'videoId' => $db['videoId'],
            'nomFichier' => $db['nomFichier'],
            'camId' => $db['camId'],
            'type' => $dbType,
            'reference' => $dbRef,
            'nomMatch' => $dbNomMatch,
            'chrono' => $dbChrono
        )
    );
}

echo json_encode(array(
    'ok' => true,
    'summary' => $summary,
    'details' => $details
));
