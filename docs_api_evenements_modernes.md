# API Moderne Evenements (`/api`)

## Portee

- Source prioritaire de verite: `site-web (syncstats.com)`.
- Identifiant canonique evenement: `EventComId`.
- Regle metier: `le clic = fin du clip` (chrono represente l'instant du clic, en ms).
- Scope: endpoints modernes `/api`.
- Hors scope: `syncscript` legacy.

## CRUD moderne

### Create / Update / Delete

- Endpoint: `POST /api/upsertEvenements.php`
- Payload principal: `evenements` (JSON array dans body form-urlencoded)

Regles:
- `EventComId` vide: creation autorisee (sans contrainte de source), avec deduplication defensive.
- `EventComId` present: operation en upsert canonique par `event_id = EventComId`.
- `etatSync = 10`: suppression physique (`DELETE`) de l'evenement cible.
- Idempotence:
  - create sans `EventComId`: dedupe defensive sur `(match_event_id, equipe_event_id, joueur_event_ref, chrono, code, souscode, noSequence)`.
  - update avec `EventComId`: `INSERT ... ON DUPLICATE KEY UPDATE`.

Reponse (tableau d'objets):
- `id`: id local client
- `EventComId`: id canonique retourne
- `etatSync`: nouvel etat serveur
- `ok`: succes bool
- `action`: `created|reused|upserted` (si succes)
- `error`/`reason`/`detail`: informations en cas d'echec

### Read bulk

- Endpoint: `POST /api/getEvenements.php`
- Entree: `username`, `tempsDepart` (ms), `arenaId`
- Sortie: `events[]`

Contrat `events[]`:
- `eventId`: identifiant ligne retour
- `EventComId`:
  - evenements (type=0): `EventComId = eventId`
  - clips (type=5): `EventComId = null`
- `chrono`, `matchIdRef`, `matchId`, `ligueId`, `arenaId`, `eqDom`, `eqVis`, `date`, `code`, `sousCode`, `scoringEnd`

### Read instantane

- Endpoint: `POST /api/getEvenementInstant.php`
- Entree: `username`, `arenaId`, `lastEventId`
- Sortie:
  - `hasNewEvent`
  - `eventId`
  - `EventComId` (meme valeur que `eventId` si present)
  - `chrono`

## Exemples

### Create sans EventComId

Payload evenement:
```json
{
  "id": 81,
  "EventComId": "",
  "source": "plateforme",
  "GameStringID": "2026-03-22_ABC_DEF_1",
  "TeamID": 12,
  "PlayerComID": 55,
  "EventTypeID": 2,
  "chrono": 174000,
  "etatSync": 3,
  "noSequence": 1
}
```

Reponse item:
```json
{
  "id": 81,
  "EventComId": 22801,
  "etatSync": 12,
  "ok": true,
  "action": "created"
}
```

### Replay du meme create

Reponse item (idempotent):
```json
{
  "id": 81,
  "EventComId": 22801,
  "etatSync": 12,
  "ok": true,
  "action": "reused"
}
```

### Update avec EventComId

Reponse item:
```json
{
  "id": 81,
  "EventComId": 22801,
  "etatSync": 12,
  "ok": true,
  "action": "upserted"
}
```

### Delete

Payload evenement:
```json
{
  "id": 81,
  "EventComId": 22801,
  "etatSync": 10,
  "chrono": 174000
}
```

Reponse item:
```json
{
  "id": 81,
  "EventComId": 22801,
  "etatSync": 10,
  "ok": true,
  "deleted": true
}
```

## Tests serveur ajoutes

- `test/unit/upsert_evenements_rules_test.php`
- `test/unit/upsert_evenements_integration_light_test.php`
- `test/unit/evenements_read_contract_test.php`
- `test/unit/upsert_match_rules_test.php`

Objectifs couverts:
- contrat create sans `EventComId`,
- idempotence fonctionnelle de decision,
- stabilite du contrat read (`EventComId` explicite).


## Note Match (TableMatch)

Pour reduire les doublons de matchs lors des retries app<->web:
- `api/upsertMatch.php` utilise un verrou applicatif MySQL (`GET_LOCK`) par `matchLongId`.
- `api/upsertClips.php` applique le meme principe pour la creation implicite de match.

Action recommandee cote DB (fortement conseillee):
- appliquer `db/2026-03-22_tablematch_unique_matchidref.sql` apres nettoyage des doublons existants.

