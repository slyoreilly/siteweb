# Consultation du match actif par surface

`GET /api/getMatchActifParSurface.php?arenaId=42` sur l’hôte Legacy.

`arenaId` est un entier positif. Il correspond à `TableArena.arenaId`,
également renvoyé sous le nom `plateauId` par `api/getArenas.php`.

Appelez cette API en HTTPS depuis le serveur de la plateforme Web avec
`Authorization: Bearer <jeton>`. Configurez le même secret de service dans
`LEGACY_MATCH_ACTIF_TOKEN` côté PHP Legacy et dans la configuration serveur de
la plateforme. Le jeton donne accès à la consultation de toutes les surfaces.
Le proxy et le serveur HTTP doivent transmettre l’en-tête Authorization à PHP.
Les réponses JSON portent `Cache-Control: no-store`.

Voir la [configuration du jeton et le déploiement](docs_configuration_legacy_token.md)
pour le chargement privé en DEV et PROD.

## Réponses

| HTTP | `etat` | Champs supplémentaires |
| --- | --- | --- |
| 200 | `match_actif` | `arenaId`, `match` avec `matchId` et `debutMs` |
| 200 | `aucun_match_actif` | `arenaId`, `match: null` |
| 404 | `surface_inexistante` | `arenaId` |
| 500 | `erreur_consultation` | |
| 400 | `arena_id_invalide` | |
| 401 | `non_autorise` | |
| 405 | `methode_non_autorisee` | |
| 503 | `service_non_configure` | |

```json
{
  "etat": "match_actif",
  "arenaId": 42,
  "match": {
    "matchId": 183746,
    "debutMs": 1700000001000
  }
}
```

`matchId` est `TableMatch.match_id` (le `GameComId` de SyncRef).
`debutMs` est un horodatage Unix en millisecondes. Sa valeur est `null`
si aucun événement de début avec un horodatage positif n’est disponible.

## Signaux Legacy

- `TableMatch.arenaId` rattache le match à la surface. Les événements de
  `TableEvenement0` se rattachent au match par `match_event_id = matchIdRef`.
- SyncRef transmet `statut = '10'` au démarrage et `'30'` à la fin par
  `api/upsertMatch.php`. Le chemin historique peut stocker un numéro de
  période ou une valeur comme `1P`, et écrit `'F'` à la finalisation.
- Le couple `code/souscode` vaut `10/0` au démarrage et `10/10` à la fin.
  Pour ces deux événements, `chrono` porte l’horodatage. Le code `3`
  porte notamment les données d’alignement dans le chemin historique.
- Un match est actif si le Legacy a reçu le statut de démarrage ou un
  événement de début, et si aucun statut final ni événement de fin n’est
  présent. La consultation utilise le premier `chrono` positif des
  événements de début. La fin prévaut si les mises à jour arrivent séparément.
- Un match planifié existe déjà dans `TableMatch`, avec ses heures prévues
  dans `date` et `dateFin` (voir `stats2/setMatchAVenir.php`).
  Un match spontané reçoit son identifiant Legacy lorsque sa référence locale
  est créée côté Legacy par la synchronisation. Les deux suivent les mêmes
  critères une fois associés à une surface. `TSDMAJ` indique une mise à jour.
  La consultation ne dépend d’aucune fenêtre de fraîcheur.
- `api/getMatchsAVenir.php` consulte un calendrier sur une fenêtre de dates.
  `api/getMatchEnCours.php` exige déjà une référence de match, renvoie son
  sommaire et peut écrire un tag dans `Video`.

Créez une association automatique uniquement sur `200 / match_actif`.
Traitez `500` ou `503` comme une consultation
indisponible. Les changements saisis sur un appareil deviennent visibles
après leur synchronisation avec le Legacy.

## Sélection du match actif

Sur la surface demandée, sélectionner le match ouvert dont `TableMatch.date`
est passée ou égale à `CURRENT_TIMESTAMP`, avec la date la plus récente.
À date identique, le plus grand `TableMatch.match_id` gagne. Les matchs futurs
et ceux sans date sont exclus. La présence de plusieurs matchs ouverts est
normale et ne produit plus de HTTP 409.

