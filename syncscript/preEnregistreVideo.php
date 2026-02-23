<?php
require '../scriptsphp/defenvvar.php';

session_start();

function chercheDemandeAjoutVideo($conn, $camID, $chronoVideo) {
    $qDemande = "SELECT demandeId, eventId, typeEvenement, chronoDemande, chronoVideo "
        . "FROM DemandeAjoutVideo "
        . "WHERE progression=2 AND cameraId='" . intval($camID) . "' "
        . "AND ABS(chronoVideo-'" . intval($chronoVideo) . "')<=120000 "
        . "ORDER BY ABS(chronoVideo-'" . intval($chronoVideo) . "') ASC, demandeId ASC LIMIT 0,1";

    $resDemande = mysqli_query($conn, $qDemande);
    if ($resDemande && mysqli_num_rows($resDemande) > 0) {
        return mysqli_fetch_array($resDemande);
    }

    return null;
}

$params = array();
error_log("preEnregistre: " . $_POST['videos']);
$params = json_decode($_POST['videos'], true);
$heure = $_POST['heure'];
$emplacementTmp = parse_url($_POST['emplacement']);
$emplacement = $emplacementTmp['host'] . $emplacementTmp['path'];
$avanceServeur = time() * 1000 - $heure;

$syncOK = array();
for ($a = 0; $a < count($params); $a++) {
    $camID = $params[$a]['video']['camID'];
    $exploded = explode('/', $params[$a]['video']['nomFic']);
    $nomFic = array_pop($exploded);
    $rSServ = $params[$a]['video']['chrono'] + $avanceServeur;

    $demandeAjoutVideo = chercheDemandeAjoutVideo($conn, $camID, $rSServ);

    if (!empty($nomFic)) {
        $monObj = array();
        $qSel = "SELECT * FROM Video
                JOIN TableMatch
                    ON (match_id = nomMatch)
            WHERE nomMatch='{$params[$a]['video']['nomMatch']}' AND camId='{$camID}' AND nomFichier Like '{$nomFic}%'";
        $retSel = mysqli_query($conn, $qSel) or die("Erreur: " . $qSel . "\n" . mysqli_error($conn));

        $type = 1000;
        $reference = 1000;
        $cv = json_decode(stripslashes($params[$a]['video']['cv']), true);

        if (isset($cv['type'])) {
            $type = $cv['type'];
        }
        if (isset($cv['reference'])) {
            $reference = $cv['reference'];
        }

        if ($demandeAjoutVideo != null) {
            $type = intval($demandeAjoutVideo['typeEvenement']);
            $reference = intval($demandeAjoutVideo['eventId']);
        }

        if (mysqli_num_rows($retSel) > 0) {
            while ($rangSel = mysqli_fetch_array($retSel)) {
                $emplacement = $rangSel['emplacement'];
                if (is_null($emplacement)) {
                    $emplacement = "www.syncstats.com";
                }
            }

            $esSimple = null;
            if (isset($params[$a]['video']['esSimple'])) {
                $esSimple = $params[$a]['video']['esSimple'];
            }

            if (file_exists('http://' . $emplacement . '/lookatthis/' . $nomFic)) {
                if ($esSimple != 12) {
                    $monObj['etat'] = 'insert';
                } else {
                    $monObj['etat'] = 'deja';
                }
            } else {
                $monObj['etat'] = 'insert';
            }

            $monObj['nomFic'] = $nomFic;
            $monObj['chrono'] = $rSServ;
            array_push($syncOK, $monObj);
        } else {
            if ($type != 5) {
                $type = 0;
            }

            $query = "INSERT INTO Video (nomFichier,nomMatch,chrono,camId,type,reference,emplacement) " .
                "VALUES ('{$nomFic}','{$params[$a]['video']['nomMatch']}','{$rSServ}','{$camID}','{$type}' ,'{$reference}','{$emplacement}')";
            mysqli_query($conn, $query) or die("Erreur: " . $query . "\n" . mysqli_error($conn));

            $monObj['nomFic'] = $nomFic;
            $monObj['etat'] = 'insert';
            $monObj['chrono'] = $rSServ;
            array_push($syncOK, $monObj);
        }

        if ($demandeAjoutVideo != null) {
            $qMajDemande = "UPDATE DemandeAjoutVideo
                    SET progression=3, videoNomFichier='" . $nomFic . "', updatedAt=NOW()
                    WHERE demandeId='" . intval($demandeAjoutVideo['demandeId']) . "'";
            mysqli_query($conn, $qMajDemande);
        }
    }
}

$ret = json_encode($syncOK);
echo $ret;

if ($ret == false) {
    echo "erreur, count(syncOK:): " . count($syncOK) . "- count($params): " . count($params);
}
?>
