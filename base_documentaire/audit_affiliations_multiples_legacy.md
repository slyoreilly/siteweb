# Audit — Affiliations multiples d'un joueur dans Legacy

**Date :** 26 novembre 2026
**Portée :** dépôt `site-web` (plateforme Legacy) uniquement, en lecture seule.
**Rôle exercé :** Projet / Diagnostic (aucune écriture, aucune migration, aucun commit).
**Objet :** déterminer si Legacy supporte un joueur inscrit (a) dans deux équipes d'une même ligue, (b) dans deux ligues différentes, en tenant compte de l'ensemble des parcours applicatifs et non du seul schéma SQL.

> **Avertissement de confiance.** Aucun dump SQL complet ni base vivante n'est présent dans le dépôt local. Le schéma est reconstruit à partir des requêtes PHP et de `base_documentaire/base_de_donnees_syncstats.md`. Les conclusions sur le **modèle** et sur le **code** sont fondées sur les sources ; les conclusions sur les **contraintes réellement déployées** et les **données de production** restent à confirmer par les requêtes de diagnostic fournies en §9. Aucun comportement n'est affirmé « fonctionnel en production » sur la seule base du modèle.

---

## 1. Modèle de données observé

Tables pertinentes (reconstruites depuis le code) :

| Table | Clé technique observée | Rôle | Colonnes de rattachement / période |
|---|---|---|---|
| `TableJoueur` | `joueur_id` | Identité **globale** du joueur | `NomJoueur`, `NumeroJoueur`, `position`, `equipe_id_ref` (dénormalisé), `Ligue`, `dernierMAJ`, `proprio` |
| `TableEquipe` | `equipe_id` | Équipe | `nom_equipe`, `ligue_equipe_ref` (dénormalisé) |
| `Ligue` | `ID_Ligue` | Ligue (racine métier) | `Nom_Ligue` |
| `abonJoueurEquipe` | `abonJouEq` (substitution) | Rattachement **joueur ↔ équipe** | `joueurId`, `equipeId`, `permission`, `debutAbon`, `finAbon` |
| `abonJoueurLigue` | `abonJouLig` (substitution) | Rattachement **joueur ↔ ligue** | `joueurId`, `ligueId`, `permission`, `debutAbon`, `finAbon` |
| `abonEquipeLigue` | — | Rattachement **équipe ↔ ligue** | `equipeId`, `ligueId`, `permission`, `debutAbon`, `finAbon` |
| `TableSaison` | `saisonId` | Fenêtre de saison d'une ligue | `ligueRef`, `premierMatch`, `dernierMatch`, `saisonActive` |
| `TableEvenement0` | `event_id` | Journal terrain (base des stats) | `joueur_event_ref`, `equipe_event_id`, `match_event_id`, `code`, `souscode`, `chrono` |
| `TableMatch` | `match_id` / `matchIdRef` | Match | `eq_dom`, `eq_vis`, `ligueRef`, `date`, `alignementDom`, `alignementVis` |

Faits structurants :

1. **Les tables d'abonnement portent la vérité fonctionnelle** ; `TableJoueur.equipe_id_ref` est dénormalisé et le code moderne l'ignore (`base_de_donnees_syncstats.md` lignes 84, 106, 119).
2. **La clé primaire de `abonJoueurEquipe` est une clé de substitution** (`abonJouEq`, cf. usages `WHERE abonJouEq = …` dans [majJoueur2.php:84](../syncscript/majJoueur2.php), [attribueJoueursDansEquipe.php:45](../stats2/attribueJoueursDansEquipe.php)). Aucune contrainte `UNIQUE(joueurId, equipeId)` n'est visible côté code — **le schéma autorise donc en principe plusieurs lignes** par joueur, par équipe et par période. Les tables legacy sont `MyISAM` sans clés étrangères (`base_de_donnees_syncstats.md` lignes 5, 136).
3. **La multiplicité est modélisée par les périodes** `debutAbon`/`finAbon` et par la possibilité de plusieurs lignes, pas par un champ unique « équipe courante ».
4. **Numéro de chandail et position sont globaux au joueur** (`TableJoueur.NumeroJoueur`, `TableJoueur.position`) — **il n'existe aucun stockage par équipe** de ces attributs.
5. **Les statistiques sont attachées à l'événement**, qui porte à la fois `joueur_event_ref` et `equipe_event_id`. L'appartenance d'un joueur à une équipe **pour un fait de jeu donné** est donc déterminée par l'événement lui-même, indépendamment de l'abonnement « courant ».

