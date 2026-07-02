# Déploiement — Site Web Legacy

Objectif :
Déployer le site web Legacy de manière reproductible, traçable et avec possibilité de retour arrière.

---

# Architecture

Source de vérité :

Git
↓
Ansible (DEV)
↓
Scripts de déploiement
↓
Production

Important :
- Ne pas modifier directement la prod via FTP sauf intervention d'urgence.
- Toute correction manuelle doit être ramenée dans Git ensuite.
- Les scripts de déploiement sont dans `deploiement/`.

---

# Structure

```
deploiement/
├── deploy-prod.sh
├── rollback-prod.sh
├── smoke-tests.sh
├── scriptTransfert.sh
├── deploy-history.log
└── README_DEPLOIEMENT.md
```

---

# Flux 1 — Mise à jour DEV (Ansible)

Mettre à jour l'environnement DEV depuis Git.

Exemple :

```bash
ansible-playbook playbooks/deploy/siteweb_lamp.yml
```

Vérifier :

```bash
git status
git log -1
```

Attendu :

```
working tree clean
```

---

# Flux 2 — Validation déploiement (simulation)

Exécuter le pipeline complet sans upload.

Depuis n'importe où :

```bash
~/siteweb/site-web/deploiement/deploy-prod.sh --dry-run
```

Répondre :

```
oui
```

Attendus :

```
1/4 Transfert
2/4 Smoke tests
3/4 Rapport
4/4 Historique
DEPLOIEMENT TERMINE
```

Aucun upload réel.

---

# Flux 3 — Déploiement réel

Avant :

Sauvegarder les fichiers sensibles.

Exécuter :

```bash
~/siteweb/site-web/deploiement/deploy-prod.sh
```

Validation :

```bash
curl -s https://syncstats.com/.deploy-version.json
```

Puis :

```bash
~/siteweb/site-web/deploiement/smoke-tests.sh
```

---

# Flux 4 — Rollback

Retour vers un commit ou tag.

Exemple :

```bash
~/siteweb/site-web/deploiement/rollback-prod.sh prod-20260622-1830
```

Le rollback :

1. checkout Git
2. déploiement
3. smoke test
4. restauration branche

Attention :
Le rollback restaure le code.
Les fichiers environnement restent protégés.

---

# Exclusions de déploiement

Le déploiement ne doit PAS écraser :

- `scriptsphp/defenvvar.php`
- `scriptsphp/defenvvar.sample.php`
- `.env`
- `.env.*`
- `php.ini`
- `.htaccess`
- fichiers secrets
- fichiers runtime

Validation :

```bash
grep -nE "exclude|defenvvar|php.ini|\.env" \
deploiement/scriptTransfert.sh
```

---

# Logs utiles

## Historique des déploiements

```bash
cat deploiement/deploy-history.log
```

## Version actuellement déployée

```bash
curl -s https://syncstats.com/.deploy-version.json
```

## Logs applicatifs

Exemples :

```bash
tail -100 monitoring/error_log
```

---

# Dépannage

## Permission denied

Rendre exécutables :

```bash
chmod +x deploiement/*.sh
```

---

## Repo Git sale

Diagnostic :

```bash
git status
```

Exemple :

```
working tree non propre
```

Corriger :
- runtime
- `.git/info/exclude`
- fichiers locaux

---

## Vérifier exclusions locales

```bash
cat .git/info/exclude
```

---

# Règles

Ne jamais :

- déployer sans dry-run
- déployer sans smoke test
- corriger prod sans retour Git
- supprimer des fichiers environnement

Toujours :

- garder Git comme source de vérité
- tester avant rollback
- documenter les exceptions