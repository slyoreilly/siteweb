<?php

declare(strict_types=1);

function evenementsReadConstruireItem(array $row): array
{
    $type = (int)$row['type'];
    $eventId = (int)$row['eventId'];
    $eventComId = ($type === 0) ? $eventId : null;

    return array(
        'type' => $type,
        'eventId' => $eventId,
        'EventComId' => $eventComId,
        'chrono' => (int)$row['chrono'],
        'matchIdRef' => $row['matchIdRef'],
        'matchId' => (int)$row['matchId'],
        'ligueId' => (int)$row['ligueId'],
        'arenaId' => (int)$row['arenaId'],
        'eqDom' => (int)$row['eqDom'],
        'eqVis' => (int)$row['eqVis'],
        'date' => $row['date'],
        'code' => (int)$row['code'],
        'sousCode' => (int)$row['sousCode'],
        'scoringEnd' => isset($row['scoringEnd']) ? (int)$row['scoringEnd'] : null,
    );
}

?>
