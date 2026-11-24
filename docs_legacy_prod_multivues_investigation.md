# Enquête — Alimentation expérimentale depuis Legacy production (lecture seule)

> Investigation read-only. **Aucune écriture, aucun commit, aucun push, aucun déploiement.**
> Hôte production sondé : `https://syncstats.com`. Fichier non versionné (livrable d'enquête).

> **Correction (convention des identifiants de match)** — vérifiée depuis
> `api/upsertMatch.php` : `TableMatch.match_id` est la clé primaire numérique =
> identifiant **interne** Legacy ; `TableMatch.matchIdRef` (= `matchLongId`) est la
> référence **externe**. Ce document antérieur présentait l'inverse ; l'endpoint
> livré et `docs_api_evenements_avec_videos.md` appliquent la convention correcte.

## 1. Verdict

Les endpoints **existants** permettent déjà de récupérer, **sans MySQL**, l'essentiel des
métadonnées de buts multi-angles : identifiant d'événement, identifiant de match, horodatage
Unix, identifiants vidéo, et une liste d'URL par événement. **Un jeu de 3 buts à 2 caméras a
été constitué et vérifié en production** (section 4).

Mais ils sont **insuffisants pour un mécanisme d'alimentation fiable** du nouveau Backend, pour
quatre raisons précises (section 5) :

1. Il faut **combiner deux endpoints** pour un seul événement (aucun ne renvoie tous les champs).
2. Le **`camId` n'est exposé par aucun endpoint fonctionnel** — il n'est qu'*inférable* du nom de fichier.
3. Les **URL renvoyées sont cassées en production** (`https://syncstats.comlookatthis<fichier>`, sans `/` ni protocole exploitable).
4. Le **code d'événement (`code=0` = but)** n'est **pas confirmable** via les endpoints publics : impossible de garantir « vrai but » en lecture seule.

→ Une **extension minimale de lecture** est donc justifiée (section 6). Conformément à la
consigne, elle n'est **pas développée** : seule la spécification est proposée.

## 2. Méthode

- Sondes HTTP `GET`/`POST` **en lecture seule uniquement**, User-Agent neutre, aucune donnée personnelle transmise.
- Endpoints **volontairement écartés** car ils **écrivent** en base ou exigent un secret :
  - `scriptsphp/getVideoEtContexte.php` → `UPDATE Video SET tagPrincipal=…` (écriture).
  - `api/getMatchEnCours.php` → peut écrire un tag dans `Video` (doc `getMatchEnCours`).
  - `api/getCamerasParSurface.php`, `api/getMatchActifParSurface.php` → `Authorization: Bearer <LEGACY_MATCH_ACTIF_TOKEN>` non fourni.
  - tous les `upsert*`, `syncscript/*`.

## 3. Inventaire des endpoints pertinents (état réel constaté)

| Endpoint | Entrée | Renvoie | Auth | État réel constaté |
| --- | --- | --- | --- | --- |
| `scriptsphp/getVideos.php` | POST `ligueId` ou `joueurId` | `recentsVideos[]` groupés par `reference`(=event_id) : `videoId`, `nomFichier`, `chrono`(ms), `nomMatch`(=`match_id`) | Aucune | **Fonctionnel.** Groupage multi-angle exploitable. **Pas de `camId`, pas d'`emplacement`.** |
| `api/getListeVideoDeEventId.php` | GET `event_id` **ou** `clipId` | Tableau d'**URL** (`emplacement`+`nomFichier`) | Aucune | **Fonctionnel** mais **URL cassées** (voir §5.3). Pas de `videoId`/`camId`. |
| `api/getVideosParLigueEtPlateau.php` | GET `ligueId`,`plateauId` | par `event_id` : `chrono` + `videos[]`(URL), `LIMIT 100` | Aucune | **Fonctionnel** (contrat vérifié). Mêmes URL cassées. Pas de `videoId`/`camId`/`match_id`. |
| `scriptsphp/getFullVideos.php` | POST `gameId` | `videoId`,`nomFichier`,`chrono`,`camId`,`emplacement` | Aucune | Fonctionnel mais **`type=10000`** = vidéos pleine partie, **pas les buts**. |
| `scriptsphp/getVideosParBut.php` | POST `ligueId`/`joueurId` | `videoId`,`cam`,`fic`,`chrono` groupés par but | Aucune | **CASSÉ en prod** : utilise l'API `mysql_*` dépréciée → **réponse vide**. C'était le seul à exposer `cam` explicitement. |
| `api/getEvenements.php` | POST `username`,`arenaId`,`tempsDepart` | `eventId`,`EventComId`,`chrono`,`matchIdRef`,`matchId`,`code`,`sousCode`… | Abonnement (`AbonnementLigue`+`TableUser`) | Fonctionnel mais **pas de vidéo/caméra** et exige un utilisateur abonné. **Seul endroit où apparaît `code`.** |

### Schéma de données déduit

- `Video.reference` = `event_id` (si `type=0`) ou `clipId` (si `type=5`).
- `Video.nomMatch` = `TableMatch.match_id` (le `GameComId`).
- `Video.nomFichier` suit le motif **`<timestampMs>_<camId>.mp4`** → le `camId` est encodé en suffixe.
- `Video.camId` ↔ inventaire `Telephones.telId` (cf. `docs_api_cameras_surface.md`).
- `TableEvenement0` : `code=0` = but ; `chrono` en ms.

## 4. Jeu de données vérifié — 3 buts, 2 caméras chacun

Source : `getVideos.php?ligueId=116` (métadonnées) croisé avec
`getListeVideoDeEventId.php?event_id=…` (URL). **Ligue 116, match `match_id=7356`, caméras 44 & 45**
(mai 2019). `camId` inféré du suffixe de `nomFichier`.

### But 1 — event_id 241586
- match_id : `7356`
- chrono (Unix ms) : `1557685437257` (≈ 2019-05-12 17:03:57 UTC)
- caméra 44 : videoId `87622`, fichier `1557685437828_44.mp4`
- caméra 45 : videoId `87864`, fichier `1557685438828_45.mp4`
- URL renvoyées (brutes, **cassées**) : `https://syncstats.comlookatthis1557685437828_44.mp4`, `…1557685438828_45.mp4`

### But 2 — event_id 241584
- match_id : `7356`
- chrono (Unix ms) : `1557685210624`
- caméra 44 : videoId `87621`, fichier `1557685211195_44.mp4`
- caméra 45 : videoId `87863`, fichier `1557685212195_45.mp4`

### But 3 — event_id 241580
- match_id : `7356`
- chrono (Unix ms) : `1557684940093`
- caméra 44 : videoId `87619`, fichier `1557684940664_44.mp4`
- caméra 45 : videoId `87861`, fichier `1557684941664_45.mp4`

> Au total **104 événements à ≥2 vidéos** ont été détectés pour la seule ligue 116 : le volume
> multi-angles est largement suffisant. La durée des fichiers reste à mesurer par le moteur
> audiovisuel local (non faite ici, conforme à la consigne).

## 5. Lacunes précises

1. **Deux appels par événement.** `getVideos.php` donne `videoId`/`chrono`/`match_id` mais pas l'URL ; `getListeVideoDeEventId.php` donne l'URL mais pas `videoId`/`camId`. Aucun endpoint ne fournit les 6 champs d'un coup.
2. **`camId` absent des endpoints fonctionnels.** Le seul qui l'exposait (`getVideosParBut.php`) est cassé (`mysql_*`). Le `camId` n'est aujourd'hui qu'*inféré* du nom de fichier — fragile et non contractuel.
3. **URL cassées en production.** `getListeVideoDeEventId` renvoie `https://syncstats.comlookatthis<fichier>` : jeton `lookatthis` littéral + `emplacement` sans protocole ni `/`. Les chemins devinés (`/videos/…`) renvoient **404**. L'URL exploitable réelle n'est donc **pas** fournie par l'API.
4. **`code`/type non vérifiable.** Impossible de confirmer `code=0` (vrai but) ni de distinguer `type=0` vs `type=5` (clip) en lecture seule publique : `getListeVideoDeEventId` a d'ailleurs renvoyé `[]` pour des `reference` qui sont probablement des clips (interrogés par la mauvaise clé). `getEvenements.php` expose `code` mais exige un abonnement et n'a aucune donnée vidéo.

## 6. Proposition d'extension minimale (NON développée)

Un **unique endpoint de lecture** couvrant les 4 lacunes, aligné sur les conventions existantes.

- **Route** : `GET /api/getEvenementsMultivues.php`
- **Auth** : `Authorization: Bearer <LEGACY_MATCH_ACTIF_TOKEN>` (même mécanisme que `getMatchActifParSurface`/`getCamerasParSurface`, via `scriptsphp/chargeSecrets.php` ; `503` si secret absent, `401` si Bearer invalide).
- **Paramètres (récupération bornée)** : `ligueId` (requis), `arenaId` (optionnel), `depuisMs` (chrono min), `limit` (défaut 50, **max 200**), `codes` (défaut `0` = buts).
- **Règle multivue** : ne retourner que les événements ayant **≥2 `camId` distincts**.
- **Lecture seule stricte** : `SELECT` uniquement, `Cache-Control: no-store`.
- **Données minimisées** (uniquement le nécessaire au Backend expérimental) :

```json
{
  "etat": "ok",
  "evenements": [
    {
      "eventId": 241586,
      "matchId": 7356,
      "matchIdRef": "…",
      "chronoMs": 1557685437257,
      "code": 0,
      "videos": [
        { "videoId": 87622, "camId": 44, "url": "https://<emplacement_correct>/<chemin>/1557685437828_44.mp4" },
        { "videoId": 87864, "camId": 45, "url": "https://<emplacement_correct>/<chemin>/1557685438828_45.mp4" }
      ]
    }
  ]
}
```

Apports vs existant : `camId` **explicite**, `code` **filtré/vérifié**, **URL correctement construites**
(corrige le bug `emplacement`/`lookatthis`), **un seul appel borné**, authentifié. Candidat naturel au
mécanisme d'alimentation permanent du nouveau Backend.

**Prérequis à cette route** : corriger la construction d'URL (champ `emplacement` sans protocole +
jeton `lookatthis`) — à traiter comme décision produit/infra, pas dans cette enquête.

## 7. Reproduire les sondes (lecture seule)

```bash
curl -s -A "SyncStats-ReadOnly-Probe" -d "ligueId=116" https://syncstats.com/scriptsphp/getVideos.php
curl -s -A "SyncStats-ReadOnly-Probe" "https://syncstats.com/api/getListeVideoDeEventId.php?event_id=241586"
```