**Conséquence :** au niveau purement SQL, les deux situations (multi-équipes même ligue, multi-ligues) sont **représentables**. Le risque ne vient pas du schéma mais des **hypothèses d'exclusivité dans le code**.

---

## 2. Parcours d'écriture — synthèse

| Script | Effet | Portée | Touche les autres inscriptions ? | Verdict multiplicité |
|---|---|---|---|---|
| [stats2/attribueJoueursDansEquipe.php](../stats2/attribueJoueursDansEquipe.php) | Affecte un joueur à une équipe | **Par ligue** (`ligueId`) | **Oui — clôt (`finAbon=NOW()`) toutes les autres inscriptions d'équipe actives du joueur dans la ligue** avant d'insérer la nouvelle (lignes 31-58) | **Exclusivité 1 équipe / ligue imposée** |
| [syncscript/majJoueur2.php](../syncscript/majJoueur2.php) | MAJ joueur + abonnements | **Par ligue** (`ligueId`) | **Oui — même logique de clôture des équipes ≠ cible dans la ligue** (lignes 66-98) ; met aussi à jour `TableJoueur` global (nom, numéro, position, ligne 39) | **Exclusivité 1 équipe / ligue imposée** |
| [scriptsphp/abonjoueurligue.php](../scriptsphp/abonjoueurligue.php) | Code 60 = retrait de ligue | **Par ligue** | **Oui — `DELETE` (destructif) de toutes les `abonJoueurEquipe` des équipes de la ligue** (lignes 39-57), sans filtre d'activité | Retrait OK mais **destructeur d'historique** |
| [scriptsphp/scriptprofil.php](../scriptsphp/scriptprofil.php) | Code 1/41 = MAJ profil ; code 40 = création + abon ligue | Global (profil) / par ligue (création) | MAJ profil = **écrit sur l'identité globale** (numéro, position, lignes 61) | Additif ; limitation globale §5 |
| [admin/enregistreMasseJoueurs.php](../admin/enregistreMasseJoueurs.php) | Création en masse | Par équipe | Non (crée toujours de **nouvelles** identités) | Additif, sans exclusivité |
| [scriptsphp/fusionne.php](../scriptsphp/fusionne.php) | Fusion de 2 joueurs | Global | Réaffecte `abonJoueurEquipe` de B vers A **sans dédup** (ligne 155) ; dédoublonne `abonJoueurLigue` par ligue (boucle 125-150) | **Peut créer de la multiplicité** ; réécrit alignements et événements |
| `scriptserveur/*.php` (`JoueurDeEquipeALigue`, `changeJoueurDeLigue`, `abonneJoueurDansLigue`) | Scripts de maintenance ad hoc | IDs codés en dur (12, 16, 3, 27) | API `mysql_*` **obsolète** (supprimée en PHP 7) | **Dormants / hors parcours courant** |

### Point central des écritures

Les deux chemins d'écriture nominatifs — `attribueJoueursDansEquipe.php` et `majJoueur2.php` — contiennent la même hypothèse implicite :

```
« Un joueur a au plus UNE équipe active PAR LIGUE. »
```

Chaque exécution, avant d'ajouter l'équipe cible, **ferme toutes les autres inscriptions d'équipe actives du joueur dans la ligue concernée** (jointure `abonJoueurEquipe ⋈ abonEquipeLigue` filtrée sur `ligueId`). Cette hypothèse :

