# Runbook - Sync inbound (Presence/Event)

## 1) Prerequis

- Endpoint presence: `POST /api/sync/presence`
- Endpoint event: `POST /api/sync/event`
- Header de securite: `X-Sync-Token`
- Variable serveur requise: `SYNC_INBOUND_TOKEN`
- Table idempotence: `sync_inbox`

## 2) Mise en service

1. Appliquer la migration SQL:
   - fichier: `db/20260316_create_sync_inbox.sql`
2. Configurer `SYNC_INBOUND_TOKEN` sur l'environnement cible.
3. Verifier le routage Apache du dossier `api/sync/.htaccess`.
4. Tester avec cURL (valide puis invalide).

## 3) Semantique HTTP

- `2xx`: message accepte/traite, retry stop cote outbox
- `4xx`: erreur fonctionnelle non retryable
- `5xx`: erreur technique retryable

## 4) Idempotence

- Cle idempotence: `(endpoint, dedupeKey)`
- Message deja traite (`processed`/`rejected`/`processing`): renvoi `200` + `duplicate=true`
- Message en echec precedent (`failed`): reprise de traitement autorisee

## 5) Observabilite

Chaque requete journalise:
- `messageId`
- `dedupeKey`
- `aggregateType`
- `actionType`
- resultat + code HTTP
- compteurs (`success`, `failure`, `duplicate`)

Important: le token n'est jamais logge en clair.

## 6) Exemples cURL

### Token valide - Presence

```bash
curl -i -X POST "https://votre-domaine/api/sync/presence" \
  -H "Content-Type: application/json" \
  -H "X-Sync-Token: change-me-dev" \
  -d '{
    "messageId": 101,
    "aggregateType": "Presence",
    "aggregateId": 678,
    "actionType": "updated",
    "dedupeKey": "presence-12345-678-updated-v1",
    "createdAt": "2026-03-16T20:01:00Z",
    "payload": {
      "GameComId": 12345,
      "joueurId": 678,
      "positionId": 4,
      "numero": "31",
      "domVis": 2,
      "statut": 1,
      "updatedBy": "SyncStatsLive",
      "updatedAt": "2026-03-16T20:00:59Z"
    }
  }'
```

### Token valide - Event

```bash
curl -i -X POST "https://votre-domaine/api/sync/event" \
  -H "Content-Type: application/json" \
  -H "X-Sync-Token: change-me-dev" \
  -d '{
    "messageId": 202,
    "aggregateType": "Event",
    "aggregateId": 9001,
    "actionType": "created",
    "dedupeKey": "event-12345-9001-created-v1",
    "createdAt": "2026-03-16T20:05:00Z",
    "payload": {
      "GameComId": 12345,
      "EventComId": 9001,
      "TeamID": 12,
      "PlayerComID": 678,
      "code": 2,
      "souscode": 1,
      "chrono": 1773453075123,
      "noSequence": 0
    }
  }'
```

### Token invalide

```bash
curl -i -X POST "https://votre-domaine/api/sync/presence" \
  -H "Content-Type: application/json" \
  -H "X-Sync-Token: mauvais-token" \
  -d '{"messageId":1,"aggregateType":"Presence","actionType":"created","dedupeKey":"x","payload":{}}'
```
