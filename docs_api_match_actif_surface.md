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

La sélection compare directement `date` à une borne `CURRENT_TIMESTAMP` lue
une seule fois dans le fuseau de la session SQL Legacy. Elle ne transforme pas
la colonne et conserve ainsi
la possibilité d'utiliser son index. Pour un `TIMESTAMP`, MySQL applique son
fuseau de session à la lecture et à la comparaison. Pour un `DATETIME`, la
valeur ne contient aucun fuseau : ses heures doivent correspondre à celles
de la session SQL. Il faut confirmer cette convention sur les données réelles.
Imposer UTC ou America/Toronto sans cette vérification pourrait déplacer les
heures historiques. Cette correction ne change aucun producteur de dates.

## Sélection séquentielle et performance

Les mesures de production fournies après la première correction sont : plus de
30 secondes pour l'appel authentifié, contre 5,9 ms pour les 20 derniers matchs
sur la surface 1 et 51,2 ms pour les événements de code 10 du match 24053.
Ces mesures rendent pertinente la séparation des lectures. Elles ne prouvent
pas à elles seules le plan coûteux précis de la requête corrélée, ni le temps
HTTP qu'obtiendra la nouvelle version.

L'API réutilise la connexion `$conn` et procède ainsi :

1. Vérifier la surface et lire `CURRENT_TIMESTAMP` une seule fois pour conserver
   la même borne temporelle pendant tout le parcours. Une surface inexistante
   conserve sa réponse 404.
2. Lire un lot de 20 candidats, avec `date <=` cette borne et sans les statuts
   finaux `30`/`F`, dans l'ordre `date DESC, match_id DESC`. Le résultat du lot
   est entièrement lu et la requête fermée avant de consulter les événements.
   Cela permet d'utiliser la même connexion MySQL sans résultat en attente.
3. Pour chaque candidat, exécuter la même requête préparée sur ses seuls
   événements de code 10, liés par `match_event_id = matchIdRef`. Elle détermine
   la présence de `10/0`, celle de `10/10`, et le premier chrono positif de début.
   La fin prévaut; sinon le statut de démarrage ou l'événement de début suffit.
4. Retourner immédiatement le premier candidat ouvert. Aucun événement des
   candidats suivants n'est consulté. S'il n'y a aucun gagnant dans le lot,
   poursuivre strictement après la dernière paire `(date, match_id)` visitée,
   jusqu'à épuisement des candidats. Il n'y a ni `OFFSET`, ni plafond de lots,
   ni limite totale de candidats, ni limite d'ancienneté.

Le lot de 20 borne uniquement la mémoire et le transfert de chaque lecture;
ce n'est pas une restriction métier. Le curseur par date puis identifiant évite
les doublons et les omissions entre lots, même à dates identiques. L'ensemble
ne comporte plus de sous-requête corrélée ni de jointure avec les événements.
Pour un gagnant dans le premier lot, il y a une lecture de surface, une lecture
de candidats et une lecture d'événements par candidat réellement visité.

Dans le cas observé, le match 24054 (`F`) est exclu dès la lecture des candidats.
Le match 24053 (`10`, événement de début, aucun événement de fin) est sélectionné
si aucun autre match ouvert plus récent ne le supplante.

