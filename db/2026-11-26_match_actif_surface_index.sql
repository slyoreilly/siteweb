-- Commandes séparées, à appliquer seulement après SHOW INDEX et EXPLAIN.
-- Ne pas recréer un index équivalent déjà présent. Aucun index n'est présumé.
-- Ordre ascendant compatible avec un parcours inverse date DESC, match_id DESC.
CREATE INDEX idx_match_surface_date_id
    ON TableMatch (arenaId, date, match_id);

-- Recherches de début/fin par référence ; couvre aussi MIN(chrono) positif.
CREATE INDEX idx_evenement_match_code_souscode_chrono
    ON TableEvenement0 (match_event_id, code, souscode, chrono);
