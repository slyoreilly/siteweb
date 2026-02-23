
<?php
////////////////////////////////////////////////////////////
//
//      upsertMatch.php
//      Est appelé dans MatchRepository.kt
//
//
////////////////////////////////////////////////////////////

include_once ($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR . "syncstatsconfig.php");

require '../scriptsphp/defenvvar.php';

$preMatch =null;
if(isset($_POST['match'])){
        $preMatch = $_POST["match"];
        $matchArray = json_decode($preMatch, true);
        }



$heure = $_POST['heure'];
$heureServeur = time()*1000;

$syncOK = array();

if($matchArray != null) {


        foreach ($matchArray as $match) {

                if (isset($heure)) {
                        // retourner le but, sans correction de chrono.
                        //$unClip['chrono'] = $unClip['chrono'] + $heureServeur - $heure;
                }

                $scoreDom = $match['scoreDom']!=null ? $match['scoreDom'] : 0;
                $scoreVis = $match['scoreVis']!=null ? $match['scoreVis'] : 0;
                $maDate =   date("Y-m-d H:i:s", round($match['date']/1000));
                $arenaId = $match['arenaId'] != null ? $match['arenaId'] : "NULL";


                if($match['GameComId']<1){
                        $qInsM = "INSERT INTO TableMatch (eq_dom, score_dom, eq_vis, score_vis, statut, matchIdRef, ligueRef, date, cleValeur, arenaId, TSDMAJ)
                        VALUES ('{$match['eqDom']}',{$scoreDom},'{$match['eqVis']}',{$scoreVis},0,'{$match['matchLongId']}','{$match['ligueId']}','{$maDate}','{$match['cleValeur']}',{$arenaId},'{$match['dernierMAJ']}')";

                        mysqli_query($conn,$qInsM) or die(mysqli_error($conn) . $qInsM);
                        $webMatchId=mysqli_insert_id($conn);

                }

                else{
                        $retour = mysqli_query($conn,"UPDATE TableMatch
                        SET eq_dom='{$match['eqDom']}',
                        score_dom=$scoreDom,
                        eq_vis='{$match['eqVis']}',
                        score_vis=$scoreVis,
                        statut='{$match['etat']}',
                        matchIdRef='{$match['matchLongId']}',
                        ligueRef='{$match['ligueId']}',
                        date='$maDate',
                        cleValeur='{$match['cleValeur']}',
                        arenaId=$arenaId,
                        TSDMAJ=NOW()
                        WHERE match_id='{$match['GameComId']}'");

                        $webMatchId = $match['GameComId'];

                }








                                $retObj = array("GameLocId"=>$match['GameLocId'], "GameComId"=>$webMatchId);
                                array_push($syncOK, $retObj);

                        }
                }

echo json_encode($syncOK);


//mysqli_close($conn);

?>
