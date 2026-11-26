<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/publication_lecture.php';
try {
    $conn = publicationConnexion();
    $nom = $_GET['nom'] ?? '';
    if (!is_string($nom) || strlen(trim($nom)) < 3 || strlen($nom) > 120) {
        publicationRepondre(400, ['etat' => 'parametre_invalide', 'detail' => 'nom de 3 à 120 octets requis']);
    }
    // Recherche globale bornée : aucune donnée de contact, aucun HCR supposé.
    $motif = '%' . strtr(trim($nom), ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    $lignes = publicationLire($conn, "SELECT joueur_id AS id, NomJoueur AS nom,
        NumeroJoueur AS numeroChandail, position FROM TableJoueur
        WHERE NomJoueur LIKE ? ESCAPE '!' ORDER BY NomJoueur, joueur_id LIMIT 51", 's', [$motif]);
    publicationRepondre(200, ['elements' => array_map('publicationJoueur', array_slice($lignes, 0, 50)),
        'lectureComplete' => count($lignes) <= 50]);
} catch (Throwable $erreur) { publicationErreur($erreur); }
