# Caméras associées à une surface

`GET /api/getCamerasParSurface.php?arenaId=<identifiant>` accepte un entier
positif correspondant à `Locations.SyncKey`, soit `TableArena.arenaId` dans
le Legacy. Envoyez `Authorization: Bearer <jeton>` avec le jeton serveur
déjà configuré dans `LEGACY_MATCH_ACTIF_TOKEN`.

Voir la [configuration du jeton et le déploiement](docs_configuration_legacy_token.md)
pour le chargement privé en DEV et PROD.

Les réponses utilisent `Content-Type: application/json; charset=utf-8` et
`Cache-Control: no-store`. Les exemples utilisent des identifiants fictifs.

| HTTP | Corps JSON |
| --- | --- |
| 200 | `{"etat":"cameras_associees","arenaId":7,"cameraIds":[12,23]}` |
| 200 | `{"etat":"aucune_camera","arenaId":7,"cameraIds":[]}` |
| 400 | `{"etat":"arena_id_invalide"}` |
| 401 | `{"etat":"non_autorise"}` avec `WWW-Authenticate: Bearer` |
| 404 | `{"etat":"surface_inexistante","arenaId":7}` |
| 405 | `{"etat":"methode_non_autorisee"}` avec `Allow: GET` |
| 500 | `{"etat":"erreur_consultation"}` |
| 503 | `{"etat":"service_non_configure"}` si le jeton serveur est absent ou vide |

## Identifiant, association et connexion

- `cameraIds` contient tous les `StatutCam.camId` entiers positifs,
  triés et dédupliqués. Aucun repli sur `telId`, aucune sélection de la seule
  caméra la plus récemment connectée.
- Deux mécanismes Legacy sont réunis :
  - la surface déclarée dans `StatutCam.arenaId`, déjà utilisée par
    `scriptsphp/getAppareilsSurSurface.php` et le mode 1 de
    `scriptsphp/getAppareilsLies.php` ;
  - les abonnements `abonAppareilSurface.surfaceId` → `TableArena.arenaId`,
    reliés à `StatutCam` par `telId`, avec le rôle caméra
    `role > 99 OR role < 1` de `scriptsphp/getAppareilsAbonne.php`.
- `scriptsphp/enregistreStatutCam.php` reçoit `arenaId`, `telId` et `camId`.
  Il insère le statut ou actualise les lignes du téléphone, et écrit
  `dernierMaJ` à chaque réception, en GMT/UTC. `dernierModif` ne change que
  lors d'une transition de statut : il ne convient pas pour mesurer la
  dernière connexion. Aucun historique de streaming n'est nécessaire.
- La requête exige `dernierMaJ` entre `UTC_TIMESTAMP() - INTERVAL 7 DAY`
  et `UTC_TIMESTAMP()`, bornes incluses. Une date absente ou future est exclue.
  Le seuil est calculé en UTC, comme l'écriture Legacy, indépendamment du
  fuseau de la session MySQL. Un statut récent suffit, quel que soit `codeEtat`.
- Les caméras de plus de sept jours, y compris celles de 8 à 30 jours et
  celles de plus de 30 jours, sont exclues. Aucune association n'est supprimée.
  Un nouveau statut récent rend la caméra admissible sans réassociation.
- `StatutCam` suffit à identifier une caméra : une entrée dans `Telephones`
  n'est plus un préalable. `telId` doit rester positif.
- Une surface existante sans caméra admissible retourne toujours
  `aucune_camera` avec `cameraIds: []`. Une surface inconnue reste en 404.
  Les filtres sont dans la jointure externe pour préserver cette distinction.
- Le retrait d'un abonnement dans `zadmin/interfaceAppareil.html` supprime
  sa ligne par `abonAppSurfId`. Le lien déclaré dans `StatutCam.arenaId`
  est indépendant : il reste admissible si sa connexion est récente.

## Pourquoi une seule caméra pouvait être retournée

