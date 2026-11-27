# Prévisualisation SyncUp → Legacy — raccordement en lecture seule

## Plan documenté avant évolution backend (25 novembre 2026)

Réutiliser les trois routes `getPublication*.php`, le client HTTP et le service
`PublicationLegacy` déjà présents. Étendre leurs contrats pour conserver les
saisons et ajouter une recherche nominative bornée hors de l’équipe cible.
Aucune migration, aucun appel aux scripts historiques de modification, aucune
utilisation de l’inbox d’écriture ou de son ACK. La déduplication de l’inbox
concerne les commandes et ne constitue pas un mécanisme de lecture.

Le backend vérifie un rôle `admin` global actif (portée UUID vide), via le
repository des affectations existant. Sans mapping organisation moderne → ligue
Legacy, les rôles locaux sont refusés plutôt que supposés autorisés sur tout le
catalogue. Le secret interservices `Legacy:Token` reste exclusivement serveur.

Le schéma consulté dans les scripts utilise `abonJouEq`, et non `id`, pour
l’inscription joueur-équipe. Le HCR n’est pas établi comme référence fiable
Legacy : aucune correspondance nominative ne sera automatique.

Un résultat de recherche vide ou tronqué ne prouve pas une identité nouvelle.
Les confirmations sont conservées en mémoire dans la page seulement. Tant que
des identités du T112 complet restent irrésolues, les retraits sont suspendus.
Le registre canonique courant `/equipes/{id}/membres` est une lecture complète
des inscriptions actives, mais ne prouve pas à lui seul que l’effectif provient
d’un T112 complet : une attestation explicite sera exigée dans la page avant de
proposer des fins d’inscription.

Les saisons seront choisies explicitement, sans déduire la saison courante de
leur ordre de création. Les lectures saisonnières portent sur le chevauchement
des périodes d’inscription avec les dates de saison, et non un instantané exact
de l’effectif à une date arbitraire.


## Contrat réel de lecture

Préfixe backend : `/api/administration/publication-legacy` ; Bearer SyncUp requis.
Les méthodes restent GET :

- `/ligues` : ligues avec `id`, `nom`, `saisons[{id,debut,fin,active}]`.
- `/ligues/{ligueId}/equipes?saisonId=…` : `id`, `nom`, `ligueId`,
  `saisonId`, `abonnements[{debut,fin}]`. Sans saison : inscriptions actives maintenant.
- `/equipes/{id}/joueurs?ligueId=…&saisonId=…` : joueurs et inscriptions dans
  cette ligue/période ; une cible hors contexte est refusée.
- `/joueurs/recherche?nom=…` : recherche globale nominative (3 à 120 octets),
  50 candidats maximum. Les caractères `%`, `_`, `!` sont littéraux. L'extension
  Legacy du 27 novembre ci-dessous décrit les paramètres à relayer lors d'un
  prochain raccordement Backend/SyncUp ; leur prise en charge aval n'est pas
  attestée par cette livraison Legacy.

Enveloppe backend : `{legacyDisponible, erreurMessage, elements, lectureComplete}`.
Le champ `lectureComplete=false` signale une recherche tronquée ; une erreur
technique renvoie `legacyDisponible=false`, une liste vide et une lecture
incomplète. Le client navigateur refuse les enveloppes absentes ou incomplètes
pour les effectifs. Un HTTP 206, du JSON tronqué, une erreur réseau ou une
identité sans identifiant ne peuvent devenir un effectif vide valide.

Joueur : `{id,prenom,nom,hcr,numeroChandail,position,inscriptionId,
provenanceInscription,debutAbon,finAbon}`. `prenom` et `hcr` valent null ; `nom`
contient le nom complet Legacy. La provenance vaut `inconnu`, aucun historique
ne permet ici de la qualifier de manuelle ou T112. Les candidats globaux n’ont
pas d’inscription cible. Ils n’exposent ni contacts ni date de naissance.

Le backend réutilise `Legacy:BaseUrl`, `Legacy:Token`, `Legacy:TimeoutSeconds`.
PHP réutilise `LEGACY_MATCH_ACTIF_TOKEN` et `chargeSecrets.php`. Le navigateur
ne reçoit que les résultats autorisés et transmet son propre Bearer SyncUp.
Les consultations PHP s’exécutent dans une transaction `READ ONLY` fermée à la
fin de la requête ; aucune inbox ni déduplication de commande n’est sollicitée.

Le différentiel ne propose jamais de création d’identité sur une absence de
résultat. Un candidat nominatif unique exige une confirmation aussi. Une
confirmation vers un joueur hors cible produit `a_ajouter_inscription` ; vers
un joueur inscrit, `inchange` ou `a_mettre_a_jour`. Les doublons de confirmation
et les périodes multiples suspendent les opérations ambiguës. Les retraits
nécessitent la vérification explicite du T112 complet et toutes les identités
résolues, indépendamment de la sélection de publication.

