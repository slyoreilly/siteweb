-- Lecture seule, à exécuter dans la base Legacy sélectionnée par votre client SQL.
-- Aucune validation de production n'a été effectuée depuis le dépôt.
SHOW TABLE STATUS WHERE Name IN ('Ligue', 'TableSaison', 'TableEquipe', 'TableJoueur',
  'abonEquipeLigue', 'abonJoueurEquipe', 'abonJoueurLigue', 'sync_inbox',
  'sync_publication_operations', 'sync_publication_cles', 'sync_publication_sous_operations');

SHOW CREATE TABLE Ligue;
SHOW INDEX FROM Ligue;
SHOW CREATE TABLE TableSaison;
SHOW INDEX FROM TableSaison;
SHOW CREATE TABLE TableEquipe;
SHOW INDEX FROM TableEquipe;
SHOW CREATE TABLE TableJoueur;
SHOW INDEX FROM TableJoueur;
SHOW CREATE TABLE abonEquipeLigue;
SHOW INDEX FROM abonEquipeLigue;
SHOW CREATE TABLE abonJoueurEquipe;
SHOW INDEX FROM abonJoueurEquipe;
SHOW CREATE TABLE abonJoueurLigue;
SHOW INDEX FROM abonJoueurLigue;
SHOW CREATE TABLE sync_inbox;
SHOW INDEX FROM sync_inbox;

-- Exécuter ces six commandes SEULEMENT si SHOW TABLE STATUS révèle ces tables.
-- Ces noms appartiennent à la proposition v2 ; aucune migration n'est appliquée.
-- SHOW CREATE TABLE sync_publication_operations;
-- SHOW INDEX FROM sync_publication_operations;
-- SHOW CREATE TABLE sync_publication_cles;
-- SHOW INDEX FROM sync_publication_cles;
-- SHOW CREATE TABLE sync_publication_sous_operations;
-- SHOW INDEX FROM sync_publication_sous_operations;