- **casse le scénario A** (deux équipes d'une même ligue) : publier/mettre à jour l'équipe A clôt l'inscription à B ;
- **ne casse pas le scénario B** (deux ligues) : le filtre `ligueId` isole chaque ligue, l'équipe de L2 n'est jamais dans l'ensemble traité pour L1.

---

## 3. Parcours de lecture — synthèse

| Consommateur | Attribution de l'équipe | Multi-équipes même ligue | Multi-ligues | Référence |
|---|---|---|---|---|
| Construction ligue mobile | Effectif **par équipe** (`abonJoueurEquipe` actif) | **OK** (joueur apparaît dans A et B) | OK (appel par ligue) | [construitLigue.php:91-118](../syncscript/construitLigue.php) |
| Export abonnements mobile | idem, par équipe | OK | OK | [syncAbon.php:96-115](../syncscript/syncAbon.php) |
| Effectifs par ligue (JSON) | Par équipe + section « Libres » | OK pour l'effectif | OK | [joueursEquipeLigue2JSON.php:76-150](../stats2/joueursEquipeLigue2JSON.php) |
| Feuille de match | Effectif par équipe + **fenêtre de date** sur `abonJoueurEquipe` | **OK si les 2 inscriptions coexistent** | OK | [getEquipeDeMatch.php:81-121](../api/getEquipeDeMatch.php) |
| Calcul de match / score | **Piloté par `equipe_event_id`** des événements | **OK (indépendant des abonnements)** | OK | [calculeMatch2.php:230-318](../scriptsphp/calculeMatch2.php) |
| Profil joueur / stats | `GROUP BY saisonId, equipeId` (par équipe) | **OK — stats séparées par équipe** | **OK — séparées par ligue/saison** | [statsJoueur2JSON.php:56-138](../stats2/statsJoueur2JSON.php) |
| Publication T112 (lecture) | Par équipe + période, requêtes préparées | OK (lecture par équipe) | OK | [getPublicationJoueurs.php:14-25](../api/getPublicationJoueurs.php) |
| **Meneurs (leaders)** | **`MAX(nom_equipe)` sur TOUTES les inscriptions actives, sans filtre ligue** | **Étiquette d'équipe potentiellement erronée** | **Étiquette d'une autre ligue possible** | [joueursDansLigueMeneurs.php:122-136](../stats2/joueursDansLigueMeneurs.php) |
| **Stats ligue (code legacy)** | Dédup par **nom** de joueur ; cumul sous une seule équipe | **Fusion des stats des 2 équipes sous une étiquette** | — | [joueursDansLigue2JSON.php:185-261](../stats2/joueursDansLigue2JSON.php) |
| **Profil « mes joueurs »** | Jointure boguée `equipeId = abonEquipeLigue.ligueId` + mauvais index `$IAL` | **Déjà défaillant** indépendamment des scénarios | idem | [user2ligueetplus.php:118-138](../stats2/user2ligueetplus.php) |

### Points clés des lectures

- La **majorité des lectures structurantes** (effectifs, feuilles de match, score, profil joueur) attribuent correctement par équipe et **supportent la multiplicité**, parce qu'elles passent soit par `abonJoueurEquipe` filtré par équipe, soit par `equipe_event_id` de l'événement.
- Les **agrégations de tête de ligue** (`meneurs`, `joueursDansLigue2JSON`) contiennent des hypothèses d'unicité (sélection d'**une** équipe par `MAX`, dédup par **nom**) qui produisent des **étiquettes d'équipe fausses** ou des **cumuls mélangés** quand un joueur a plusieurs inscriptions actives. Les **comptes de buts/passes** restent généralement corrects (groupés par `event_id` ou par `joueur_id`), c'est **l'équipe affichée** qui est fausse.
- L'incrémentalité de synchronisation repose sur `TableJoueur.dernierMAJ` **global** : toute modification sur une inscription remonte le joueur dans **toutes** ses équipes/ligues à la prochaine synchro.

---

## 4. Numéro de chandail et position (section 5 de la mission)

