# Destinataire Legacy — préparation du 27 novembre 2026

## État de livraison

**Première publication non autorisable à ce stade.** Aucune création métier,
migration, livraison, modification Backend/SyncUp, opération de push ou commit.
La vérification exacte et le blocage HTTP sont implémentés ; le moteur d'écriture,
le stockage des reçus et la reprise ne le sont pas. Il ne s'agit pas d'un
destinataire opérationnel de publication.

Contrat Backend lu intégralement avant modification :
`../backend/backend/docs/syncup/contrats/workflow_publication_controlee_legacy.md`,
daté du 27 novembre 2026. Les records de
`PublicationControleeLegacy.cs` et le port de
`PublicationControleeLegacy.Lecture.cs` ont également été consultés en lecture seule.

## Diagnostic ciblé des moteurs

| Table | Preuve locale | Conclusion permise |
| --- | --- | --- |
| `TableEquipe`, `TableJoueur` | Requêtes PHP ; documentation `base_de_donnees_syncstats.md`, §1 et points d'attention | Moteur historique MyISAM signalé globalement, DDL individuel absent |
| `abonEquipeLigue`, `abonJoueurEquipe`, `abonJoueurLigue` | Même documentation ; `audit_affiliations_multiples_legacy.md`, §1 et §10 | Multiplicité représentable ; moteurs et contraintes déployés inconnus |
| `Ligue`, `TableSaison` | Routes GET existantes et documentation | Moteurs inconnus ; nécessaires pour destination et révision |
| `sync_inbox` | `db/20260316_create_sync_inbox.sql` | DDL local InnoDB, PK `syncInboxId`, UNIQUE `(endpoint,dedupeKey)` ; déploiement non attesté |
| Clés et reçus de publication | Aucun DDL existant identifié | Tables nouvelles à proposer ; l'ACK inbox n'est pas un reçu métier |

L'audit du 26 novembre précise lui-même qu'aucun dump complet ni base vivante
n'est disponible. Il ne permet pas d'affirmer individuellement que chaque table
est encore MyISAM aujourd'hui. Aucun accès à une base déployée n'a été effectué.
Les seules transactions `READ ONLY` des GET ne rendent pas MyISAM transactionnel.

**Stratégie retenue : blocage de v1, proposition d'opérations récupérables v2.**
Le lot atomique v1 n'est pas démontrable avec les preuves disponibles. Aucun
writer incompatible n'est implémenté avant accord contractuel. Si les commandes
de schéma prouvent finalement que toutes les tables et effets de triggers sont
transactionnels, revenir à v1 est possible, avec une implémentation distincte
de transaction et des tests d'interruption réels. Aucune conversion automatique.

## Fichiers et contrat actuellement exécutable

- `api/lib/publication_lecture.php` : extraction du Bearer existant dans
  `publicationAuthentifier()`, sans changement de secret ni de droits.
- `api/sync/publication-equipe.php` : GET et POST authentifiés ; **503
  `ECRITURES_BLOQUEES` systématique**, avant toute connexion SQL, sans parsing
  métier ni inbox. 401 si Bearer absent/invalide, 503 si secret absent,
  405 et `Allow: GET, POST` pour les autres méthodes.
- `api/getPublicationVerification.php` et `api/lib/publication_verification.php` :
  lecture exacte décrite ci-dessous.
- `api/lib/publication_commande.php` : validation préparatoire du transport,
  non raccordée à une écriture. Corps au plus 1 Mio, clé de 1 à 128 unités UTF-16
  hors chaîne blanche, empreinte hexadécimale majuscule de 64 caractères,
  `planJson` chaîne. SHA-256 sur ses octets UTF-8 exacts **avant parsing**.
  Exige `Version=publication-equipe-v1`. Ce validateur n'est pas une validation
  complète des règles métier ni une autorisation de publier.
- `test/sql/publication_controlee_schema.sql` : commandes de schéma en lecture seule.
- Tests : `test/unit/publication_destinataire_test.php` et
  `test/unit/publication_verification_sql_test.py`, plus
  `test/unit/publication_verification_mariadb_test.py` pour le dialecte réel sur
  conteneur jetable sans réseau, sans ports ni volume persistant.

