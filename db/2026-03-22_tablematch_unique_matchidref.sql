-- Objectif: renforcer l'idempotence des creations de match.
-- IMPORTANT: executer d'abord la requete de diagnostic ci-dessous.

-- 1) Diagnostic doublons existants
-- SELECT matchIdRef, COUNT(*) AS nb
-- FROM TableMatch
-- WHERE matchIdRef IS NOT NULL AND matchIdRef <> ''
-- GROUP BY matchIdRef
-- HAVING COUNT(*) > 1;

-- 2) (Optionnel) Nettoyage manuel/controle des doublons avant contrainte
-- Conserver la ligne canonique (ex: MIN(match_id) ou selon vos regles metier),
-- puis supprimer/merger les autres.

-- 3) Contrainte d'unicite logique sur matchIdRef
ALTER TABLE TableMatch
ADD UNIQUE KEY uq_tablematch_matchidref (matchIdRef);

