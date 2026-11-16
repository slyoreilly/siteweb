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

## Réponses

| HTTP | `etat` | Champs supplémentaires |
| --- | --- | --- |
| 200 | `match_actif` | `arenaId`, `match` avec `matchId` et `debutMs` |
| 200 | `aucun_match_actif` | `arenaId`, `match: null` |
| 409 | `plusieurs_matchs_actifs` | `arenaId` |
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
  La consultation ne dépend d’aucune fenêtre horaire.
- `api/getMatchsAVenir.php` consulte un calendrier sur une fenêtre de dates.
  `api/getMatchEnCours.php` exige déjà une référence de match, renvoie son
  sommaire et peut écrire un tag dans `Video`.

Créez une association automatique uniquement sur `200 / match_actif`.
Traitez `409` comme un conflit et `500` ou `503` comme une consultation
indisponible. Les changements saisis sur un appareil deviennent visibles
après leur synchronisation avec le Legacy.

## Tests locaux

```sh
python test/unit/match_actif_surface_sql_test.py
php test/unit/match_actif_surface_test.php
```
