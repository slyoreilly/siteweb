<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/publication_lecture.php';
try {
    $conn = publicationConnexion();
    $lignes = publicationLire($conn, 'SELECT l.ID_Ligue AS id, l.Nom_Ligue AS nom,
        s.saisonId, s.premierMatch, s.dernierMatch, s.saisonActive
        FROM Ligue l LEFT JOIN TableSaison s ON s.ligueRef = l.ID_Ligue
        ORDER BY l.Nom_Ligue, s.premierMatch, s.saisonId');
    $ligues = [];
    foreach ($lignes as $ligne) {
        $id = (int)$ligne['id'];
        if (!isset($ligues[$id])) $ligues[$id] = ['id' => $id, 'nom' => $ligne['nom'], 'saisons' => []];
        if ($ligne['saisonId'] !== null) $ligues[$id]['saisons'][] = [
            'id' => (int)$ligne['saisonId'], 'debut' => $ligne['premierMatch'],
            'fin' => $ligne['dernierMatch'], 'active' => (bool)$ligne['saisonActive']];
    }
    publicationRepondre(200, array_values($ligues));
} catch (Throwable $erreur) { publicationErreur($erreur); }