L'ancienne requête ne comportait ni `LIMIT 1` ni agrégation choisissant une
seule caméra. Elle imposait cependant la chaîne
`abonAppareilSurface` → `Telephones` → `StatutCam`, avec un rôle caméra.
Une caméra connue uniquement par `StatutCam.arenaId`, absente de `Telephones`,
sans abonnement, ou avec un rôle hors filtre était donc écartée. Plusieurs
appareils partageant le même `camId` produisent aussi un seul identifiant,
conformément au dédoublonnage demandé. L'ancienne requête n'appliquait aucun
seuil de connexion.

Ce sont des causes possibles du résultat `[24]`, pas un constat sur les
données de production : aucun environnement déployé ni aucune base réelle
n'ont été consultés.

## Diagnostic en lecture seule

Vous pouvez exécuter ce SELECT MySQL pour `arenaId = 1` (remplacez `1` dans
les deux branches pour une autre surface). Il expose les deux sources,
les rôles, l'inventaire facultatif et le statut de connexion. Les doublons
entre sources sont intentionnels pour expliquer le chemin d'association.
`retenue` décrit chaque chemin : un abonnement hors rôle peut être exclu
alors que le lien déclaré de la même caméra est admissible.

```sql
SELECT liens.surfaceId, liens.origine, liens.telId, liens.role,
       appareil.telId AS telId_inventaire,
       camera.arenaId AS surface_declaree,
       camera.camId, camera.dernierMaJ,
       UTC_TIMESTAMP() AS maintenant_utc,
       CASE
           WHEN camera.telId IS NULL THEN 'statut_absent'
           WHEN camera.telId <= 0 OR camera.camId IS NULL OR camera.camId <= 0
               THEN 'identifiant_invalide'
           WHEN liens.origine = 'abonnement'
                AND (liens.role IS NULL OR (liens.role BETWEEN 1 AND 99))
               THEN 'role_hors_filtre_camera'
           WHEN camera.dernierMaJ IS NULL THEN 'connexion_inconnue'
           WHEN camera.dernierMaJ > UTC_TIMESTAMP() THEN 'date_future'
           WHEN camera.dernierMaJ < UTC_TIMESTAMP() - INTERVAL 30 DAY
               THEN 'plus_de_30_jours_association_conservee'
           WHEN camera.dernierMaJ < UTC_TIMESTAMP() - INTERVAL 7 DAY
               THEN 'plus_de_7_jours'
           ELSE 'retenue'
       END AS diagnostic
FROM (
    SELECT surfaceId, telId, role, 'abonnement' AS origine
    FROM abonAppareilSurface WHERE surfaceId = 1
    UNION ALL
    SELECT arenaId, telId, NULL AS role, 'surface_declaree' AS origine
    FROM StatutCam WHERE arenaId = 1
) liens
LEFT JOIN StatutCam camera ON camera.telId = liens.telId
LEFT JOIN Telephones appareil ON appareil.telId = liens.telId
ORDER BY liens.telId, liens.origine, camera.camId;
```

## Validation locale

- `python test/unit/cameras_surface_sql_test.py` exécute la requête sur
  SQLite avec une horloge UTC figée. Seule l'expression d'horloge MySQL est
  adaptée à SQLite. Cinq tests couvrent les deux sources d'association,
  plusieurs caméras récentes sans inventaire `Telephones`, les doublons
  d'abonnement et de `camId`, les autres surfaces, les rôles, les identifiants
  invalides, les surfaces vides/inconnues et les statuts absents.
- Les cas temporels couvrent maintenant, exactement sept jours, sept jours
  et une seconde, huit jours, exactement trente jours, plus de trente jours,
  une date nulle et une date future. La conservation des associations et
  le retour après une nouvelle connexion sont vérifiés.
- `php test/unit/cameras_surface_test.php` couvre le contrat JSON existant,
  le typage des identifiants et l'échec technique simulé.
- Cette simulation SQLite ne remplace pas une exécution MySQL. Aucun
  accès à une base réelle n'est requis ni effectué.
