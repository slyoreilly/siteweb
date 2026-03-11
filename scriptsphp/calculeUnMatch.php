<?php

/////////////////////////////////////////////////////////////
//
//  DÃ©finitions des variables
//
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';

function calculeUnMatchByNoMatchId($noMatchId)
{
    global $conn;

    if (!isset($noMatchId) || $noMatchId === '' || $noMatchId === 0 || $noMatchId === '0') {
        return false;
    }

    $rEnr = mysqli_query($conn, "SELECT matchIdRef,eq_dom,eq_vis
                                    FROM TableMatch 
                                WHERE match_id = '{$noMatchId}'") or die(mysqli_error($conn));
    $isEnr = mysqli_num_rows($rEnr);

    if ($isEnr <= 0) {
        return false;
    }

    $vecMatch = mysqli_fetch_row($rEnr);
    $matchIdRef = $vecMatch[0];
    $eDom = $vecMatch[1];
    $eVis = $vecMatch[2];

    $rPeriode = mysqli_query($conn, "SELECT MAX(souscode) as sc
                                FROM TableEvenement0 
                                WHERE match_event_id = '{$matchIdRef}' 
                                AND code=11") or die(mysqli_error($conn));

    if (mysqli_num_rows($rPeriode) > 0) {
        $statutAr = mysqli_fetch_row($rPeriode);
        if ($statutAr[0] < 10) {
            if (is_null($statutAr[0])) {
                $statut = 0;
            } else {
                $statut = $statutAr[0];
            }
        } else {
            $statut = $statutAr[0] % 10;
            $statut = $statut . 'P';
        }
    } else {
        $statut = 0;
    }

    $matchFini = mysqli_query($conn, "SELECT * 
                                FROM TableEvenement0 
                                WHERE match_event_id = '{$matchIdRef}' 
                                AND code=10 
                                AND souscode=10") or die(mysqli_error($conn));
    $fini = mysqli_num_rows($matchFini);

    $qComptDom = "SELECT TableEvenement0.*,EventType.GameValue 
FROM TableEvenement0 
JOIN EventType 
   ON (TableEvenement0.code=EventType.Code)
   WHERE match_event_id = '{$matchIdRef}' 
    AND GameValue>0 
    AND equipe_event_id =  '{$eDom}'";
    $compteDom = mysqli_query($conn, $qComptDom) or die(mysqli_error($conn));

    $compteVis = mysqli_query($conn, "SELECT TableEvenement0.*,EventType.GameValue
                                FROM TableEvenement0 
                                JOIN EventType 
                                   ON (TableEvenement0.code=EventType.Code)
                                   WHERE match_event_id = '{$matchIdRef}' 
                                    AND GameValue>0 
                                    AND equipe_event_id = '{$eVis}'") or die(mysqli_error($conn));

    $cDom = 0;
    $cVis = 0;

    while ($rangCDom = mysqli_fetch_array($compteDom)) {
        $cDom = $cDom + $rangCDom['GameValue'];
    }
    while ($rangCVis = mysqli_fetch_array($compteVis)) {
        $cVis = $cVis + $rangCVis['GameValue'];
    }

    if ($fini > 0) {
        if ($cDom == $cVis) {
            $cFD = 0;
            $cFV = 0;

            $resFus = mysqli_query($conn, "SELECT  * FROM TableEvenement0 
                                        WHERE match_event_id = '{$matchIdRef}' AND code=2 AND souscode=1") or die(mysqli_error($conn));

            while ($rangFus = mysqli_fetch_array($resFus)) {
                if ($rangFus['equipe_event_id'] == $eDom) {
                    $cFD++;
                }
                if ($rangFus['equipe_event_id'] == $eVis) {
                    $cFV++;
                }
            }
            if ($cFD > $cFV) {
                $cDom++;
            }
            if ($cFV > $cFD) {
                $cVis++;
            }
        }

        $qNiou = "UPDATE TableMatch
                                            SET score_dom='{$cDom}', score_vis='{$cVis}' ,statut='F'
                                            WHERE match_id='{$noMatchId}'";
        $retour = mysqli_query($conn, $qNiou);
    } else {
        $qNiou = "UPDATE TableMatch
                                            SET score_dom='{$cDom}', score_vis='{$cVis}' ,statut='{$statut}'
                                            WHERE match_id='{$noMatchId}'";
        $retour = mysqli_query($conn, $qNiou);
    }

    return ($retour !== false);
}

if (isset($_POST['noMatchId'])) {
    calculeUnMatchByNoMatchId($_POST['noMatchId']);
}
?>