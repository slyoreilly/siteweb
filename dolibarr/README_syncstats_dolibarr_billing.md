# Facturation SyncStats vers Dolibarr (mode tiers -> facture)

## Exécution CLI

```bash
php dolibarr/syncstats_dolibarr_billing.php \
  --periode_debut=2026-01-01 \
  --periode_fin=2026-02-28 \
  --mode=brouillon
```

Options:

- `--mode=brouillon|valider` (défaut `brouillon`)
- `--limite_tiers=NN` (utile en test)
- `--config=/chemin/vers/syncstats_dolibarr_billing.config.php`

## Logique Dolibarr

Pour chaque tiers Dolibarr (`/thirdparties`):

1. Vérifie que le tiers est client (`client > 0`).
2. Lit les extrafields:
   - `array_options.options_facturation_syncstats_active`
   - `array_options.options_ligues_syncstats`
   - `array_options.options_prix_match_ht`
3. Construit la signature stable:
   - `SYNCSTATS|FACTURATION|{periode_debut}|{periode_fin}`
4. Anti-doublon via API:
   - `GET /invoices?sqlfilters=(fk_soc:=:{ID}) and (ref_client:=:'{signature}')`
5. Si aucune facture:
   - `POST /invoices` avec `socid`, `date`, `ref_client`, `note_public`
   - `POST /invoices/{id}/lines` avec description détaillée, `qty`, `subprice`
6. Si `--mode=valider`:
   - `POST /invoices/{id}/validate`

## Source des matchs

Le script agrège les matchs facturables par ligue depuis MySQL stats:

- `COUNT(DISTINCT tm.match_id)`
- `INNER JOIN Video v ON tm.match_id = v.nomMatch`

## Configuration

Copier le sample:

- `dolibarr/syncstats_dolibarr_billing.config.sample.php`
  -> `dolibarr/syncstats_dolibarr_billing.config.php`

Le script exige `defenvvar.php` pour la connexion MySQL:

- `scriptsphp/defenvvar.php` (fichier local non versionné)
- template disponible: `scriptsphp/defenvvar.sample.php`

Variables d'environnement reconnues:

- `DOLIBARR_BASE_URL`
- `DOLIBARR_API_KEY`
- `DOLIBARR_PAGE_SIZE`
- `SYNCSTATS_DEFAULT_PRICE_PER_MATCH`
- `SYNCSTATS_DRY_RUN`

## Comportement erreur

- Toute erreur API/SQL sur un tiers est logguée.
- Le script continue avec le tiers suivant.
- Le script reste stateless (aucune table créée/modifiée).