Les réponses conservent JSON UTF-8 et `Cache-Control: no-store`. La consultation
du reçu renvoie aussi 503 : annoncer 404 serait affirmer une absence que le
stockage non implémenté ne peut pas prouver. Elle ne renvoie jamais un ACK de
transport comme reçu. Les états terminée/en cours/absente/conflit ne sont donc
**pas disponibles** avant implémentation du contrat approuvé.

## Vérification par identifiants exacts

```http
GET /api/getPublicationVerification.php?ligueId=1&saisonId=2&equipeId=10&joueurIds=7,8
Authorization: Bearer <secret interservices existant>
```

Pour préparer une création d'équipe, remplacer `equipeId=10` par
`creerEquipe=1`. Les deux sont exclusifs. `joueurIds` est obligatoire : chaîne
CSV de 0 à 100 IDs positifs distincts, au plus 2147483647, sans espace ni zéro
initial. La chaîne vide signifie aucune identité Legacy à confirmer, par exemple
pour des créations explicitement décidées dans le futur plan ; elle n'autorise
elle-même aucune création. Ligue et saison sont obligatoires. L'instance vient
uniquement de `LEGACY_PUBLICATION_INSTANCE` fourni par le serveur (1 à 80 octets,
sans valeur par défaut), jamais d'une URL ou d'un paramètre navigateur.

Le secret `LEGACY_MATCH_ACTIF_TOKEN` et le chargeur privé existants sont réutilisés.
La route GET utilise le même contrôle d'accès que les autres lectures. Le futur
adaptateur Backend doit conserver son contrôle d'administrateur global actif,
sa liste blanche d'instances/origines et ne jamais exposer ce secret au navigateur.

Une seule instruction préparée `SELECT … UNION ALL …` retourne :

- La ligue et la saison exacte de cette ligue, leurs noms/dates utiles.
- L'équipe cible et ses abonnements équipe-ligue, toutes périodes et permissions.
- Les identités demandées par `TableJoueur.joueur_id`, même hors ligue/cible,
  avec nom et attributs globaux ; aucun contact, HCR, date de naissance ou UUID canonique.
- Toutes leurs lignes `abonJoueurEquipe` et `abonJoueurLigue`, y compris anciennes
  ou non admissibles, et tous les abonnements ligue des équipes correspondantes.

Aucune limite à 50 ni déduction nominative. Le filtre ne sélectionne pas les
affiliés non demandés. Les périodes multiples sont conservées, sans `DISTINCT`
qui cacherait des doublons. `abonJoueurLigue` est lu pour les autres affiliations,
sans être une condition d'appartenance à l'effectif cible et sans être écrit.

La réponse 200 contient :

```text
version: "verification-identifiants-v1"
instance, ligueId, saisonId, equipeId (null pour création)
revision: SHA-256 hexadécimal majuscule opaque
valide: booléen
lectureComplete: true
ecrituresAutorisees: false
identites: [{id, existe, autresAffiliations, inscriptionsCibles: [abonJouEq, ...]}]
anomalies: [code, ...]
donnees: {ligue: [], saison: [], equipe: [], joueur: [],
          joueurEquipe: [], equipeLigue: [], joueurLigue: []}
```

`lectureComplete=true` signifie que le SELECT réussi n'est pas tronqué, y compris
quand une identité demandée est absente (`existe=false`, `valide=false`). Une erreur
SQL/sérialisation donne 503 `erreur_consultation`, jamais une liste vide complète.
Les paramètres invalides donnent 400 ; instance non configurée : 503
`instance_non_configuree`. Une destination invalide retourne 200 `valide=false`
avec les données diagnostiques, pas un succès de publication.

`valide` exige une ligue unique, une saison unique de cette ligue aux dates
valides non nulles/non inversées, chaque identité exacte unique et, pour une
équipe existante, une équipe unique avec exactement un abonnement admissible
dans la ligue/période. Deux abonnements compatibles sont refusés. Deux
inscriptions joueur compatibles sont refusées. Zéro inscription joueur cible
est admissible pour préparer un ajout. Les intersections sont inclusives et
communes aux périodes saison/joueur/équipe ; permission non nulle `<31`.
Les autres équipes de la saison et les autres ligues sont signalées sans
modifier aucune ligne. Une même équipe inscrite dans une autre ligue est aussi
détectée. Les lignes brutes permettent une analyse plus stricte par le Backend.

