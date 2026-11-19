<?php

// Configuration privée historique : même emplacement en DEV et en PROD.
// Le fichier peut appeler putenv() ou retourner un tableau de variables.
// Les variables déjà fournies par le serveur restent prioritaires.
$jetonLegacyEnvironnement = getenv('LEGACY_MATCH_ACTIF_TOKEN');
$fichierSecretsLegacy = __DIR__ . '/../.env.php';
if (is_readable($fichierSecretsLegacy)) {
    $secretsLegacy = require $fichierSecretsLegacy;
    if ($jetonLegacyEnvironnement === false && is_array($secretsLegacy)
        && isset($secretsLegacy['LEGACY_MATCH_ACTIF_TOKEN'])
        && is_string($secretsLegacy['LEGACY_MATCH_ACTIF_TOKEN'])) {
        putenv('LEGACY_MATCH_ACTIF_TOKEN=' . $secretsLegacy['LEGACY_MATCH_ACTIF_TOKEN']);
    }
}
if ($jetonLegacyEnvironnement !== false) {
    putenv('LEGACY_MATCH_ACTIF_TOKEN=' . $jetonLegacyEnvironnement);
}
unset($jetonLegacyEnvironnement, $fichierSecretsLegacy, $secretsLegacy);
