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
- Par défaut, le script réutilise `scriptsphp/defenvvar.php` (même mécanique que vos scripts existants) pour la connexion MySQL.
- Créer `scriptsphp/defenvvar.php` à partir de `scriptsphp/defenvvar.sample.php` sur chaque environnement (fichier local non versionné).
- Renseigner les paramètres Dolibarr (`DOLIBARR_BASE_URL`, `DOLIBARR_API_KEY`, `DOLIBARR_PAGE_SIZE`) dans `defenvvar.php` ou via variables d'environnement système.
- Ajuster `price_per_match` et `dry_run` dans ce fichier de config.

Variables d'environnement lues par le script:

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