La révision est calculée après tri des clés de chaque objet et des lignes de
chaque groupe (doublons conservés), sur le JSON PHP de
`[version,instance,ligueId,saisonId,equipeId,idsTries,groupes]`, UTF-8 non échappé,
slashes non échappés. Backend doit traiter ce jeton comme opaque. Il couvre
dates, permissions, inscriptions et attributs globaux des IDs sélectionnés.
L'instruction unique évite les lectures applicatives successives incohérentes ;
les garanties précises de verrouillage MyISAM/instantané InnoDB et la charge
restent à valider sur le moteur réel. **Ce jeton de lecture ne fournit pas encore
une précondition atomique d'écriture.** Il ne couvre pas des clés techniques
qui n'existent pas encore ; il faudra étendre sa version avec le futur stockage.

Adaptation du port Backend, non effectuée ici : vérifier l'instance et les trois
IDs de contexte, refuser version inconnue, HTTP incomplet, données malformées,
`lectureComplete != true`, `valide != true` et toute identité absente/dupliquée.
Mapper `revision` vers `VerificationDestinationLegacy.Revision`, `valide` vers
`Valide`, `lectureComplete` vers `Complete`, et chaque paire
`(id,autresAffiliations)` vers `IdentiteCibleLegacy`. Préserver les anomalies et
les périodes pour le diagnostic. Ne pas déduire une disponibilité d'écriture
de ce résultat ; `ecrituresAutorisees` reste false.

## Demande de changement Backend — proposition v2 à approuver

Cette section est une proposition précise, **pas un protocole activé**. Owner
destinataire : Legacy ; owner adaptation du journal et du port : Backend.
Maintenir v1 bloquée si MyISAM est confirmé. Obtenir un accord Backend explicite
avant d'implémenter les points ci-dessous.

### Pourquoi un journal seul ne suffit pas

Enregistrer une intention InnoDB, créer une identité MyISAM puis enregistrer
son ID laisse une fenêtre de panne entre les deux dernières instructions.
À la reprise, le nom ne permet pas de distinguer création réussie et absence.
Un verrou consultatif, une transaction PHP ou `sync_inbox` ne résolvent pas
cette fenêtre. Il faut une preuve unique dans **la même instruction métier**
que la création, ou une procédure qui bloque pour réconciliation manuelle.

### Migrations proposées, jamais appliquées

Ajouter une colonne technique nullable `syncCreationPublication BINARY(32)` et
son index UNIQUE, sans toucher le moteur, à `TableEquipe`, `TableJoueur`,
`abonEquipeLigue` et `abonJoueurEquipe`. Les lignes historiques gardent NULL.
Ce sont des condensats de clés opaques de déduplication ; aucun HCR ni UUID
canonique brut dans `TableJoueur`. Exemple à adapter après `SHOW CREATE TABLE` :

```sql
-- PROPOSITION SEULEMENT, ne pas appliquer dans cette mission.
ALTER TABLE TableJoueur ADD COLUMN syncCreationPublication BINARY(32) NULL,
  ADD UNIQUE KEY uq_joueur_creation_publication (syncCreationPublication);
-- Même colonne sur TableEquipe, abonEquipeLigue, abonJoueurEquipe,
-- avec noms d'index respectifs uq_equipe_creation_publication,
-- uq_equipe_ligue_creation_publication, uq_joueur_equipe_creation_publication.
```

Créer trois tables **InnoDB** proposées :

| Table | Clés et données obligatoires |
| --- | --- |
| `sync_publication_operations` | PK `operationId BIGINT` ; UNIQUE binaire `(instance,cleIdempotence)` ; empreinte immuable BINARY(32), planJson exact MEDIUMBLOB, état, reçu final, timestamps ; référence à `sync_inbox.syncInboxId` unique |
| `sync_publication_cles` | PK `(instance,type,cleOpaque)` ; ID Legacy, hash des paramètres de création ; unicité inverse identité `(instance,type,legacyId)` pour les joueurs ; type/destination gouvernés pour les équipes |
| `sync_publication_sous_operations` | PK `(operationId,cleSousOperation)` ; type, dépendances, hash paramètres immuable, état `preparee/appliquee/conflit`, ID métier, état avant/après, reçu JSON et dates |

