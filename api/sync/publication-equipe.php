<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/publication_lecture.php';

// Aucun chemin d'écriture tant que le contrat de stockage n'est pas approuvé.
// Ne jamais annoncer 404 : l'absence de reçu ne peut pas encore être établie.
try {
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? '', ['GET', 'POST'], true)) {
        header('Allow: GET, POST');
        publicationRepondre(405, ['etat' => 'methode_non_autorisee']);
    }
    publicationAuthentifier();
    publicationRepondre(503, ['etat' => 'ECRITURES_BLOQUEES',
        'detail' => 'Atomicite non etablie ; contrat de reprise et stockage des recus a valider.']);
} catch (Throwable $erreur) { publicationErreur($erreur); }
