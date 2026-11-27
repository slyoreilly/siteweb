<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/publication_verification.php';
try {
    $conn = publicationConnexion();
    $instance = getenv('LEGACY_PUBLICATION_INSTANCE');
    if (!is_string($instance) || trim($instance) === '' || strlen($instance) > 80) {
        publicationRepondre(503, ['etat' => 'instance_non_configuree']);
    }
    $ligue = publicationEntier('ligueId');
    $saison = publicationEntier('saisonId');
    $equipe = publicationEntier('equipeId', true);
    if (($equipe === null && ($_GET['creerEquipe'] ?? null) !== '1')
        || ($equipe !== null && isset($_GET['creerEquipe']))) {
        publicationRepondre(400, ['etat' => 'parametre_invalide', 'detail' => 'equipeId ou creerEquipe=1 requis']);
    }
    try { $ids = publicationIdentifiantsExacts($_GET['joueurIds'] ?? null); }
    catch (InvalidArgumentException $erreur) {
        publicationRepondre(400, ['etat' => 'parametre_invalide', 'detail' => $erreur->getMessage()]);
    }
    [$sql, $types, $valeurs] = publicationRequeteVerification($ligue, $saison, $equipe, $ids);
    $lignes = publicationLire($conn, $sql, $types, $valeurs);
    $resultat = publicationConstruireVerification($instance, $ligue, $saison, $equipe, $ids, $lignes);
    $conn->rollback();
    publicationRepondre(200, $resultat);
} catch (Throwable $erreur) { publicationErreur($erreur); }
