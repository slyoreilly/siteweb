<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/publication_lecture.php';
try {
    $conn = publicationConnexion();
    $equipeId = publicationEntier('equipeId');
    $ligueId = publicationEntier('ligueId');
    $saisonId = publicationEntier('saisonId', true);
    [$debut, $fin] = publicationPeriode($conn, $ligueId, $saisonId);
    $equipes = publicationLire($conn, 'SELECT equipeId FROM abonEquipeLigue
        WHERE ligueId = ? AND equipeId = ? AND permission < 31
        AND debutAbon <= ? AND finAbon >= ?', 'iiss', [$ligueId, $equipeId, $fin, $debut]);
    if (!$equipes) publicationRepondre(422, ['etat' => 'equipe_hors_contexte']);
    $lignes = publicationLire($conn, 'SELECT tj.joueur_id AS id, tj.NomJoueur AS nom,
        tj.NumeroJoueur AS numeroChandail, tj.position, aje.abonJouEq AS inscriptionId,
        aje.debutAbon, aje.finAbon FROM TableJoueur tj
        JOIN abonJoueurEquipe aje ON aje.joueurId = tj.joueur_id
        WHERE aje.equipeId = ? AND aje.permission < 31
        AND aje.debutAbon <= ? AND aje.finAbon >= ?
        AND EXISTS (SELECT 1 FROM abonEquipeLigue ael WHERE ael.equipeId = aje.equipeId
            AND ael.ligueId = ? AND ael.permission < 31
            AND GREATEST(ael.debutAbon, aje.debutAbon, ?) <= LEAST(ael.finAbon, aje.finAbon, ?))
        ORDER BY tj.NomJoueur, tj.joueur_id, aje.abonJouEq',
        'ississ', [$equipeId, $fin, $debut, $ligueId, $debut, $fin]);
    publicationRepondre(200, array_map('publicationJoueur', $lignes));
} catch (Throwable $erreur) { publicationErreur($erreur); }
