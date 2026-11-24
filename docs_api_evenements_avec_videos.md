# API — Événements avec vidéos découpées (`GET /api/getEvenementsAvecVideos.php`)

Endpoint de **lecture seule** et authentifié qui alimente le nouveau Backend
expérimental à partir de Legacy production, **sans accès direct MySQL**. Il
retourne des événements et leurs **vidéos découpées** (`Video.type = 0`).

Hors périmètre de cette première version : vidéos **RAW** (`type = 10000`) et
**clips manuels** (`type = 5`).

## Authentification

Réutilise le jeton interservices `LEGACY_MATCH_ACTIF_TOKEN` (même mécanisme que
`getMatchActifParSurface.php` / `getCamerasParSurface.php`, chargé par
`scriptsphp/chargeSecrets.php`). Envoyer `Authorization: Bearer <jeton>`.

**Périmètre d'autorisation** : ce jeton donne un accès de *consultation* serveur
à serveur aux données Legacy de toutes les surfaces. Cet endpoint reste dans ce
périmètre : il est strictement en lecture seule et n'expose que des références
(événement, match, caméra, fichier) et des **URL vidéo déjà publiques**. Aucune
donnée personnelle ni secret n'est exposé. Si une granularité plus fine devient
nécessaire, prévoir un jeton dédié plutôt que d'élargir celui-ci.

## Paramètres (query string)

| Paramètre | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `matchId` | entier | — | Identifiant **interne** Legacy du match (`TableMatch.match_id`, clé numérique). Facultatif. |
| `ligueId` | entier | — | `TableMatch.ligueRef`. Facultatif. |
| `minChronoMs` | entier | — | Horodatage Unix minimal (ms). Facultatif. |
| `maxChronoMs` | entier | — | Horodatage Unix maximal (ms). Facultatif. |
| `codes` | liste d'entiers (`,`) | — | Codes d'événement, ex. `0` = but. Facultatif. |
| `minCameras` | entier ≥ 1 | `1` | Nombre minimal de caméras distinctes par événement. |
| `limit` | entier ≥ 1 | `50` | Nombre maximal d'événements, **borné à 200**. |

**Périmètre obligatoire** : la requête doit fournir au moins `matchId` **ou**
`ligueId`, sinon `400 { "etat": "perimetre_insuffisant" }`.

## Réponse `200`

```json
{
  "etat": "ok",
  "perimetre": { "matchId": null, "ligueId": 107, "minChronoMs": null,
                 "maxChronoMs": null, "codes": [0], "minCameras": 2, "limit": 3 },
  "nbEvenements": 1,
  "evenements": [
    {
      "eventId": 241586,
      "match": { "idInterneLegacy": 7356, "idExterneReference": "2019-REF-7356" },
      "chronoMs": 1557685437257,
      "code": 0,
      "sousCode": 0,
      "videos": [
        {
          "videoId": 87622,
          "camId": 44,
          "nomFichier": "1557685437828_44.mp4",
          "url": "https://syncstats.ca/lookatthis/1557685437828_44.mp4",
          "dureeReelleFichier": null,
          "dureeDeclaree": { "valeur": 8000, "unite": "ms", "source": "appareil_capture", "verifiee": false }
        }
      ]
    }
  ]
}
```

- `match.idInterneLegacy` = `TableMatch.match_id` : clé primaire numérique Legacy
  (AUTO_INCREMENT retourné comme GameComId par `upsertMatch`) = identifiant **interne**.
- `match.idExterneReference` = `TableMatch.matchIdRef` (= `matchLongId`) : référence
  **externe** fournie par le client / SyncRef, clé de liaison des événements
  (`TableEvenement0.match_event_id = matchIdRef`).
- `camId` = valeur **officielle** `Video.camId` (jamais reconstruite depuis le nom de fichier).
- `url` : construite via le mécanisme fonctionnel existant — hôte `5.39.81.14`
  réécrit en `syncstats.ca`, fichier servi sous `/lookatthis/`. Vérifié : renvoie
  un `video/mp4` 200 avec requêtes *range* (206).
- **Durée** — deux notions explicitement distinguées :
  - `dureeDeclaree` : durée **déclarée par l'appareil de capture**, indicative et
    **non vérifiée** (`verifiee: false`, unité `ms`). Exposée seulement si une
    valeur strictement positive existe en base, sinon `null`.
  - `dureeReelleFichier` : toujours `null` ici ; réservée à la **durée réelle du
    fichier**, que le Backend obtient par inspection audiovisuelle.

### Autres états

| HTTP | `etat` |
| --- | --- |
| 400 | `perimetre_insuffisant` (ni `matchId` ni `ligueId`) |
| 400 | `parametre_invalide` (+ `parametre`) |
| 401 | `non_autorise` (Bearer absent/incorrect) + `WWW-Authenticate: Bearer` |
| 405 | `methode_non_autorisee` + `Allow: GET` |
| 500 | `erreur_consultation` |
| 503 | `service_non_configure` (jeton serveur absent) |

## Appel après déploiement

```bash
# 3 vrais buts récents avec au moins 2 angles (ligue 107) :
curl -s -H "Authorization: Bearer $LEGACY_MATCH_ACTIF_TOKEN" \
  "https://syncstats.com/api/getEvenementsAvecVideos.php?ligueId=107&codes=0&minCameras=2&limit=3"

# Par match externe précis :
curl -s -H "Authorization: Bearer $LEGACY_MATCH_ACTIF_TOKEN" \
  "https://syncstats.com/api/getEvenementsAvecVideos.php?matchId=7356&codes=0&minCameras=2&limit=3"
```

## Déploiement

- Livrer `api/getEvenementsAvecVideos.php` et `api/lib/evenements_avec_videos.php`.
- `scriptsphp/chargeSecrets.php` et le secret `LEGACY_MATCH_ACTIF_TOKEN` sont déjà
  en place pour les routes existantes (voir `docs_configuration_legacy_token.md`).
- Aucune modification des anciens endpoints ni des données historiques.

## Validation locale (exécutée)

- **Lint `php -l`** des deux fichiers : OK sur PHP **7.4, 8.1 et 8.3** (conteneurs
  Docker), couvrant les versions de production plausibles.
- **Tests unitaires** `test/unit/evenements_avec_videos_test.php` (URL, validation
  des paramètres, mise en forme, non-troncature) : **passent** sur PHP 7.4/8.1/8.3.
- **Test d'intégration SQL** contre **MariaDB 10.6** (schéma Legacy réel, requêtes
  exécutées via `mysqli`) : vérifie la syntaxe/compatibilité SQL, l'ordre
  `chrono DESC`, l'exclusion des clips (`type=5`) et RAW (`type=10000`), le filtre
  `code`, le bornage par `limit` (événements uniquement) et l'**absence de
  troncature** des listes de vidéos (événement à 3 vidéos / 2 caméras → 3 vidéos).
- **URL** validée contre la production : fichier `video/mp4` réellement servi (200,
  *range* 206).

Commande (depuis la racine du dépôt, Docker requis) :

```bash
docker run --rm -v "$PWD:/app" -w /app php:8.1-cli php test/unit/evenements_avec_videos_test.php
```