## Limites et passage ultérieur à l’écriture

Les droits organisationnels nécessiteront un mapping gouverné vers les ligues,
sans rapprochement implicite par nom. L’attestation du T112 devra idéalement
être remplacée par une preuve backend issue du reçu d’import officiel.
La recherche partielle n’est pas une preuve d’inexistence. Les autres
appartenances ne sont pas modifiées ; leur catalogue détaillé n’est pas exposé.
Les saisons représentent des chevauchements, pas un instantané historique.

Avant une publication effective : mandat distinct, droits contextuels reliés
aux ligues, identités et saisons confirmées, résolution des périodes multiples,
relecture serveur au moment de l’action, contrat d’écriture gouverné et
idempotence/inbox éprouvées. Aucune route d’écriture n’est ajoutée par ce lot.


## Livraison et validations du 25 novembre 2026

### Composants modifiés

- Legacy : les trois routes `api/getPublication{Ligues,Equipes,Joueurs}.php`,
  nouvelle route `api/getPublicationRechercheJoueurs.php`, socle partagé
  `api/lib/publication_lecture.php`, test `test/unit/publication_lecture_test.php`.
- Backend : contrôleur, DTO, interfaces, service et client HTTP
  `PublicationLegacy`, tests `PublicationLegacyClientTests`, contrat
  `workflow_previsualisation_publication_legacy.md` et son index.
- SyncUp : `apiLegacy.js`, `publicationLegacy.js`, page de prévisualisation,
  hook de lecture (conservation du message d’erreur), tests de publication et
  nouveau test du client API. Le mode fictif était déjà désactivé au démarrage.

Le précédent audit Legacy n’a pas été retrouvé sous un fichier identifiable
dans les dépôts consultés. Les sources existantes ont été réexaminées : routes
 de publication, `getEquipesAbonneesLigue.php`, références à `abonJouEq`, lectures
 de `TableSaison`, règles backend/T112, chargeur de secrets, runbook inbox/ACK.
Les scripts historiques ont seulement été lus, jamais exécutés.

### Tests locaux

- Suite SyncUp complète : **121/121** ; dont **62** ciblent le différentiel et
  le client de publication. Les anciennes attentes de création automatique ont
  été remplacées par des attentes de confirmation ou d’identité non établie.
- Backend publication : **16/16** (authentification HTTP 401/403, panne réseau,
  HTTP 500/206, JSON invalide/tronqué, contexte saisonnier, recherche partielle,
  contrôle des affectations administratives).
- PHP : **13 vérifications** réussies dans `php:8.3-cli`, sans réseau ni SQL ;
  syntaxe des quatre routes et du socle vérifiée.
- Compilations Vite et API .NET réussies. L’API a été compilée vers un dossier
  temporaire distinct pour éviter les DLL verrouillées par le service local ;
  celui-ci n’a été ni arrêté ni redémarré.
- `git diff --check` réussi pour les modifications des trois dépôts.

Les tests SQL des nouvelles requêtes n’ont pas été exécutés contre une base
Legacy. La correction `abonJouEq` est étayée par les scripts existants ; le
schéma vivant reste à confirmer. Les tests automatisés utilisent des réponses
de test et ne constituent aucune preuve de lecture de joueurs réels.

### Lectures réelles, exclusivement GET

Serveur configuré : `https://syncstats.com/`, secret chargé depuis la
configuration privée backend sans l’imprimer. Les en-têtes JSON et User-Agent
ont permis de dépasser un premier refus HTTP 406 ; le client HTTP a été aligné.

| Contrôle réel | Résultat observé |
| --- | --- |
| Ligues sans Bearer | HTTP 401 |
| Ligues avec Bearer | HTTP 200, 147 ligues |
| Ligue 314 | « 3 vs 3 2026 » |
| Équipes de la ligue 314 | HTTP 200, 11 équipes ; équipe 2500 « Blancs » |
| Effectif des équipes 2500, 2508, 2504, 2499 | HTTP 500 pour chaque lecture |
| Recherche globale nominative | HTTP 404, route non déployée |
| Métadonnées saisonnières dans la réponse déployée | Absentes (`id`, `nom` seulement) |

Les cas joueur déjà présent, joueur hors cible, affilié sélectionné ou ignoré,
homonymes, panne et lecture partielle sont couverts **localement**, mais les
scénarios joueurs n’ont pas pu être validés sur données réelles. Aucun effectif
fictif n’a servi à contourner ce blocage. Aucune identité personnelle n’est
reproduite dans ce rapport.

### État final et limites de réception

