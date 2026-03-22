<?php

declare(strict_types=1);

function upsertEvenementsSourceNormalisee($value): string
{
    if ($value === null) {
        return '';
    }

    return strtolower(trim((string)$value));
}

function upsertEvenementsSourceAutoriseePourCreationLocale(array $evenement): bool
{
    $source = upsertEvenementsSourceNormalisee($evenement['source'] ?? null);

    return in_array($source, array('plateforme', 'plateformeweb'), true);
}

function upsertEvenementsDecisionCreationLocale(array $evenement): array
{
    $eventComIdValue = $evenement['EventComId'] ?? null;
    $eventComIdVide = ($eventComIdValue === null || $eventComIdValue === '');

    if (!$eventComIdVide) {
        return array(
            'eventComIdVide' => false,
            'autorise' => true,
            'raison' => 'event_com_id_present'
        );
    }

    $sourceAutorisee = upsertEvenementsSourceAutoriseePourCreationLocale($evenement);
    if ($sourceAutorisee) {
        return array(
            'eventComIdVide' => true,
            'autorise' => true,
            'raison' => 'event_com_id_vide_source_autorisee'
        );
    }

    return array(
        'eventComIdVide' => true,
        'autorise' => false,
        'raison' => 'event_com_id_vide_source_non_autorisee'
    );
}

?>
