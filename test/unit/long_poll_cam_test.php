<?php
// Scénarios ciblés des trois versions, horloge virtuelle et SQL doublé.
foreach (array('sync', 'sync1', 'sync2') as $version) {
    foreach (array('donnees_tardif', 'donnees_reprise', 'donnees_reprise_exception',
        'epuisement', 'epuisement_exception', 'saturation_courte', 'expiration', 'expiration_courte', 'expiration_plafonnee') as $scenario) {
        passthru(escapeshellarg(PHP_BINARY) . ' -n '
            . escapeshellarg(__DIR__ . '/occupation_connexions_test.php') . ' '
            . escapeshellarg($version . '_' . $scenario), $code);
        if ($code !== 0) { exit($code); }
    }
}
foreach (array('sync2_demandes', 'sync2_demandes_seules', 'sync2_demandes_seules_saturation', 'sync2_demandes_sans_match', 'sync2_demandes_apres_pause', 'sync_init_exception') as $cas) {
    passthru(escapeshellarg(PHP_BINARY) . ' -n '
        . escapeshellarg(__DIR__ . '/occupation_connexions_test.php') . ' '
        . escapeshellarg($cas), $code);
    if ($code !== 0) { exit($code); }
}
// Un exit dans le bootstrap historique doit lui aussi produire une réponse exploitable.
exec(escapeshellarg(PHP_BINARY) . ' -n '
    . escapeshellarg(__DIR__ . '/occupation_connexions_test.php') . ' sync_init_exit', $lignes, $code);
$reponse = json_decode(implode("\n", $lignes), true);
if ($code !== 0 || !is_array($reponse) || $reponse['matchPeriode'] !== array()
    || !isset($reponse['heure'], $reponse['match'], $reponse['video'], $reponse['matchs'])) {
    throw new RuntimeException('Repli JSON du bootstrap interrompu incorrect');
}
echo 'OK sync_init_exit' . PHP_EOL;