Le code de raccordement est prêt pour revue locale. Le critère de réussite
bout en bout sur joueurs réels **n’est pas encore démontré** : le service
déployé renvoie 500 pour les effectifs et ne fournit ni recherche globale ni
contrat saisonnier étendu. Les versions PHP, backend et SyncUp devront être
livrées ensemble dans un mandat ultérieur autorisant le déploiement, puis les
scénarios réels devront être rejoués. Il faut notamment confirmer le schéma
`TableSaison`/`abonJoueurEquipe` avant cette livraison et vérifier les périodes
réelles. Le client refuse le contrat ancien incomplet au lieu de produire un
différentiel présenté comme complet.

Le catalogue est réservé aux administrateurs globaux actifs. L’extension aux
administrateurs d’organisation reste conditionnée au mapping de droits vers
Legacy. L’état officiel T112 est attesté par l’administrateur dans la page ; il
n’est pas automatiquement prouvé par un reçu backend dans ce lot.

Aucune écriture métier, création de joueur, modification d’inscription,
publication, migration, livraison distante, opération de push ou commit n’a été
effectuée. Les modifications préexistantes/concurrentes concernant le
laboratoire audiovisuel n’ont pas été incluses dans ce travail.

## Option B — contrat Legacy pour Backend et SyncUp (27 novembre 2026)

Cette extension concerne exclusivement `api/getPublicationRechercheJoueurs.php`.
Elle conserve le Bearer interservices, GET, JSON UTF-8, `Cache-Control: no-store`
et la transaction `READ ONLY` de `publication_lecture.php`. Aucun changement
Backend ou SyncUp n'est livré ici. Les consommateurs devront relayer les
paramètres et conserver les deux nouveaux champs sans en déduire une écriture.

### Paramètres de requête

| Paramètre | Contrat |
| --- | --- |
| `nom` | Obligatoire, chaîne ; au moins 3 octets après `trim`, au plus 120 octets avant `trim`, comme auparavant. Recherche de sous-chaîne ; `%`, `_`, `!` sont littéraux et les apostrophes restent des valeurs liées. Casse et accents suivent la collation SQL Legacy. |
| `ligueId` | Facultatif, entier positif de 1 à 2147483647. Absent : recherche globale conservée sur `TableJoueur`, sans filtre d'inscription. Présent : identités inscrites à une équipe de cette ligue dans la période retenue. |
| `saisonId` | Facultatif, même domaine entier ; exige `ligueId`. La saison doit appartenir à la ligue et fournir une unique période exploitable via `publicationPeriode`. Absent : les deux bornes sont le même `NOW()` SQL, capturé une fois. |
| `equipeId` | Facultatif, même domaine entier ; exige `ligueId`. La cible doit avoir une inscription `abonEquipeLigue` admissible dans la période. Elle sert à prioriser les candidats, sans exclure ceux des autres équipes de la ligue. |

Un paramètre entier vide, nul au sens HTTP (`"null"`), nul numérique, négatif,
hors borne ou de type tableau est invalide. Sans `ligueId`, fournir `saisonId`
ou `equipeId` produit HTTP 400 ; la recherche globale simple reste inchangée.
Une ligue sans inscription admissible renvoie une liste vide, sans nouvelle
erreur « ligue inconnue ». Les contrôles saison/cible existants restent les
références de contexte.

### Réponse HTTP 200

L'objet possède `elements` (tableau, jamais null, de 0 à 50 identités distinctes)
et `lectureComplete` (booléen). Une identité est `TableJoueur.joueur_id` : deux
homonymes restent deux candidats. Le 51e candidat distinct sert uniquement à
détecter la troncature ; `lectureComplete` est vrai pour 0 à 50 candidats et
faux à partir de 51. Il n'y a pas de pagination ni de total exposé.

| Champ d'un candidat | Type JSON et sens |
| --- | --- |
| `id` | entier, identifiant Legacy du joueur |
| `prenom` | null |
| `nom` | chaîne, nom complet Legacy |
| `hcr` | null |
| `numeroChandail` | chaîne ou null, valeur existante de `TableJoueur` |
| `position` | chaîne ou null, valeur existante de `TableJoueur` |
| `inscriptionId` | null : la recherche ne choisit aucune inscription parmi les appartenances |
| `provenanceInscription` | chaîne constante `"inconnu"` |
| `debutAbon`, `finAbon` | null, aucune période unique présumée |
| `equipesDansLigue` | tableau distinct de `{id: entier, nom: chaîne}` pour les appartenances admissibles dans la ligue/période ; tri nom d'équipe puis identifiant ; toutes les équipes des candidats retenus sont rendues |
| `presentDansEquipeCible` | booléen strict ; vrai si au moins une appartenance admissible concerne `equipeId` |

En recherche globale, `equipesDansLigue=[]` et
`presentDansEquipeCible=false` : aucune ligue/cible n'est évaluée. Avec une
ligue sans cible, les appartenances sont renseignées et le booléen vaut false.
Ces valeurs ne prouvent pas l'absence d'appartenance dans un autre contexte.

