# Runbook - Sync inbound + ACK prioritaire faible latence

## 1) Rappel du flux

Pour `Event` et `Presence` (created/updated):
1. traitement metier amont
2. callback ACK immediat vers aval
3. inbox `done` uniquement si ACK `success`

Aucun fallback par cle metier.

## 2) Variables de config

- `SYNC_INBOUND_TOKEN`
- `SYNC_ACK_URL`
- `SYNC_ACK_TOKEN`
- `SYNC_ACK_HEADER` (defaut `X-Sync-Token`)
- `SYNC_ACK_TIMEOUT_SECONDS`
- `SYNC_ACK_MAX_ATTEMPTS`

## 3) Migrations

1. Creation table (nouvelle install):
- `db/20260316_create_sync_inbox.sql`

2. Extension table existante:
- `db/20260316_alter_sync_inbox_ack.sql`

## 4) Politique ACK

- `200` -> `ack_status=success`, `status=done`, `ack_at` rempli
- `400/404/409` -> `ack_status=rejected`, `status=rejected`, pas de retry infini
- `401/403` -> `ack_status=failed_auth`, `status=failed_auth`, alerte
- timeout/reseau/`5xx` -> `ack_status=retrying`, `ack_next_attempt_at` calcule
  backoff: `1s, 2s, 5s, 10s, 30s, 60s` (borne par `SYNC_ACK_MAX_ATTEMPTS`)

## 5) Worker ACK (continu)

Commande:

```bash
php api/sync/ack_worker.php --loop
```

Le worker traite `sync_inbox.ack_status='retrying'` avec `ack_next_attempt_at <= NOW()`.

## 6) Rejouer manuellement les ACK en retry

Relancer un lot en erreur technique:

```sql
UPDATE sync_inbox
SET ack_status='retrying',
    ack_next_attempt_at=NOW(),
    updatedAt=NOW()
WHERE ack_status='retrying';
```

Relancer un item precise:

```sql
UPDATE sync_inbox
SET ack_status='retrying',
    ack_next_attempt_at=NOW(),
    updatedAt=NOW()
WHERE syncInboxId=<ID>;
```

## 7) Isoler les cas rejected / failed_auth

```sql
SELECT syncInboxId, endpoint, dedupeKey, ack_status, ack_attempts, ack_http_code, ack_last_error, updatedAt
FROM sync_inbox
WHERE ack_status IN ('rejected','failed_auth')
ORDER BY updatedAt DESC;
```

## 8) Verification SQL rapide

Messages non done:

```sql
SELECT syncInboxId, endpoint, dedupeKey, status, ack_status, ack_attempts, ack_next_attempt_at
FROM sync_inbox
WHERE status <> 'done'
ORDER BY updatedAt DESC;
```

Latency ACK:

```sql
SELECT syncInboxId, endpoint, TIMESTAMPDIFF(MICROSECOND, createdAt, ack_at)/1000 AS ack_latency_ms
FROM sync_inbox
WHERE ack_at IS NOT NULL
ORDER BY syncInboxId DESC
LIMIT 100;
```

## 9) cURL reference ACK

```bash
curl -X POST "https://<AVAL>/api/sync/ack" \
  -H "Content-Type: application/json" \
  -H "X-Sync-Token: <SYNC_ACK_TOKEN>" \
  -d '{
    "entity":"Event",
    "sourceEntityId":68,
    "dedupeKey":"event:68:created:638...",
    "upstreamId":"123456"
  }'
```
