# Caméras associées à une surface

`GET /api/getCamerasParSurface.php?arenaId=<identifiant>` accepte un entier
positif correspondant à `Locations.SyncKey`, soit `TableArena.arenaId` dans
le Legacy. Envoyez `Authorization: Bearer <jeton>` avec le jeton serveur
déjà configuré dans `LEGACY_MATCH_ACTIF_TOKEN`.

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

## Identifiant et association

- `cameraIds` contient les identifiants entiers positifs de l'inventaire
  `Telephones.telId`, triés et dédupliqués. SyncCam transmet son réglage
  `camID` dans le champ `telId` dans `NetworkUtilities.java`.
  L'identifiant désigne l'appareil enregistré dans le Legacy. Le réglage
  SyncCam est modifiable et le code consulté ne garantit pas l'immuabilité
  de l'identifiant d'une caméra physique ni l'absence de réattribution.
  Les contraintes de la base déployée restent à vérifier.
- `abonAppareilSurface.surfaceId` référence `TableArena.arenaId` et
  `abonAppareilSurface.telId` référence `Telephones.telId`. Le filtre
  `role > 99 OR role < 1` reprend la sélection caméra de
  `scriptsphp/getAppareilsAbonne.php`. Une association sans appareil dans
  l'inventaire est ignorée.
- Une association est courante tant que sa ligne existe. L'interface
  `zadmin/interfaceAppareil.html` supprime la ligne par `abonAppSurfId`
  lors du retrait. Le code consulté n'applique ni intervalle de validité
  ni indicateur d'archivage à cette relation.
- L'API consulte l'inventaire et les associations exclusivement. La
  disponibilité des flux relève de la plateforme Web et du service streaming.

## Tests

- `python test/unit/cameras_surface_sql_test.py` exécute la requête sur
  SQLite avec des associations dupliquées, supprimées, orphelines, des
  rôles distincts et des surfaces vides ou inconnues.
- `php test/unit/cameras_surface_test.php` couvre les réponses de
  consultation, le typage des identifiants et l'échec technique simulé.
