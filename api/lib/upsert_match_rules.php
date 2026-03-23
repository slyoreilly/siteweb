<?php

declare(strict_types=1);

function upsertMatchNormaliser(array $match): array
{
    $scoreDom = isset($match['scoreDom']) && is_numeric($match['scoreDom']) ? (int)$match['scoreDom'] : 0;
    $scoreVis = isset($match['scoreVis']) && is_numeric($match['scoreVis']) ? (int)$match['scoreVis'] : 0;

    $rawDate = isset($match['date']) && is_numeric($match['date']) ? (int)$match['date'] : 0;
    if ($rawDate <= 0) {
        $maDate = date('Y-m-d H:i:s');
    } else {
        $maDate = date('Y-m-d H:i:s', (int)round($rawDate / 1000));
    }

    return array(
        'GameLocId' => isset($match['GameLocId']) ? (int)$match['GameLocId'] : 0,
        'GameComId' => isset($match['GameComId']) && is_numeric($match['GameComId']) ? (int)$match['GameComId'] : 0,
        'eqDom' => isset($match['eqDom']) && is_numeric($match['eqDom']) ? (int)$match['eqDom'] : 0,
        'eqVis' => isset($match['eqVis']) && is_numeric($match['eqVis']) ? (int)$match['eqVis'] : 0,
        'ligueId' => isset($match['ligueId']) && is_numeric($match['ligueId']) ? (int)$match['ligueId'] : 0,
        'matchLongId' => trim((string)($match['matchLongId'] ?? '')),
        'scoreDom' => $scoreDom,
        'scoreVis' => $scoreVis,
        'dateSql' => $maDate,
        'arenaId' => (isset($match['arenaId']) && is_numeric($match['arenaId'])) ? (int)$match['arenaId'] : null,
        'cleValeur' => isset($match['cleValeur']) ? (string)$match['cleValeur'] : '',
        'etat' => isset($match['etat']) && is_numeric($match['etat']) ? (int)$match['etat'] : 0,
        'TSDMAJ' => isset($match['dernierMAJ']) && is_numeric($match['dernierMAJ']) ? (string)$match['dernierMAJ'] : (string)(time() * 1000),
    );
}

?>