Les collations des clés doivent être binaires/sensibles à la casse ; aucune
expiration de ces preuves. Les tailles/index et le lien `operationId` doivent
être figés dans le DDL approuvé après inspection du schéma. L'inbox garde son
unicité `(endpoint,dedupeKey)` ; un ACK n'est jamais utilisé comme reçu final.
Le futur writer n'appelle aucun transport ACK dans la section critique.

Les colonnes techniques UNIQUE rendent les INSERT répétés reconnaissables
malgré une panne avant journalisation du reçu. Elles ne rendent pas MyISAM
transactionnel et ne garantissent pas sa survie à une corruption après crash
machine. Cette limite de durabilité doit être explicitement acceptée ou
conduire au refus de v2 et au choix d'une migration transactionnelle gouvernée
hors mission. Un simple arrêt de worker et un crash serveur sont deux tests distincts.

### Clés, sous-opérations et concurrence

Conserver `CleIdentite` et `CleEquipe` définies par Backend ; aucun rapprochement
HCR/nominatif. Pour la colonne technique, SHA-256 sur les octets UTF-8 de
`type + NUL + instance + NUL + cleOpaque` (NUL interdit dans les composants).
Les namespaces sont `identite`, `equipe`, `equipe-ligue`, `joueur-equipe`.
Pour les inscriptions, `cleOpaque` est le tuple décimal séparé par `:` :
`equipeId:ligueId:saisonId` ou `joueurId:equipeId:ligueId:saisonId`.
Ces clés d'inscription sont indépendantes de la clé de commande et communes
aux commandes concurrentes visant la même inscription.

Ordre des sous-opérations : équipe explicite/retrouvée, abonnement équipe-ligue
si équipe créée, pour chaque sélection identité explicite/retrouvée, inscription
cible, attributs globaux non nuls, puis reçu complet. Une équipe existante sans
abonnement compatible est refusée. Un seul abonnement compatible est réutilisé
sans toucher ses dates ; plusieurs périodes donnent conflit. Les nouvelles
bornes sont exactement celles de la saison relue. Aucune inscription dans A
n'est close lors d'une publication B. Aucun affilié non sélectionné n'est traité.
`abonJoueurLigue` n'est pas nécessaire au contrat d'effectif ciblé et ne sera pas
créée par ce premier writer ; toute exigence d'un autre consommateur doit être
arbitrée avant activation. Les valeurs de permission de création restent **à
valider par le propriétaire métier** ; le seuil de lecture `<31` n'autorise pas
à inventer une valeur d'écriture.

Sérialiser les publications d'une instance avec un verrou de session serveur,
puis réserver chaque clé et vérifier les contraintes uniques. Un worker perdant
son verrou s'arrête. Pour se protéger aussi des writers historiques, la section
de vérification/création doit disposer d'un verrouillage des tables réellement
compatible avec MyISAM, les tables InnoDB, les triggers et l'hébergement, ou
d'une fenêtre de maintenance interdisant les autres écritures sur la ligue de
test. Un verrou consultatif seul ne protège pas des scripts historiques. Ces
préconditions et le coût des verrous sont à valider avant implémentation.

Les attributs globaux non nuls exigent des UPDATE conditionnels comparant les
valeurs avant/après attendues (NULL avec comparaison SQL null-safe). Une reprise
ne réapplique pas aveuglément une ancienne valeur sur une modification récente.
Si le résultat observé ne correspond ni à l'état attendu ni à l'état après,
marquer conflit et arrêter. Le numéro/position null est toujours préservé.

### Consultation, reçu et reprise proposés

Version du plan proposée : `publication-equipe-v2-recuperable` ; sérialisation
.NET par défaut et empreinte des octets inchangées. Ne jamais accepter cette
version dans le validateur v1 actuel. Routes inchangées. Ajouter à GET le
paramètre facultatif `empreinte` pour permettre une comparaison de la clé.

