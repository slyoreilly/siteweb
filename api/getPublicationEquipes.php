<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/publication_lecture.php';
try {
    $conn = publicationConnexion();
    $ligueId = publicationEntier('ligueId');
    $saisonId = publicationEntier('saisonId', true);
    [$debut, $fin] = publicationPeriode($conn, $ligueId, $saisonId);
    $lignes = publicationLire($conn, 'SELECT te.equipe_id AS id, te.nom_equipe AS nom,
        ael.debutAbon, ael.finAbon FROM TableEquipe te
        JOIN abonEquipeLigue ael ON ael.equipeId = te.equipe_id
        WHERE ael.ligueId = ? AND ael.permission < 31
        AND ael.debutAbon <= ? AND ael.finAbon >= ?
        ORDER BY te.nom_equipe, te.equipe_id, ael.debutAbon', 'iss', [$ligueId, $fin, $debut]);
    $equipes = [];
    foreach ($lignes as $ligne) {
        $id = (int)$ligne['id'];
        if (!isset($equipes[$id])) $equipes[$id] = ['id' => $id, 'nom' => $ligne['nom'],
            'ligueId' => $ligueId, 'saisonId' => $saisonId, 'abonnements' => []];
        $equipes[$id]['abonnements'][] = ['debut' => $ligne['debutAbon'], 'fin' => $ligne['finAbon']];
    }
    publicationRepondre(200, array_values($equipes));
} catch (Throwable $erreur) { publicationErreur($erreur); }