Les critères d'ouverture restent ceux décrits ci-dessus : statut `10` ou
événement `10/0`, sans statut `30`/`F` ni événement `10/10`. Aucun filtre sur
`dateFin`, `TSDMAJ`, la durée d'ouverture ou l'activité récente n'est ajouté.
Un match ouvert depuis plusieurs jours, voire plusieurs années, reste admissible.
Le tri utilise `TableMatch.date`, jamais `debutMs`. Le contrat de `debutMs`
(premier chrono positif d'un événement de début, sinon `null`) est conservé.

### Format et fuseau horaire

Les écritures du dépôt traitent `TableMatch.date` comme une date SQL, pas
comme des millisecondes Unix :

- `api/lib/upsert_match_rules.php` convertit l'entrée en millisecondes en
  `Y-m-d H:i:s` avec `date()`, donc avec le fuseau PHP du serveur.
- `stats2/setMatchAVenir.php` écrit directement `dateDeb` et place sa session
  SQL en `+00:00`; il ne convertit pas lui-même la chaîne reçue.
- Le `php.ini` versionné indique `Europe/Berlin`. Cela ne prouve pas le
  fuseau du processus PHP de production. Aucun DDL de `TableMatch` dans ce
  dépôt ne permet de certifier le type de sa colonne `date` en production.

La requête compare directement `date <= CURRENT_TIMESTAMP`, dans le fuseau
de la session SQL Legacy. Elle ne transforme pas la colonne et conserve ainsi
la possibilité d'utiliser son index. Pour un `TIMESTAMP`, MySQL applique son
fuseau de session à la lecture et à la comparaison. Pour un `DATETIME`, la
valeur ne contient aucun fuseau : ses heures doivent correspondre à celles
de la session SQL. Il faut confirmer cette convention sur les données réelles.
Imposer UTC ou America/Toronto sans cette vérification pourrait déplacer les
heures historiques. Cette correction ne change aucun producteur de dates.

## Requête et index

La sous-requête corrélée cherche d'abord les matchs de la surface dont la
date est admissible, dans l'ordre `date DESC, match_id DESC`, et s'arrête au
premier match ouvert (`LIMIT 1`). La jointure externe conserve la distinction
entre surface inexistante et surface sans match actif. Les événements sont
consultés par des sondes scalaires corrélées `SELECT 1 ... LIMIT 1` sur la
référence du candidat pour vérifier l'ouverture/fermeture; leur `MIN(chrono)`
n'est calculé qu'après la sélection du gagnant. Aucun regroupement global
de l'historique des événements n'est nécessaire.

Les commandes proposées sont séparées dans
[`db/2026-11-26_match_actif_surface_index.sql`](db/2026-11-26_match_actif_surface_index.sql).
Elles ne sont pas exécutées par l'API :

- `(arenaId, date, match_id)` sur `TableMatch` permet la recherche par surface
  et date, puis le parcours inverse pour les deux clés de tri décroissantes.
- `(match_event_id, code, souscode, chrono)` sur `TableEvenement0` permet les
  recherches ciblées de début/fin et couvre le calcul du premier chrono positif.

Vérifiez les index existants avant toute création pour éviter les doublons.
Vérifiez aussi le type, la longueur et la collation des références de match :
l'index proposé porte sur la référence entière et suppose un type indexable
dans les limites du moteur. Aucun index ni plan d'exécution de production
n'est présumé. Sans ces accès indexés, les recherches peuvent rester lentes.
En absence de match ouvert, tous les matchs passés de la surface peuvent devoir
être examinés, puisque la règle interdit de limiter l'ancienneté.

## Vérifications minimales après déploiement

1. Vérifiez le schéma, les index et l'horloge sur la connexion de l'application :

   ```sql
   SHOW COLUMNS FROM TableMatch LIKE 'date';
   SHOW INDEX FROM TableMatch;
   SHOW INDEX FROM TableEvenement0;
   SELECT @@session.time_zone, @@global.time_zone, @@system_time_zone,
          CURRENT_TIMESTAMP, UTC_TIMESTAMP();
   ```

   Relevez aussi `date_default_timezone_get()` dans le processus PHP concerné
   (la configuration CLI seule ne suffit pas). Comparez un match récent connu
   et un match futur connu avec leurs heures prévues réelles, `TableMatch.date`
   et, lorsqu'il existe, le chrono Unix de début. Confirmez l'absence de décalage
   de fuseau; si c'est un `DATETIME`, confirmez la convention commune des écritures.
2. Faites un `EXPLAIN` de `REQUETE_MATCH_ACTIF_SURFACE` avec une surface réelle
   à la place de `?`. Vérifiez la recherche des candidats par surface/date,
   l'ordre fourni par l'index sans tri global, et les accès aux événements par
   référence/code/souscode, sans matérialisation complète de leur historique.
   Si nécessaire, faites examiner puis appliquer les index SQL séparés et
   revérifiez le plan. Leur création peut prendre des verrous et du temps.
3. Avec le jeton habituel, appelez l'API sur une surface ayant plusieurs matchs
   ouverts : HTTP 200 et le match admissible le plus récent, aucun 409. Vérifiez
   qu'un match futur ou terminé plus récent ne gagne pas. Vérifiez également
   une surface sans match actif (`match: null`) et une surface inexistante (404).
   Utilisez les données existantes, sans modifier les matchs de production.
4. Mesurez plusieurs appels HTTPS authentifiés sur une surface chargée et sur
   une surface sans match actif, puis la découverte complète de SyncStats Live.
   L'appel doit laisser assez de marge pour que **toute la découverte reste
   sous 8 secondes**. Comparez aux 20,8 secondes observées avant correction.
   Aucun temps de production ne peut être garanti par les tests locaux.

## Tests locaux

```sh
python test/unit/match_actif_surface_sql_test.py
php test/unit/match_actif_surface_test.php
php -l api/lib/match_actif_surface.php
```

Les tests SQL exécutent la requête réelle sur SQLite avec une horloge SQL figée,
sans index pour les cas métier, puis avec les index proposés pour contrôler le
plan local. Ils couvrent les sept situations métier demandées, la borne exacte
de l'heure actuelle, les critères historiques, `debutMs`, les surfaces et les
dates absentes. Ils ne remplacent pas la validation du plan MySQL en production.
Le test PHP vérifie le contrat des réponses, dont l'absence de conflit 409.