L'admissibilité utilise `abonJoueurEquipe` et `abonEquipeLigue`, avec
`permission < 31` sur les deux. Il doit exister une **intersection commune** :
`GREATEST(debutEquipeLigue, debutJoueurEquipe, debutPeriode) <=
LEAST(finEquipeLigue, finJoueurEquipe, finPeriode)`. Les bornes sont inclusives.
Deux inscriptions séparées dans la même saison ne suffisent donc pas. Une
période nulle ne satisfait pas cette comparaison. `abonJoueurLigue` n'est
jamais exigée. Les inscriptions multiples ne multiplient ni les identités ni
les équipes du JSON.

L'ordre des candidats est : présence cible décroissante, nom Legacy, puis
identifiant joueur croissant, **avant** la limite de 50 identités. Si plus de
50 joueurs de la cible correspondent au nom, seuls les 50 premiers selon ce
tri sont rendus et `lectureComplete=false`. Les inscriptions multiples
doivent toujours être relues par la route d'effectif avant toute décision
ultérieure ; ce JSON n'est pas un contrat d'affectation.

Exemple fictif :

```http
GET /api/getPublicationRechercheJoueurs.php?nom=O%27Neil&ligueId=314&saisonId=42&equipeId=2500
Authorization: Bearer <secret interservices côté serveur>
```

```json
{
  "elements": [
    {
      "id": 9001,
      "prenom": null,
      "nom": "Élodie O'Neil",
      "hcr": null,
      "numeroChandail": "0",
      "position": "C",
      "inscriptionId": null,
      "provenanceInscription": "inconnu",
      "debutAbon": null,
      "finAbon": null,
      "equipesDansLigue": [
        {"id": 2500, "nom": "Blancs"},
        {"id": 2508, "nom": "Rouges"}
      ],
      "presentDansEquipeCible": true
    }
  ],
  "lectureComplete": true
}
```

### Erreurs et interprétation aval

Les erreurs gardent leur objet `{etat, detail?}`, sans enveloppe de succès :

| HTTP | `etat` | Condition |
| --- | --- | --- |
| 400 | `parametre_invalide` | Nom/entier invalide (`detail` existant) ou contexte sans ligue (`detail: "ligueId requis avec saisonId ou equipeId"`) |
| 401 | `non_autorise` | Bearer absent ou incorrect |
| 405 | `methode_non_autorisee` | Méthode autre que GET ; `Allow: GET` |
| 422 | `saison_inexploitable` | Saison absente de la ligue, multiple, dates manquantes ou inversées |
| 422 | `equipe_hors_contexte` | Cible sans inscription ligue/période admissible |
| 503 | `service_non_configure` | Secret interservices non configuré |
| 503 | `erreur_consultation` | Erreur de lecture ou de sérialisation |

L'authentification reste contrôlée avant les paramètres, puis le nom, les
entiers, la dépendance à la ligue, la saison et enfin la cible. Backend doit
préserver la distinction entre erreur, résultat vide et résultat tronqué dans
son enveloppe existante. SyncUp doit envoyer la sélection courante à chaque
recherche, invalider les résultats lorsque ce contexte change, afficher les
appartenances et utiliser le booléen de présence pour la présentation. Aucune
absence, même avec `lectureComplete=true`, ne confirme une nouvelle identité.

### Vérifications locales et SQL à réception

Commandes sans base réelle :

```text
python test/unit/publication_recherche_sql_test.py
php -n test/unit/publication_recherche_test.php
php test/unit/publication_lecture_test.php
php -l api/getPublicationRechercheJoueurs.php
```

Les tests SQL utilisent les requêtes de production sur SQLite avec adaptation
de `GREATEST`/`LEAST` ; ils ne valident ni la collation, ni les index, ni le
schéma vivant MySQL/MariaDB. Les tests PHP exercent la route et les fonctions
de validation/sérialisation réelles, avec lectures simulées. Aucun accès réel
n'est supposé ou effectué pour cette extension.

Vous pouvez exécuter le script de lecture
`test/sql/publication_recherche_verifications.sql` sur votre environnement
Legacy autorisé, en adaptant les identifiants, dates et motif littéral. Il
permet de vérifier le schéma, les 51 identités ordonnées, les appartenances
distinctes d'un candidat et les inscriptions multiples sous-jacentes. Pour
une saison, reprenez exactement `premierMatch`/`dernierMatch` ; sans saison,
utilisez le même instant pour les deux bornes. Comparez ensuite les 50
premières identités et le marqueur de troncature avec la réponse GET. Vérifiez
aussi une cible hors contexte et une saison d'une autre ligue (HTTP 422).
Une vérification des plans `EXPLAIN` sur ces SELECT reste nécessaire avant
livraison sur un gros catalogue. Ce lot n'ajoute aucun index ni migration.
