# 🤖 AI Context — site-web

## 🎯 Objectif

Ce service sert à opérer le cœur web/API historique de SyncStats : synchroniser les données terrain, maintenir l’état des matchs/événements et exposer les données aux interfaces web/mobile.

---

## 🔥 Flux critique

Mobile terrain → `syncscript/traiteSync.php` → includes métier (`dechargeTransactions`, `dechargeMatchs_2`, etc.) → MySQL → réponse JSON consommée par l’app

---

## ⚠️ Règles métier critiques

- La réponse de sync doit toujours être un JSON valide (jamais vide)
- Compatibilité payload legacy mobile obligatoire
- Les événements modifient score/statut de match de façon déterministe
- Aucun output parasite avant JSON (BOM, warning, echo debug)

---

## 🧪 Tests essentiels

Pour valider ce repo :

- [ ] appel sync minimal retourne un objet JSON valide
- [ ] sync avec événements (`but`, `punition`, `debutMatch`, `finMatch`) ne plante pas
- [ ] utilisateur inconnu retourne un JSON d’erreur contrôlé
- [ ] pas de warning/fatal dans logs lors d’un cycle sync complet

---

## 🚫 À ne pas faire

- Changer le contrat JSON des endpoints sync sans plan de compatibilité
- Introduire des `echo/print` de debug dans les includes critiques
- Modifier schéma DB sans migration et validation des scripts dépendants
- Sauvegarder des fichiers PHP en UTF-8 avec BOM

---

## ✅ À faire idéalement

- Garder les correctifs minimaux et ciblés sur le flux sync
- Ajouter des gardes `is_array/isset` sur payloads non fiables
- Journaliser proprement les erreurs serveur sans casser la réponse client
- Prioriser la robustesse des endpoints historiques
