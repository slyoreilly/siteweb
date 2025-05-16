<?php
require '../scriptsphp/defenvvar.php';
session_start();

$params = json_decode($_POST['videos'], true);
$heure = $_POST['heure'];
$emplacementTmp = parse_url($_POST['emplacement']);
$emplacement = $emplacementTmp['host'] . $emplacementTmp['path'];
$avanceServeur = time() * 1000 - $heure;

$syncOK = array();

foreach ($params as $item) {
    $video = $item['video'];
    $camID = $video['camID'];
    $nomFic = basename($video['nomFic']);
    $nomMatch = $video['nomMatch'];
    $rSServ = $video['chrono'] + $avanceServeur;

    $cv = isset($video['cv']) ? json_decode(stripslashes($video['cv']), true) : [];
    $type = isset($cv['type']) ? $cv['type'] : "0";
    $reference = isset($video['reference']) ? intval($video['reference']) : 0;

    $qSel = "SELECT * FROM Video 
             JOIN TableMatch ON (match_id = nomMatch)  
             WHERE nomMatch='$nomMatch' AND camId='$camID' AND nomFichier LIKE '$nomFic%'";

    $retSel = mysqli_query($conn, $qSel) or die("Erreur: $qSel\n" . mysqli_error($conn));

    $monObj = array();
    $esSimple = isset($video['esSimple']) ? $video['esSimple'] : null;

    if (mysqli_num_rows($retSel) > 0) {
        $rangSel = mysqli_fetch_array($retSel);
        $emplacement = $rangSel['emplacement'] ?? "www.syncstats.com";

        $url = "http://$emplacement/lookatthis/$nomFic";
        $monObj['etat'] = ($esSimple != 12 && !file_exists($url)) ? 'insert' : 'deja';
    } else {
        if ($type != "5") $type = "0";
        $cleValeur = json_encode(['type' => $type]);

        $query = "INSERT INTO Video (nomFichier, nomMatch, chrono, camId, type, reference, emplacement)
                  VALUES ('$nomFic', '$nomMatch', '$rSServ', '$camID', '$type', '$reference', '$emplacement')";
        mysqli_query($conn, $query) or die("Erreur: $query\n" . mysqli_error($conn));

        $monObj['etat'] = 'insert';
    }

    $monObj['nomFic'] = $nomFic;
    $monObj['chrono'] = $rSServ;
    $syncOK[] = $monObj;
}

echo json_encode($syncOK);
if (!$syncOK) {
    echo "erreur, count(syncOK): " . count($syncOK) . " - count(params): " . count($params);
}

mysqli_close($conn);
?>
