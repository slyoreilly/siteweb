# Facturation SyncStats vers Dolibarr

Script CLI PHP: `syncstats_dolibarr_billing.php`

## Paramètres

```bash
php dolibarr/syncstats_dolibarr_billing.php \
  --period_start=2026-01-01 \
  --period_end=2026-02-28
```

Optionnel:

```bash
--config=/chemin/vers/syncstats_dolibarr_billing.config.php
```

## Configuration

- Copier `dolibarr/syncstats_dolibarr_billing.config.sample.php`
  vers `dolibarr/syncstats_dolibarr_billing.config.php`.
- Par défaut, le script réutilise `scriptsphp/defenvvar.php` (même mécanique que vos scripts existants).
- Adapter l'URL Dolibarr, la clé API et `price_per_match`.
- Optionnel: désactiver `use_defenvvar` pour forcer une connexion MySQL directe via les paramètres `host/port/database/username/password`.

Le script supporte aussi les variables d'environnement:

- `SYNCSTATS_DB_HOST` (si `use_defenvvar=false`)
- `SYNCSTATS_DB_PORT` (si `use_defenvvar=false`)
- `SYNCSTATS_DB_NAME` (si `use_defenvvar=false`)
- `SYNCSTATS_DB_USER` (si `use_defenvvar=false`)
- `SYNCSTATS_DB_PASSWORD` (si `use_defenvvar=false`)
- `SYNCSTATS_DB_CHARSET` (si `use_defenvvar=false`)
- `DOLIBARR_BASE_URL`
- `DOLIBARR_API_KEY`
- `DOLIBARR_PAGE_SIZE`
- `SYNCSTATS_PRICE_PER_MATCH`
- `SYNCSTATS_DRY_RUN`

## Logique

1. Récupère les tiers Dolibarr (`/thirdparties`).
2. Lit l'extrafield `league_ids`.
3. Compte les matchs facturables par ligue via MySQL (`COUNT(DISTINCT tm.match_id)` avec `INNER JOIN Video`).
4. Vérifie l'absence d'une facture existante sur la période (`note_public`).
5. Crée la facture puis ajoute la ligne avec quantité = total des matchs.
6. Continue sur les autres clients même en cas d'erreur sur un client.

## Important

- Le script est stateless.
- Le script ne crée aucune table et ne modifie pas MySQL stats.
- Le script écrit uniquement via l'API Dolibarr (création facture + ligne).