| Réponse proposée | Sémantique |
| --- | --- |
| 200 | Reçu métier final complet, même forme PascalCase que `RecuPublicationLegacy` v1, `Termine=true`, mêmes IDs exacts par sélection |
| 202 | `{etat:"en_cours" ou "partielle", cleIdempotence, empreinte, sousOperations:[{cleSousOperation,type,etat,empreinteParametres,legacyId,inscriptionId}]}` ; chaque reçu confirme seulement sa sous-opération |
| 404 | Aucune intention durable pour cette clé ; aucune écriture autorisée sans intention préalable |
| 409 | Clé/autre empreinte, révision périmée avant première opération, mapping contradictoire, période ambiguë ou état divergent ; aucun reçu final inventé |
| 503 | Stockage indisponible, preuve illisible ou incohérente ; résultat incertain |

Dans une sous-opération, `cleSousOperation` est une chaîne stable dans le plan,
`type` vaut `equipe`, `equipe-ligue`, `identite`, `joueur-equipe` ou `attributs` ;
`etat` vaut `preparee`, `appliquee` ou `conflit`. `empreinteParametres` est le
SHA-256 majuscule des paramètres exacts persistés avant exécution. Les IDs sont
nuls avant application ; `legacyId` est l'ID équipe/joueur selon le type,
`inscriptionId` est positif uniquement pour une inscription appliquée.
Une opération d'attributs confirme l'ID joueur ; ses valeurs avant/après restent
dans le journal Legacy. La liste conserve toutes les étapes préparées,
y compris celles non appliquées. Seul le reçu final autorise les mappings.

1. Backend consulte d'abord GET avec la clé et l'empreinte persistées.
2. Si 200 : valider intégralement le reçu puis finaliser les correspondances.
3. Si 202 : conserver le journal préparé ; ne finaliser aucun mapping partiel.
   Réémettre seulement le **plan exact initial** avec la même clé/empreinte.
   La route reprend les sous-opérations, pas une nouvelle publication.
4. Pour chaque intention préparée sans reçu, retrouver la ligne par sa clé
   technique UNIQUE ; comparer ID/paramètres/périodes attendus. Si conforme,
   reconstruire le reçu ; si absente, réexécuter l'INSERT portant la même clé ;
   si divergente, 409 et réconciliation administrative. Ne jamais rechercher par nom.
5. Ne comparer la révision initiale qu'avant tout effet. Après un effet déjà
   prouvé, contrôler les préconditions par sous-opération et les effets attendus :
   une révision globale modifiée par notre opération ne signifie pas absence.
6. Retourner le reçu complet durable seulement après toutes les confirmations.
   Réponse perdue après succès : GET retourne exactement ce reçu. Aucun rollback
   fictif ni suppression compensatoire des créations partielles.

**Adaptations Backend nécessaires** : reconnaître 202 partiel, garder le plan
exact et les reçus d'étape, reprendre avant une nouvelle prévisualisation,
conserver les IDs encore non finalisés sans les publier dans les mappings
canoniques, gérer la divergence avec réconciliation administrative. Le chemin
actuel « aucun reçu final, relire la révision initiale puis réémettre » ne suffit
pas après une création partielle. Le reçu final et les règles de validation des
IDs restent identiques. Aucun changement Backend n'a été appliqué ici.

## Validation locale et scénarios restants

Tests exécutés : **59 vérifications PHP destinataire**, **13 vérifications PHP
du socle existant**, **31 scénarios PHP de recherche**, **8 tests SQLite de
vérification exacte** et **9 tests SQLite de recherche** réussis. Syntaxe des
cinq fichiers PHP ajoutés/modifiés vérifiée ; `git diff --check` sans erreur.
**8 consultations MariaDB 10.6** réussies sur les schémas fictifs MyISAM et
InnoDB ; le conteneur de test a été arrêté et supprimé automatiquement.
Les tests PHP utilisent `php:8.3-cli`, dépôt monté en lecture seule, réseau
désactivé ; les tests SQL unitaires utilisent SQLite en mémoire. Ils
exercent les fonctions et le SELECT de production, pas une base déployée.
Le test MariaDB supplémentaire utilise deux schémas fictifs, l'un entièrement
MyISAM et l'autre entièrement InnoDB ; il compare le SELECT aux mêmes fixtures.
Il ne valide aucun schéma déployé ni les garanties d'un futur writer.

