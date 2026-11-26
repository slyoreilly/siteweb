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
  50 candidats maximum. Les caractères `%`, `_`, `!` sont littéraux.

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