Aucune nouvelle migration ni création d'index n'accompagne cette correction.
Le fichier SQL d'index proposé lors de la correction précédente reste une
proposition historique, sans exécution automatique. Les mesures fournies ne
justifient pas d'imposer des index supplémentaires. En absence de match ouvert,
tous les candidats passés de la surface peuvent devoir être vérifiés : cela
reste nécessaire pour préserver la règle sans limite d'ancienneté.

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
2. Si les temps restent élevés, faites un `EXPLAIN` des requêtes simples
   `REQUETE_CANDIDATS_MATCH_ACTIF` (avec `ORDRE_CANDIDATS_MATCH_ACTIF`, puis
   `SUITE_CANDIDATS_MATCH_ACTIF` avant l'ordre pour un lot suivant) et
   `REQUETE_EVENEMENTS_MATCH_ACTIF`, avec des paramètres réels. Vérifiez les
   accès par surface/date et par référence/code. N'appliquez aucune migration
   sur la seule base du temps de l'ancienne requête corrélée.
3. Avec le jeton habituel, appelez l'API sur une surface ayant plusieurs matchs
   ouverts : HTTP 200 et le match admissible le plus récent, aucun 409. Pour la
   surface 1, le résultat attendu est 24053 tant que les données décrites restent
   inchangées et qu'aucun match ouvert plus récent n'est apparu. Vérifiez
   qu'un match futur ou terminé plus récent ne gagne pas. Vérifiez également
   une surface sans match actif (`match: null`) et une surface inexistante (404).
   Utilisez les données existantes, sans modifier les matchs de production.
4. Mesurez plusieurs appels HTTPS authentifiés sur une surface chargée et sur
   une surface sans match actif, puis la découverte complète de SyncStats Live.
   L'appel doit laisser assez de marge pour que **toute la découverte reste
   sous 8 secondes**. Comparez aux dépassements de 30 secondes observés.
   Aucun temps de production ne peut être garanti par les tests locaux.

## Diagnostic temporaire de la requête lente

Le marqueur de cette instrumentation est `match-actif-diag-v1`. Chaque appel
à `error_log()` écrit immédiatement une étape, sans attendre la réponse HTTP
ni une fonction de fin de requête. `debut=...` identifie le début de la requête
en secondes Unix; `+... ms` mesure le temps depuis l'entrée dans le script avec
`microtime(true)`. Regroupez les lignes ayant le même `debut` pour ne pas mélanger
des appels simultanés. Aucun jeton, en-tête, mot de passe ou contenu d'environnement
n'est ajouté aux traces.

Les repères couvrent l'authentification, le chargement de `defenvvar.php`, la
connexion (ou sa vérification/reconnexion), la lecture de surface, la préparation
des événements, chaque lot de candidats, les identifiants de chaque candidat
visité, chaque lecture d'événements, le match sélectionné (ou `aucun`) et l'envoi
de la réponse, y compris les réponses d'erreur. Les traces sont actives uniquement
lorsque le point d'entrée définit `tracerMatchActif()`.

Attention au futur déploiement : `scriptsphp/defenvvar.php` est ignoré par Git et
exclu du script de transfert. Le fichier local contient les repères de connexion;
ils figurent aussi dans `scriptsphp/defenvvar.sample.php`. Reportez uniquement ces
appels à `tracerMatchActif()` dans le fichier réel de l'hébergement, autour des
connexions existantes. Ne remplacez pas sa configuration par celle du modèle.
Sans ce report, `avant_defenvvar`/`apres_defenvvar` restent visibles, mais ne
permettent pas d'isoler la connexion MySQL.

Pour consulter les traces sans SSH après votre déploiement :

1. Ouvrez le gestionnaire de fichiers du panneau d'hébergement ou votre client
   FTPS habituel, à la racine du site (le transfert utilise `/public_html` par
   défaut). Recherchez `api/error_log`, puis `error_log` à la racine du site.
   Ce sont des emplacements possibles, pas un chemin de production confirmé.
2. Depuis votre client habituel, lancez l'appel authentifié
   `/api/getMatchActifParSurface.php?arenaId=1`, en conservant le jeton hors des
   journaux. Actualisez ou retéléchargez le journal pendant l'appel, puis après
   l'expiration. Recherchez `match-actif-diag-v1`.
3. Si ces fichiers ne contiennent pas le marqueur, consultez la destination PHP
   `error_log` dans le panneau. Sous cPanel, ouvrez **Logiciel → Éditeur INI
   MultiPHP**, sélectionnez le domaine Legacy et recherchez `error_log` (au
   besoin dans **Mode éditeur**). Ouvrez ou téléchargez ce fichier via le
   gestionnaire de fichiers/FTPS; utilisez le chemin configuré, plutôt qu'un
   emplacement présumé. La [documentation cPanel](https://docs.cpanel.net/cpanel/software/multiphp-ini-editor-for-cpanel/)
   décrit cette interface. Le dépôt ne confirme ni le panneau ni la valeur
   effective en production. Si le chemin est vide, `syslog`, inaccessible ou
   imposé par PHP-FPM, demandez au support de l'hébergement l'accès au journal
   PHP du domaine pour l'heure de l'appel et le marqueur ci-dessus. Aucun SSH
   ni script `phpinfo()` public n'est nécessaire.
4. Comparez les temps des paires `avant_...`/`apres_...`. Une paire incomplète
   désigne l'opération en cours à la dernière trace; elle ne prouve pas à elle
   seule la cause interne. Un délai côté client de 30 secondes peut laisser
   PHP poursuivre : consultez aussi les lignes arrivées après l'expiration.
   Sans `entree_script`, vérifiez d'abord la version déployée et la destination
   du journal; aucune étape PHP ne peut alors être incriminée.

Retirez cette instrumentation après le diagnostic. Aucun commit, transfert ou
déploiement n'a été effectué pour l'ajouter.

## Tests locaux

```sh
python test/unit/match_actif_surface_sql_test.py
php test/unit/match_actif_surface_test.php
php -l api/lib/match_actif_surface.php
php -l api/getMatchActifParSurface.php
```

Les tests SQL exécutent les requêtes réelles sur SQLite avec une horloge SQL
figée et reproduisent leur parcours par lots. Ils couvrent les sept situations
métier, les critères historiques, `debutMs`, le cas 24054/24053, l'arrêt des
lectures d'événements au gagnant, plus de trois lots, les égalités entre lots,
un changement de date au curseur et l'épuisement après des lots complets.
Ils ne mesurent ni MySQL ni le temps HTTP de production.

Le test PHP vérifie le contrat des réponses et exerce directement la fonction
de sélection : arrêt immédiat (y compris un générateur qui échoue s'il est
repris), absence de plafond total, fin prioritaire et ouverture par statut ou
événement. Ces tests ne nécessitent pas de base de production.
