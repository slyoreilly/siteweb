-- À exécuter par l'utilisateur sur un environnement Legacy autorisé.
-- Variables de session seulement ; aucune écriture métier.
SET @ligueId = 314, @saisonId = 42, @equipeId = 2500, @joueurId = 9001;
SET @debut = '2026-01-01 00:00:00', @fin = '2026-12-31 23:59:59';
-- Même échappement que la route : ! -> !!, % -> !%, _ -> !_.
SET @motif = '%O''Neil%';
START TRANSACTION READ ONLY;

SHOW COLUMNS FROM abonJoueurEquipe;
SHOW COLUMNS FROM abonEquipeLigue;
SHOW COLUMNS FROM TableEquipe;
SHOW COLUMNS FROM TableJoueur;
SELECT premierMatch, dernierMatch FROM TableSaison
WHERE ligueRef = @ligueId AND saisonId = @saisonId;

-- Contrôle cible : au moins une ligne, sinon HTTP 422 attendu.
SELECT equipeId FROM abonEquipeLigue
WHERE ligueId = @ligueId AND equipeId = @equipeId AND permission < 31
AND debutAbon <= @fin AND finAbon >= @debut;

-- Même sélection et même tri que la route. La 51e ligne n'est pas rendue.
-- Pour l'absence de cible : SET @equipeId = NULL avant la transaction.
SELECT tj.joueur_id AS id, tj.NomJoueur AS nom,
    tj.NumeroJoueur AS numeroChandail, tj.position, contexte.presentDansEquipeCible
FROM TableJoueur tj
JOIN (SELECT aje.joueurId,
    MAX(CASE WHEN aje.equipeId = @equipeId THEN 1 ELSE 0 END) AS presentDansEquipeCible
    FROM abonJoueurEquipe aje
    JOIN abonEquipeLigue ael ON ael.equipeId = aje.equipeId
    WHERE ael.ligueId = @ligueId AND aje.permission < 31 AND ael.permission < 31
    AND GREATEST(ael.debutAbon, aje.debutAbon, @debut) <= LEAST(ael.finAbon, aje.finAbon, @fin)
    GROUP BY aje.joueurId) contexte ON contexte.joueurId = tj.joueur_id
WHERE tj.NomJoueur LIKE @motif ESCAPE '!'
ORDER BY contexte.presentDansEquipeCible DESC, tj.NomJoueur, tj.joueur_id LIMIT 51;

-- Appartenances distinctes attendues pour un candidat retenu.
SELECT DISTINCT aje.joueurId, te.equipe_id AS id, te.nom_equipe AS nom
FROM abonJoueurEquipe aje
JOIN abonEquipeLigue ael ON ael.equipeId = aje.equipeId
JOIN TableEquipe te ON te.equipe_id = aje.equipeId
WHERE ael.ligueId = @ligueId AND aje.permission < 31 AND ael.permission < 31
AND GREATEST(ael.debutAbon, aje.debutAbon, @debut) <= LEAST(ael.finAbon, aje.finAbon, @fin)
AND aje.joueurId = @joueurId
ORDER BY te.nom_equipe, te.equipe_id, aje.joueurId;

-- Détail explicatif, y compris les paires SANS période commune.
-- Plusieurs lignes ici ne doivent pas dupliquer les équipes ci-dessus.
SELECT aje.abonJouEq, aje.joueurId, aje.equipeId,
    aje.permission AS permissionJoueur, ael.permission AS permissionEquipe,
    aje.debutAbon AS debutJoueur, aje.finAbon AS finJoueur,
    ael.debutAbon AS debutEquipe, ael.finAbon AS finEquipe,
    GREATEST(ael.debutAbon, aje.debutAbon, @debut) <= LEAST(ael.finAbon, aje.finAbon, @fin) AS periodeCommune
FROM abonJoueurEquipe aje
JOIN abonEquipeLigue ael ON ael.equipeId = aje.equipeId
WHERE ael.ligueId = @ligueId AND aje.joueurId = @joueurId
ORDER BY aje.equipeId, aje.abonJouEq, ael.debutAbon;

ROLLBACK;
