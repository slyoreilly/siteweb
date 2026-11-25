<?php
namespace TransitionsRemote;
use DateTime;
use DateTimeZone;

// Le script complet est exécuté ; seules l'ouverture et les requêtes SQL sont doublées.
function verifier($condition, $message) {
    if (!$condition) { throw new \RuntimeException($message); }
}
function mysqli_query($connexion, $sql) {
    $GLOBALS['requetes'][] = $sql;
    return strpos($sql, 'SELECT ') === 0 ? (object)[] : true;
}
function mysqli_num_rows($resultat) { return $GLOBALS['ancienEtat'] === null ? 0 : 1; }
function mysqli_fetch_row($resultat) { return [$GLOBALS['ancienEtat']]; }

$source = file_get_contents(__DIR__ . '/../../scriptsphp/enregistreStatutRemote.php');
verifier(substr_count($source, "require '../scriptsphp/defenvvar.php';") === 1, 'Inclusion inattendue');
$source = str_replace("require '../scriptsphp/defenvvar.php';", '$conn = new \\stdClass();', $source);
$source = str_replace('<?php', '<?php namespace TransitionsRemote; use DateTime; use DateTimeZone;', $source);
foreach ([['10', '10'], ['10', '20'], ['20', '10'], ['10', '0'], ['0', '10'], ['0', '0'], [10, '10'], [null, '20']] as $transition) {
    list($ancienEtat, $nouvelEtat) = $transition;
    $requetes = [];
    $_POST = ['username'=>'compte-test', 'arenaId'=>'1', 'telId'=>'remote-test',
        'batterie'=>'80', 'memoire'=>'123456', 'temperature'=>'31', 'codeEtat'=>$nouvelEtat,
        'remoteId'=>'2', 'version'=>'test', 'settings'=>'{"mode":"match"}'];
    ob_start();
    eval('?>' . $source);
    $sortie = ob_get_clean();
    verifier(count($requetes) === 2, 'Une lecture et une écriture sont attendues');
    $ecriture = $requetes[1];
    if ($ancienEtat === null) {
        verifier(strpos($ecriture, 'INSERT INTO StatutRemote') === 0, 'Insertion absente');
        verifier(strpos($ecriture, "'20','remote-test'") !== false, 'État initial perdu');
        verifier(strpos($sortie, ' - INS') !== false, 'Réponse insertion modifiée');
    } else {
        $changement = $ancienEtat != $nouvelEtat;
        verifier(strpos($ecriture, "codeEtat = '{$nouvelEtat}'") !== false, 'État reçu écrasé');
        verifier((strpos($ecriture, 'dernierModif') !== false) === $changement, 'Horodatage de transition incorrect');
        verifier(strpos($sortie, $changement ? '- MOD2' : '- MOD1') !== false, 'Branche de réponse incorrecte');
    }
    verifier(strpos($ecriture, 'dernierMaJ') !== false, 'Heartbeat non horodaté');
    echo 'OK Remote ' . ($ancienEtat === null ? 'absent' : $ancienEtat) . ' -> ' . $nouvelEtat . PHP_EOL;
}
