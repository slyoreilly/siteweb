# 📦 site-web

## 🎯 Rôle

Ce repo contient le cœur historique web + API de SyncStats (PHP), incluant le site public, l’admin, le monitoring et les scripts de synchronisation mobile/terrain.  
Il est utilisé pour recevoir des données de match (événements, clips, statuts appareils), les traiter, les stocker et les exposer aux interfaces web/mobile.  
Il sert aux opérateurs terrain, administrateurs de ligue, arbitres et utilisateurs finaux SyncStats.

---

## 🧠 Contexte système

Ce repo fait partie du système **SyncStats**.

👉 Voir : `../syncstats-system/AGENTS_REGISTRY.md` pour la vue globale d'ownership

---

## 🔗 Dépendances

### Entrées
- Apps terrain SyncStats / SyncCam / SyncBoard (sync des événements, matchs, statuts)
- Interfaces web admin/monitoring
- Endpoints API internes (`/api`, `/syncscript`, `/scriptsphp`)

### Sorties
- Base MySQL SyncStats (tables matchs, événements, abonnements, appareils, etc.)
- Réponses JSON consommées par apps mobiles et interfaces web
- Fichiers médias (vidéos, uploads) et traitements serveur associés
- Flux d’ACK/ingestion sync (dossier `api/sync`)

---

## 🔄 Flux principal

Nouveau système:
Entrée mobile/terrain → endpoint sync (`api/upsertEvenements.php`) → validation/transformation → écritures DB (événements/matchs/abonnements) → recalculs/MAJ → réponse JSON de synchronisation
Ancien Système:
Entrée mobile/terrain → endpoint sync (`syncscript/traiteSync.php`) → validation/transformation → écritures DB (événements/matchs/abonnements) → recalculs/MAJ → réponse JSON de synchronisation

Exemple :  
Event mobile → `traiteSync.php` → `dechargeMatchs_2.php` + DB → JSON `syncOK/syncOKdetail`

---

## ▶️ Lancer le projet

### Local

```bash
# Pré-requis
# - PHP + serveur web (Apache/Nginx)
# - MySQL accessible
# - Variables d’environnement (ex: WORK_ENV)

# Dépendances PHP (si nécessaire)
composer install
```

Doit être placer dans un dossier accessible par l'extérieur (ex var/html/www/)

### Docker

- Pas de Docker en ce moment, à évaluer.

---

## 🧪 Tests

### Lancer les tests
- Tests unitaires disponibles dans `test/unit` :
  - `upsert_evenements_rules_test.php`
  - `upsert_evenements_integration_light_test.php`
  - `evenements_read_contract_test.php`
  - `upsert_match_rules_test.php`

Exécution (exemple) :
```bash
php test/unit/upsert_evenements_rules_test.php
```

### Types de tests
- Unitaires : présents sur le contrat des événements et règles d'upsert match/événements
- Intégration : validation légère disponible pour l'upsert d'événements + validations manuelles des endpoints sync/API
- E2E : non formalisés

---

## 📡 API (si applicable)

### Endpoints principaux (exemples)
| Méthode | Route | Description |
|---|---|---|
| POST | `/syncscript/traiteSync.php` | Synchronisation principale mobile (événements + récupération données) |
| POST | `/syncscript/syncInfoComptes.php` | Sync d’info compte/temps |
| POST | `/api/upsertEvenements.php` | Upsert d’événements |
| POST | `/api/upsertMatch.php` | Upsert match |
| POST | `/api/sync/event.php` | Inbound événement moderne (inbox + ACK) |
| POST | `/api/sync/presence.php` | Inbound présence moderne (inbox + ACK) |
| GET/POST | `/api/getMatchEnCours.php` | Récupération match en cours |

---

## 🗃️ Structure du projet

```txt
/api              # Endpoints API récents et sync inbound/ack
/syncscript       # Endpoints historiques de synchronisation terrain
/scriptsphp       # Fonctions/services PHP métier historiques
/scriptserveur    # Scripts de maintenance/normalisation/vidéo
/monitoring       # UI + backend monitoring
/mobile           # Assets/pages mobile web
/db               # Scripts SQL/migrations ponctuelles
/base_documentaire# Documentation technique et DB
```

---

## 🧠 Logique métier

### Concepts clés
- **Sync transactionnelle** : le mobile pousse événements/états, le serveur répond avec mapping/ACK/mises à jour.
- **Match/Event** : événements horodatés, recalculs de score/statut, consolidation match.
- **Abonnements/liaisons appareils** : droits d’accès ligue/match/appareil.
- **Vidéo/clip** : rattachement des actions aux matchs/événements.

---

## ⚙️ Configuration

Le fichier scriptsphp/defenvvar.php contient les variables principales.

### Variables d’environnement (exemples observés)
- `WORK_ENV` (`development` / `production`)
- `SYNC_INBOUND_TOKEN`
- `SYNC_ACK_URL`
- `SYNC_ACK_TOKEN`
- `SYNC_ACK_HEADER`
- `SYNC_ACK_TIMEOUT_SECONDS`
- `SYNC_ACK_MAX_ATTEMPTS`

---

## 🚀 Déploiement

- **Dev** : environnement PHP + MySQL, `WORK_ENV=development`
- **Prod** : environnement hébergé PHP/MySQL, `WORK_ENV=production`
- Pipeline : fichiers Bitbucket historiques présents (à confirmer avec votre CI actuelle)

---

## 🐞 Debug rapide

Checklist :
- endpoint sync répond (`traiteSync.php` renvoie JSON valide)
- connexion DB OK
- pas de sortie parasite avant JSON (BOM, warning/fatal)
- logs PHP/Apache consultés
- flux complet mobile → DB → réponse validé

---

## 🤖 AI GUIDE

### Comment travailler avec ce repo
1. Lire ce README + docs dans `base_documentaire`
2. Identifier d’abord le flux exact touché (`api` vs `syncscript`)
3. Valider l’impact JSON côté client mobile avant merge

### Contraintes importantes
- Respecter la compatibilité des payloads mobiles historiques
- Éviter les régressions silencieuses (réponse vide, BOM, warnings en sortie)
- Tester les chemins critiques de sync de bout en bout

---

## 📍 TODO / Dette technique

- [ ] Ajouter tests d’intégration automatiques sur endpoints sync critiques
- [ ] Centraliser la gestion d’erreurs JSON (format homogène)
- [ ] Réduire les `die()` dispersés dans les includes métier
- [ ] Normaliser encodage des fichiers PHP en UTF-8 sans BOM

---

## 👤 Responsable

- À compléter (équipe SyncStats / propriétaire technique)
