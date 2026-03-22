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
    return true;
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

    return array(
        'eventComIdVide' => true,
        'autorise' => true,
        'raison' => 'event_com_id_vide_autorise'
    );
}

function upsertEvenementsValidationChrono(array $evenement): array
{
    if (!array_key_exists('chrono', $evenement)) {
        return array('ok' => false, 'raison' => 'chrono_absent');
    }

    if (!is_numeric($evenement['chrono'])) {
        return array('ok' => false, 'raison' => 'chrono_non_numerique');
    }

    if ((int)$evenement['chrono'] < 0) {
        return array('ok' => false, 'raison' => 'chrono_negatif');
    }

    return array('ok' => true, 'raison' => 'ok');
}

?>
