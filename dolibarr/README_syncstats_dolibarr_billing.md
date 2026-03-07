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
- `--tiers_id=ID` (forcer un tiers précis)
- `--dry_run=0|1` (force le dry-run en CLI)
- `--verbose=1` (affiche des logs DEBUG supplémentaires)


## Test dry-run contrôlé (1 tiers)

```bash
php dolibarr/syncstats_dolibarr_billing.php \
  --periode_debut=2026-01-01 \
  --periode_fin=2026-02-28 \
  --tiers_id=123 \
  --dry_run=1
```

Sorties attendues (sans création Dolibarr):
- tiers traité
- ligues associées
- signature (`ref_client`)
- anti-doublon (trouvé / non trouvé)
- nb de matchs facturables
- montant estimé HT


## Commande demandée (brouillon non-validé)

```bash
php dolibarr/syncstats_dolibarr_billing.php \
  --periode_debut=2026-01-01 \
  --periode_fin=2026-02-28 \
  --mode=brouillon \
  --limite_tiers=1
```

Cette commande crée une facture brouillon (non validée) si `dry_run` n'est pas actif.
Pour forcer des logs détaillés, ajoutez `--verbose=1`.

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

Le script s'appuie uniquement sur defenvvar.php pour la connexion MySQL:


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