- Stockés **uniquement** sur `TableJoueur` (`NumeroJoueur`, `position`). Aucun champ par équipe.
- Écrits par [majJoueur2.php:39](../syncscript/majJoueur2.php), [scriptprofil.php:61](../scriptsphp/scriptprofil.php).
- Lus partout tels quels : effectifs, feuilles de match, meneurs, publication T112 (`tj.NumeroJoueur`, `tj.position` dans [getPublicationJoueurs.php:14-16](../api/getPublicationJoueurs.php)).

**Conséquence :** un joueur **#12 attaquant dans A** et **#18 défenseur dans B** ne peut pas être représenté fidèlement. La dernière écriture gagne et s'applique **partout** (A, B, toutes ligues). Il s'agit d'une **limitation connue de modèle**, pas d'un bug. La mission demande explicitement de **ne pas corriger** ce point.

- **Impact feuille de match :** le numéro/position affichés sur la feuille proviennent de l'identité globale, donc identiques dans les deux équipes. L'affichage est faux pour l'une des deux, mais **aucune donnée historique d'événement n'est corrompue** (les événements ne stockent pas le numéro ; ils référencent `joueur_event_ref` + `equipe_event_id`). La modification du numéro **modifie l'affichage, pas l'historique des faits de jeu**.

---

## 5. Saison et périodes (section 6 de la mission)