| Scénario demandé | Couverture de cette livraison |
| --- | --- |
| Équipe existante / création équipe | Lecture exacte / préparation sans équipe ; aucune création exécutée |
| Joueur cible, autre équipe même ligue, autre ligue | Lecture complète et signalement testés |
| Identité explicitement créée | Absence distinguée d'une autorisation ; writer différé |
| Affilié sélectionné / non sélectionné | IDs exacts et exclusion de l'ID non demandé testés |
| Répétition / révision modifiée / périodes multiples | Stabilité et changement de révision, ambiguïtés testés en lecture |
| Même clé/autre empreinte | Vérification des octets et refus d'empreinte testés ; conflit durable différé |
| Non autorisé | 401 et blocage 503 testés sur la route réelle, sans connexion SQL |
| Deux commandes concurrentes | Writer différé ; test d'exclusion et de contraintes uniques à réaliser après accord |
| Panne après équipe/joueur/inscription | Writer différé ; interruption réelle après chaque INSERT et avant chaque reçu à tester |
| Réponse perdue / reprise / reçu partiel | Stockage différé ; tests impossibles honnêtement avant le protocole approuvé |
| Révision Legacy périmée à l'écriture | Précondition d'écriture différée ; variation du jeton de lecture testée |

Les assertions de non-mutation comparent les fixtures avant/après SELECT et les
affiliations avant/après construction de la réponse. Elles prouvent l'absence
d'effet des lectures ; elles **ne démontrent pas encore** la préservation des
affiliations par un writer. Aucun test simulé n'est présenté comme validation
de production. Les tests obligatoires d'écriture demeurent des conditions de
réception du futur lot, et non des tests déclarés réussis.

Commandes reproductibles :

```text
python test/unit/publication_verification_sql_test.py
python test/unit/publication_recherche_sql_test.py
python test/unit/publication_verification_mariadb_test.py
php -n test/unit/publication_destinataire_test.php
php -n test/unit/publication_lecture_test.php
php -n test/unit/publication_recherche_test.php
```

## Vérifications réelles et conditions de première publication

1. Exécuter les `SHOW TABLE STATUS`, `SHOW CREATE TABLE`, `SHOW INDEX` exacts de
   `test/sql/publication_controlee_schema.sql` pour les huit tables existantes.
   Les trois tables techniques proposées n'ont à être interrogées que si présentes.
   Vérifier aussi les triggers/effets indirects des tables écrites avec le DBA.
2. Confirmer colonnes, types, bornes de date, clés primaires, unicités et moteurs,
   y compris `abonJouEq`, `abonJouLig`, `premierMatch`, `dernierMatch`.
   Exécuter `EXPLAIN` du nouveau SELECT avec les IDs de la ligue de test.
3. Choisir sur preuve v1 transactionnelle ou approuver v2 avec Backend, y compris
   les limites de durabilité MyISAM et les migrations additives requises.
4. Valider les permissions de création et les règles de verrouillage vis-à-vis
   des writers historiques ; préparer une ligue/saison de test explicitement identifiées.
5. Implémenter le writer, ses clés/reçus durables et l'adaptateur Backend selon
   le contrat approuvé, sans changement du modèle canonique ni ajout HCR Legacy.
6. Réaliser sur une base MySQL/MariaDB isolée tous les scénarios d'écriture de la
   mission, avec deux sessions concurrentes et interruptions aux fenêtres critiques.
   Comparer toutes les affiliations préexistantes ligne par ligne ; tester aussi
   les attributs nuls, les périodes ambiguës, la révision périmée et les reçus invalides.
7. Valider le circuit complet Backend → Legacy sur la ligue de test autorisée,
   reçu final par joueur et seconde publication sans doublons. L'activation et
   le déploiement exigent un mandat ultérieur. Le présent code reste bloqué,
   sans variable d'environnement permettant de contourner le blocage.
