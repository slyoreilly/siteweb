<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/publication_lecture.php';
try {
    $conn = publicationConnexion();
    $nom = $_GET['nom'] ?? '';
    if (!is_string($nom) || strlen(trim($nom)) < 3 || strlen($nom) > 120) {
        publicationRepondre(400, ['etat' => 'parametre_invalide', 'detail' => 'nom de 3 à 120 octets requis']);
    }
    $ligueId = publicationEntier('ligueId', true);
    $saisonId = publicationEntier('saisonId', true);
    $equipeId = publicationEntier('equipeId', true);
    if ($ligueId === null && ($saisonId !== null || $equipeId !== null)) {
        publicationRepondre(400, ['etat' => 'parametre_invalide', 'detail' => 'ligueId requis avec saisonId ou equipeId']);
    }
    $motif = '%' . strtr(trim($nom), ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    if ($ligueId === null) {
        // Recherche globale conservée : aucune donnée de contact, aucun HCR supposé.
        $lignes = publicationLire($conn, "SELECT joueur_id AS id, NomJoueur AS nom,
            NumeroJoueur AS numeroChandail, position FROM TableJoueur
            WHERE NomJoueur LIKE ? ESCAPE '!' ORDER BY NomJoueur, joueur_id LIMIT 51", 's', [$motif]);
    } else {
        [$debut, $fin] = publicationPeriode($conn, $ligueId, $saisonId);
        if ($equipeId !== null) {
            $equipes = publicationLire($conn, 'SELECT equipeId FROM abonEquipeLigue
                WHERE ligueId = ? AND equipeId = ? AND permission < 31
                AND debutAbon <= ? AND finAbon >= ?', 'iiss', [$ligueId, $equipeId, $fin, $debut]);
            if (!$equipes) publicationRepondre(422, ['etat' => 'equipe_hors_contexte']);
        }
        // Une ligne par identité avant LIMIT, même avec plusieurs inscriptions.
        // La présence cible et les appartenances exigent la même période commune.
        $sqlIdentites = "SELECT tj.joueur_id AS id, tj.NomJoueur AS nom,
            tj.NumeroJoueur AS numeroChandail, tj.position, contexte.presentDansEquipeCible
            FROM TableJoueur tj
            JOIN (SELECT aje.joueurId,
                MAX(CASE WHEN aje.equipeId = ? THEN 1 ELSE 0 END) AS presentDansEquipeCible
                FROM abonJoueurEquipe aje
                JOIN abonEquipeLigue ael ON ael.equipeId = aje.equipeId
                WHERE ael.ligueId = ? AND aje.permission < 31 AND ael.permission < 31
                AND GREATEST(ael.debutAbon, aje.debutAbon, ?) <= LEAST(ael.finAbon, aje.finAbon, ?)
                GROUP BY aje.joueurId) contexte ON contexte.joueurId = tj.joueur_id
            WHERE tj.NomJoueur LIKE ? ESCAPE '!'
            ORDER BY contexte.presentDansEquipeCible DESC, tj.NomJoueur, tj.joueur_id LIMIT 51";
        $lignes = publicationLire($conn, $sqlIdentites, 'iisss', [$equipeId, $ligueId, $debut, $fin, $motif]);
    }
    $lectureComplete = count($lignes) <= 50;
    $lignes = array_slice($lignes, 0, 50);
    $appartenances = [];
    if ($ligueId !== null && $lignes) {
        // Enrichir seulement les 50 identités retenues, sans tronquer leurs équipes.
        $marqueurs = implode(',', array_fill(0, count($lignes), '?'));
        $sqlEquipes = "SELECT DISTINCT aje.joueurId, te.equipe_id AS id, te.nom_equipe AS nom
            FROM abonJoueurEquipe aje
            JOIN abonEquipeLigue ael ON ael.equipeId = aje.equipeId
            JOIN TableEquipe te ON te.equipe_id = aje.equipeId
            WHERE ael.ligueId = ? AND aje.permission < 31 AND ael.permission < 31
            AND GREATEST(ael.debutAbon, aje.debutAbon, ?) <= LEAST(ael.finAbon, aje.finAbon, ?)
            AND aje.joueurId IN ($marqueurs)
            ORDER BY te.nom_equipe, te.equipe_id, aje.joueurId";
        $equipes = publicationLire($conn, $sqlEquipes, 'iss' . str_repeat('i', count($lignes)),
            [$ligueId, $debut, $fin, ...array_column($lignes, 'id')]);
        foreach ($equipes as $equipe) {
            $appartenances[(int)$equipe['joueurId']][] = ['id' => (int)$equipe['id'], 'nom' => $equipe['nom']];
        }
    }
    $elements = [];
    foreach ($lignes as $ligne) {
        $joueur = publicationJoueur($ligne);
        $joueur['equipesDansLigue'] = $appartenances[$joueur['id']] ?? [];
        $joueur['presentDansEquipeCible'] = (bool)($ligne['presentDansEquipeCible'] ?? false);
        $elements[] = $joueur;
    }
    publicationRepondre(200, ['elements' => $elements, 'lectureComplete' => $lectureComplete]);
} catch (Throwable $erreur) { publicationErreur($erreur); }