- Les saisons sont des **fenêtres** (`premierMatch`/`dernierMatch`) par ligue (`TableSaison.ligueRef`). Deux ligues ont des saisons indépendantes — **aucun couplage**, favorable au scénario B.
- Les lectures de publication utilisent un **chevauchement de périodes** correct (`GREATEST(...) <= LEAST(...)`, [getPublicationJoueurs.php:20-24](../api/getPublicationJoueurs.php)) : robuste.
- Plusieurs lectures legacy comparent les abonnements à **une date unique** `$dateAbon` (= aujourd'hui si saison en cours, sinon `dernierMatch`) plutôt qu'à la date de chaque match : [joueursDansLigue2JSON.php:83-115](../stats2/joueursDansLigue2JSON.php), [calculeMatch2.php:102-135](../scriptsphp/calculeMatch2.php). Une inscription à B clôturée en cours de saison peut donc être vue comme inactive au moment du calcul, bien que les événements existent. **Les stats par événement restent justes** (elles ne dépendent pas de l'abonnement) ; c'est surtout la **résolution de l'étiquette d'équipe** qui est sensible à la date.
- Comparaisons de dates en **chaînes** (`$rangeeMatch['date'] >= $premierMatch`, [joueursDansLigue2JSON.php:58](../stats2/joueursDansLigue2JSON.php)) : fonctionne tant que le format reste `Y-m-d`, fragile sinon.

---

## 6. Scénarios C, D, E

- **Scénario C (trois équipes, régulier A + affilié B + affilié C).** Si B et C sont dans la **même ligue** que A : chaque écriture nominative (`attribue`/`majJoueur2`) n'en laisse **qu'une** active dans la ligue → B et C sont clôturées. Si A, B, C sont dans **trois ligues** : coexistence possible côté écriture, mais les agrégats de tête de ligue (meneurs) peuvent étiqueter le joueur avec l'équipe `MAX(nom_equipe)` d'**une autre ligue**.
- **Scénario D (modifications successives).** Tant que l'on reste dans **une même ligue**, ajouter B clôt A (ou inversement), car chaque passage par `majJoueur2`/`attribue` impose l'unicité ligue. La modification du numéro/position passe par l'identité globale (§4) et se répercute partout. Terminer l'inscription à B via `abonjoueurligue.php` code 60 **supprime (`DELETE`)** les lignes d'équipe de la ligue — destructif pour l'historique, et à portée ligue.
- **Scénario E (publication T112 répétée).** Le lot de publication **actuel est en lecture seule** : il n'écrit rien ([publication_lecture.php:33](../api/lib/publication_lecture.php) ouvre une transaction `START TRANSACTION READ ONLY` ; POST renvoie 405, cf. test [publication_lecture_test.php:21](../test/unit/publication_lecture_test.php)). Le risque de clôture involontaire de B **n'existe que pour un futur chemin d'écriture**. Si ce futur chemin réutilise `attribueJoueursDansEquipe.php` ou `majJoueur2.php`, alors publier l'effectif de A **clôturerait** l'inscription à B dans la même ligue (3e publication « MAJ effectif de A » = clôture de B). Si le futur chemin est **limité à l'équipe cible** (comme la lecture l'est déjà), B est préservée.

---

# LIVRABLE A — Joueur dans plusieurs équipes d'une même ligue

| Dimension | Verdict | Détail |
|---|---|---|
| Support du modèle SQL | **Supporté** (à confirmer §9) | Clé de substitution `abonJouEq`, pas d'unicité composite visible ; multiplicité représentable. |
| Compatibilité des écritures | **BLOQUANT** | `attribueJoueursDansEquipe.php` (31-58) et `majJoueur2.php` (66-98) clôturent toute autre inscription d'équipe active du joueur **dans la ligue**. L'exclusivité « 1 équipe active / ligue » est imposée par le code. |
| Compatibilité des lectures | **Risque modéré** | Effectifs et feuilles de match corrects si les inscriptions coexistent. Meneurs et stats-ligue : étiquette d'équipe erronée / cumul mélangé ([joueursDansLigueMeneurs.php:122](../stats2/joueursDansLigueMeneurs.php), [joueursDansLigue2JSON.php:185](../stats2/joueursDansLigue2JSON.php)). |
| Feuilles de match | **Aucun problème** (hors numéro) | Effectif par équipe + fenêtre de date sur `abonJoueurEquipe` ([getEquipeDeMatch.php:81](../api/getEquipeDeMatch.php)). Le numéro/position sont globaux (§4). |
| Statistiques | **Aucun problème** sur les comptes ; **risque modéré** sur l'étiquette | Comptes pilotés par `equipe_event_id` (justes). Étiquette d'équipe des agrégats de ligue potentiellement fausse. |
| ETL vers la plateforme Web | **À vérifier (hors dépôt)** | L'ETL réside hors `site-web`. S'il consomme les endpoints par équipe, il hérite d'une lecture correcte ; s'il s'appuie sur « l'équipe courante » du joueur, il hérite de l'hypothèse d'unicité. À confirmer dans le dépôt propriétaire. |
| Risques historiques | **Risque élevé** | `abonjoueurligue.php` code 60 fait un `DELETE` (pas un `finAbon`) des inscriptions d'équipe de la ligue (39-57) : perte d'historique d'affiliation. |
| Risque publication T112 | **BLOQUANT si écriture via scripts legacy** | Une future publication qui passerait par `attribue`/`majJoueur2` clôturerait l'affiliation concurrente dans la ligue (scénario E). |
| **Niveau de confiance** | **Élevé sur le code, moyen sur la production** | Les hypothèses d'unicité sont lues directement dans les requêtes ; l'absence de contrainte SQL reste à confirmer (§9). |

**Classement A :** Écritures = **Bloquant** · Historique (code 60) = **Risque élevé** · Lectures d'agrégats = **Risque modéré** · Numéro/position = **Limitation connue** · Effectifs / feuilles / score / profil = **Aucun problème identifié**.

---

# LIVRABLE B — Joueur dans plusieurs ligues différentes

| Dimension | Verdict | Détail |
|---|---|---|
| Support du modèle SQL | **Supporté** (à confirmer §9) | `abonJoueurLigue` et `abonJoueurEquipe` multi-lignes ; saisons indépendantes par ligue. |
| Compatibilité des écritures | **Aucun problème identifié** | `attribue`/`majJoueur2` sont **filtrés par `ligueId`** : traiter L1 ne touche pas les inscriptions de L2. `majJoueur2` insère l'abon ligue manquant sans clôturer l'autre ligue (46-61). |
| Compatibilité des lectures | **Risque modéré** | Construction mobile, effectifs, profil joueur : corrects (appels/regroupements par ligue). Meneurs : `MAX(nom_equipe)` **sans filtre de ligue** peut afficher l'équipe d'une autre ligue ([joueursDansLigueMeneurs.php:129-136](../stats2/joueursDansLigueMeneurs.php)). |
| Feuilles de match | **Aucun problème** (hors numéro) | Par équipe/date ; les deux ligues utilisent des équipes distinctes. |
| Statistiques | **Aucun problème** | Profil joueur groupé par ligue/saison/équipe ([statsJoueur2JSON.php:100-101](../stats2/statsJoueur2JSON.php)) : stats correctement ventilées par ligue. |
| ETL vers la plateforme Web | **À vérifier (hors dépôt)** | Même réserve qu'en A. |
| Risques historiques | **Risque modéré** | `fusionne.php` dédoublonne `abonJoueurLigue` par ligue mais pas `abonJoueurEquipe` (155) ; une fusion mal ciblée peut mélanger des identités multi-ligues. Hors du flux de publication nominal. |
| Risque publication T112 | **Risque faible** | La portée par ligue des écritures legacy protège l'autre ligue. Le risque se limite à l'étiquetage d'agrégats. |
| **Niveau de confiance** | **Élevé sur le code, moyen sur la production** | Le filtrage par `ligueId` est explicite dans les requêtes ; contraintes réelles à confirmer (§9). |

**Classement B :** Écritures = **Aucun problème identifié** · Meneurs (étiquette) = **Risque modéré** · Fusion = **Risque modéré** · Numéro/position = **Limitation connue** · Reste = **Aucun problème identifié**.

---

## 7. Comparaison A vs B

Le discriminant est la **portée des écritures** :

- Les scripts d'inscription nominatifs raisonnent **par ligue** et imposent **une seule équipe active par ligue**.
- Donc : **plusieurs ligues = supporté** (portées disjointes) ; **plusieurs équipes dans une même ligue = non supporté par les écritures** (clôture mutuelle).

Le modèle SQL et la plupart des lectures (y compris la lecture de publication T112 récente) supportent les deux ; ce sont les **écritures legacy** et quelques **agrégats de tête de ligue** qui portent l'hypothèse d'exclusivité.

---

## 8. Recommandation pratique

**Peut-on autoriser la publication d'un joueur dans plusieurs équipes ou ligues avec les mécanismes Legacy actuels ?**

- **Plusieurs ligues (B) : OUI**, sous réserve de confirmer en production l'absence de contrainte d'unicité (§9) et d'accepter deux limitations connues : (i) numéro/position globaux au joueur, (ii) étiquette d'équipe possiblement fausse dans les meneurs. Ces limites n'altèrent ni les effectifs, ni les feuilles de match, ni les scores, ni les stats ventilées du profil.

- **Plusieurs équipes d'une même ligue (A) : NON** avec les chemins d'écriture actuels. Il faut **éviter** que la publication T112 passe par :
  - `stats2/attribueJoueursDansEquipe.php`
  - `syncscript/majJoueur2.php`
  - `scriptsphp/abonjoueurligue.php` code 60 (DELETE destructif)

  car ils clôturent/suppriment l'affiliation concurrente **dans la ligue**.

**Chemins à éviter ou à corriger pour supporter A :**
1. Ne **pas** réutiliser les scripts ci-dessus pour une écriture de publication ; prévoir un **contrat d'écriture gouverné, limité à l'inscription (joueur, équipe) cible** (comme la lecture de publication l'est déjà), qui n'émette un `finAbon` que sur l'inscription visée et jamais sur les autres équipes de la ligue.
2. Remplacer le `DELETE` du code 60 par un `finAbon` ciblé pour préserver l'historique.
3. Corriger l'attribution d'équipe des agrégats (`meneurs`, `joueursDansLigue2JSON`) pour joindre `abonJoueurEquipe` sur `equipe_event_id` **et** filtrer par `ligueId` (à traiter hors de cette mission).
4. Confirmer avec le propriétaire de l'ETL (hors `site-web`) qu'il lit par équipe et non « l'équipe courante ».

**Rien n'est implémenté par cette mission.** Les points 1-4 sont des recommandations à mandater séparément auprès des agents propriétaires concernés.

---

## 9. Requêtes de diagnostic (lecture seule) à faire exécuter sur la base réelle

> À exécuter par l'utilisateur sur la base Legacy. **Toutes en lecture seule.** Elles servent à confirmer les hypothèses du présent audit (absence de contrainte d'unicité ; existence de cas réels d'affiliation multiple).

**9.1 — Contraintes et index réels sur les tables d'abonnement :**

```sql
SHOW CREATE TABLE abonJoueurEquipe;
SHOW CREATE TABLE abonJoueurLigue;
SHOW INDEX FROM abonJoueurEquipe;
SHOW INDEX FROM abonJoueurLigue;
```

**9.2 — Joueurs déjà inscrits dans plusieurs équipes d'une même ligue, simultanément (cas A existants) :**

```sql
SELECT aje.joueurId, ael.ligueId, COUNT(DISTINCT aje.equipeId) AS nbEquipes,
       GROUP_CONCAT(DISTINCT aje.equipeId) AS equipes
FROM abonJoueurEquipe aje
JOIN abonEquipeLigue ael ON ael.equipeId = aje.equipeId
WHERE aje.debutAbon <= NOW() AND aje.finAbon > NOW()
  AND ael.debutAbon <= NOW() AND ael.finAbon > NOW()
GROUP BY aje.joueurId, ael.ligueId
HAVING nbEquipes > 1
ORDER BY nbEquipes DESC;
```

**9.3 — Joueurs actifs dans plusieurs ligues (cas B existants) :**

```sql
SELECT joueurId, COUNT(DISTINCT ligueId) AS nbLigues,
       GROUP_CONCAT(DISTINCT ligueId) AS ligues
FROM abonJoueurLigue
WHERE debutAbon <= NOW() AND finAbon > NOW()
GROUP BY joueurId
HAVING nbLigues > 1
ORDER BY nbLigues DESC;
```

**9.4 — Doublons exacts d'inscription (joueur, équipe) actifs (symptôme d'absence d'unicité) :**

```sql
SELECT joueurId, equipeId, COUNT(*) AS nbLignes
FROM abonJoueurEquipe
WHERE debutAbon <= NOW() AND finAbon > NOW()
GROUP BY joueurId, equipeId
HAVING nbLignes > 1;
```

**9.5 — Cohérence : un joueur a-t-il des événements (faits de jeu) pour une équipe à laquelle il n'a plus d'inscription active ?** (mesure l'écart entre historique d'événements et abonnements courants)

```sql
SELECT e.joueur_event_ref, e.equipe_event_id, COUNT(*) AS nbEvenements
FROM TableEvenement0 e
LEFT JOIN abonJoueurEquipe aje
  ON aje.joueurId = e.joueur_event_ref
 AND aje.equipeId = e.equipe_event_id
 AND aje.debutAbon <= NOW() AND aje.finAbon > NOW()
WHERE e.code < 10 AND aje.abonJouEq IS NULL
GROUP BY e.joueur_event_ref, e.equipe_event_id
ORDER BY nbEvenements DESC
LIMIT 100;
```

---

## 10. Limites de l'audit

- Aucune exécution SQL ni test contre une base réelle : les résultats des §9 restent à produire par l'utilisateur.
- Les contraintes d'unicité **réellement déployées** ne sont pas vérifiables depuis le dépôt (pas de dump). L'absence constatée est une absence **dans le code**, pas une preuve d'absence en base.
- L'**ETL vers la plateforme Web** et le **backend SyncStats** (notions « régulier/affilié », T112) sont hors du dépôt `site-web` et n'ont pas été audités ; seules les conséquences côté Legacy sont traitées.
- Les scripts `scriptserveur/*` utilisant `mysql_*` sont probablement non fonctionnels sur PHP moderne et traités comme dormants.
- Aucune modification, migration, écriture ou commit n'a été effectué.
