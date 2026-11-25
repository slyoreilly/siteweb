# Réduction de l’occupation MySQL du Legacy

Mandat du 25 novembre 2026. Corrections locales seulement ; aucun commit, push,
déploiement ou accès à la production. Les risques ci-dessous ne constituent pas
une preuve de la cause historique de la saturation.

## Gestion observée

- `scriptsphp/defenvvar.php` local (non suivi par Git) et son exemple ouvrent
  immédiatement une connexion `mysqli`, puis règlent son charset à `utf8`.
  Si `$conn` existe déjà, une réinclusion vérifie `SELECT 1` et tente une
  reconnexion en cas d’échec. `require_once` évite cette réexécution.
- Les hôtes configurés ici n’ont pas de préfixe `p:` : connexions ordinaires,
  réutilisées dans une exécution PHP, sans pool applicatif entre requêtes HTTP.
  Une variable globale ou statique PHP n’est pas un pool inter-requêtes.
  Sans fermeture explicite, une connexion ordinaire est libérée à la fin du script ;
  un worker CLI en boucle peut donc la conserver indéfiniment.
- `phpobjects/Database.php` réutilise en priorité la connexion globale avec
  `ping()`. Les quatre capteurs de `enregistreStatutCam.php` ne créent donc pas
  normalement quatre connexions. Les vérifications `ping()` supplémentaires
  ajoutent toutefois des allers-retours. Son chemin de secours utilise
  `new mysqli` et des variables de configuration locales dont la disponibilité
  dépend de la première inclusion : il n’a pas été modifié dans ce mandat.
- D’autres chemins ouvrent explicitement des connexions, par exemple
  `syncscript/dechargeMatchs_2.php` (`$connSB`) et les scripts de connexion.
  L’unicité de connexion n’est donc pas garantie à l’échelle du dépôt.
- Aucune modification de `defenvvar.php`, de son exemple ou de `Database.php`.
  Après une fermeture ciblée, `$conn = null` permet au mécanisme existant de
  rouvrir une connexion sans interroger un objet `mysqli` déjà fermé.

**Limites :** le fichier privé effectivement installé en production, la version
PHP/MySQL, les paramètres de persistance, les limites du compte SQL, les règles
du proxy et le nombre de processus PHP/instances ne sont pas vérifiés.
Les valeurs et secrets du fichier local ne sont pas reproduits ici.

## Corrections effectuées

| Fichiers | Avant | Après et gain attendu | Risques / limites |
| --- | --- | --- | --- |
| `getStreamDeviceConfig.php`, `syncPushTempsCam.php`, `.1.php`, `.2.php` | Une connexion reste ouverte durant toutes les pauses de 5 s. | Une connexion par itération complète de SQL ; fermeture après consommation des résultats, avant la pause et la réponse. Jusqu’à 5 secondes de connexion occupée supprimées par pause. | Une nouvelle connexion est nécessaire à l’itération suivante ; davantage d’ouvertures, mais pas entre les requêtes SQL rapprochées. Les cadences et conditions de sortie sont conservées. |
| `syncPushTempsCam.2.php` | Les demandes d’ajout vidéo sont traitées une seule fois avant la boucle. | Ce traitement reste exécuté une seule fois, sur la première connexion réutilisée par la première lecture. | Les mises à jour et l’ordre du traitement ne changent pas. |
| `getAppareilsSurSurface.php` | Des HEAD HTTP sont entrelacés avec les lectures SQL, sans timeout explicite. | Les 20 vidéos au maximum par caméra sont matérialisées ; toutes les lectures des remotes sont terminées avant fermeture SQL, puis les HEAD s’exécutent sans connexion SQL. Délais : connexion 2 s, total 4 s par appel. | Un serveur lent peut désormais être considéré indisponible ; les vidéos suivantes restent essayées dans le même ordre. Mémoire supplémentaire proportionnelle aux vidéos candidates. Jusqu’à 80 s de réseau par caméra restent possibles si les 20 appels expirent ; ce délai n’occupe plus MySQL. |
| `getMonitoringFilesIndex.php` | Connexion conservée durant les recherches de dossiers et métadonnées. | Fermeture dès que les lignes sont matérialisées, également sur exception SQL interceptée. Toute la durée d’inspection des fichiers est retirée de l’occupation SQL. | Pagination, authentification et JSON conservés. L’ouverture initiale avant les refus HTTP reste inchangée, car les paramètres d’authentification sont aussi initialisés par `defenvvar.php`. |
| `api/sync/ack_worker.php` | Connexion conservée entre les lots, notamment durant `sleep(1)` en mode `--loop`. | Fermeture en `finally` après chaque lot ; réouverture au prochain lot. Une seconde d’occupation supprimée par pause inactive, ainsi que l’attente entre lots. | Les appels réseau conservent leur connexion et leur verrou SQL : voir ci-dessous. Une connexion supplémentaire par lot est le compromis retenu. |
| `scriptsphp/enregistreStatutRemote.php` | `if ($codeEtat=$tmpSel[0])` écrase l’état reçu. Si l’ancien état est non nul, toutes les transitions restent bloquées ; si l’ancien état vaut `0`, `dernierModif` est réécrit à chaque heartbeat. | Une comparaison `==`, cohérente avec l’endpoint caméra, sélectionne la branche sans altérer l’état reçu. `dernierModif` change uniquement lors d’une transition, y compris depuis/vers `0`. | Toujours une lecture et une écriture par appel : pas de réduction du nombre de requêtes ni de gain de durée SQL revendiqué. La correction rend les transitions à nouveau visibles aux consommateurs et aux éventuels triggers existants. Elle ne restaure pas l’historique perdu. |

Les chemins d’erreur historiques par `die`/`exit` terminent la requête PHP et
libèrent ses connexions ordinaires. Les changements ne créent ni transaction
ni fermeture à l’intérieur d’une transaction existante dans ces traitements.

## Worker : protection à conserver

`syncAcquireRowLock()` utilise `GET_LOCK('sync_inbox_ack_<id>', 0)` ;
`syncReleaseRowLock()` libère ce verrou en `finally`. Fermer MySQL avant
`SyncAckClient::sendOnce()` libérerait le verrou et permettrait un second
acquittement concurrent. Ce changement est donc volontairement écarté.
Le client possède déjà un timeout total configurable (8 s par défaut dans
la configuration locale) et un timeout de connexion plafonné à 5 s.
Libérer aussi la connexion durant HTTP nécessite un mécanisme de réservation
durable, une analyse d’idempotence et un arbitrage séparé ; hors correctif urgent.

## Heartbeats et HTTP 429 : recommandations à arbitrer

Les deux endpoints de statut ouvrent SQL avant le traitement. Aucun limiteur
pré-SQL ni réponse 429 n’a été trouvé dans ces deux endpoints. Le `Retry-After`
de `api/getEvenementInstant.php` concerne un 503 après une erreur SQL, pas une
protection avant connexion. Les règles du frontal de production sont inconnues.

Les sources locales voisines montrent déjà des protections clientes :

- SyncStatsRef : `TransmissionStatut.kt` limite à une requête active, cadence
  nominale de 15 s, conserve au plus le dernier changement significatif et
  applique une temporisation d’échec de 15 à 120 s ; les 429/503 tiennent compte
  de `Retry-After`. Les fluctuations batterie/mémoire/température ne définissent
  pas un changement significatif ; les changements d’état restent transmis.
- SyncCam : `StabilisationReseau.kt` possède un cooldown sur 429 de 10, 30, 60,
  puis 120 s. Cela ne prouve pas que les binaires déployés utilisent ce code.
- Les anciens chemins Java consultés ne démontrent pas une prise en charge
  de `Retry-After` ; leur comportement et toutes les versions installées restent
  à valider. Un rejet ne doit pas être supposé ralentir un ancien client.

**Aucun limiteur ajouté.** Une limite par IP pénaliserait les appareils derrière
le même NAT. Une clé appareil devrait au minimum séparer endpoint, compte et
`telId` validés ; cet identifiant fourni par le client n’est pas une preuve
d’authenticité et peut être changé pour contourner une limite.

Option à étudier après le tournoi : dédoublonnage très court des heartbeats
sans changement significatif, sous verrou atomique par appareil, en conservant
une marge sous 15 s et les changements légitimes (état, aréna, compte,
configuration). Le choix entre 429 + `Retry-After` et réponse compatible doit
être validé avec les anciens clients. Un 200 synthétique peut masquer une
écriture échouée et ne doit pas être adopté sans contrat explicite.

Un cache fichier n’est acceptable qu’après vérification d’un stockage local
partagé entre processus, de `flock`, des permissions, du nettoyage, de limites
de taille et d’une éventuelle distribution multi-hôtes. APCu n’assure pas à lui
seul une coordination inter-hôtes. Ni ces conditions d’hébergement ni un
contrat fiable de rejet des anciens clients ne sont établis ici.
Ne pas ajouter ce mécanisme avant ces vérifications.

Observation laissée hors changement fonctionnel : les alarmes de caméra peuvent
envoyer des courriels avant la dernière écriture de statut. Cela ne démontre
pas la cause de l’incident. La limitation de fréquence serveur et les évolutions
architecturales restent reportées après le tournoi.

## Validation locale

Les tests exécutent les scripts PHP applicatifs avec des doublures SQL, HTTP
et d’attente ; aucune base réelle, requête réseau ou attente de cinq secondes.
Ils utilisent des jeux représentatifs et des assertions sur les réponses et
les écritures, sans prétendre valider le moteur SQL ou les appareils installés.
Les quatre suites passent sous **PHP 7.4 et PHP 8.3**. La syntaxe des huit
fichiers applicatifs et des quatre fichiers de tests passe sur ces deux
versions, dans des images locales sans réseau et avec le dépôt en lecture seule.
`git diff --check` passe également.

| Test | Couverture |
| --- | --- |
| `test/unit/statut_remote_transitions_test.php` | Huit cas : `10→10`, `10→20`, `20→10`, `10→0`, `0→10`, `0→0`, entier `10` versus chaîne `"10"`, insertion initiale. Vérification de l’état écrit, de la branche MOD1/MOD2, de `dernierMaJ`, de la présence de `dernierModif` seulement sur transition et du nombre de requêtes (deux). |
| `test/unit/occupation_connexions_test.php` | Quinze scénarios lancés séparément, dont les modes développement/production du fichier local et de son exemple. Long polls vides, configuration Stream immédiate et après une pause, événements/buts, clips, périodes, aréna et abonnements des trois variantes, paramètres caméra et sport de `.2`, demandes événement/clip et exclusion d’une autre surface, demandes traitées une seule fois malgré deux itérations, réponse caméras/remotes, vidéo absente puis présente et timeouts. |
| `test/unit/monitoring_index_connexions_test.php` | Quatre cas : réponse avec caméra et fichier, pagination avec `hasMore`, erreur d’exécution SQL et refus d’authentification. Vérification des métadonnées/URL et de la fermeture du statement puis de la connexion avant l’inspection des fichiers. |
| `test/unit/ack_worker_connexions_test.php` | Sept cas : inactivité, succès, rejet, erreur d’authentification, réessai, maximum de tentatives et exception réseau. Deux lots successifs ; verrou conservé durant HTTP et écriture, libéré avant fermeture, aucune connexion durant la pause. Les primitives SQL de verrouillage sont doublées : ceci ne prouve pas la concurrence réelle de deux processus. |

Les long polls de cette suite exécutent le **corps du fichier local
`defenvvar.php`**, avec les fonctions MySQL doublées et sans chargement des
secrets ni modification de l’environnement. Le test spécifique vérifie aussi
`defenvvar.sample.php`, dans les branches développement et production : une
réinclusion avec connexion ouverte conserve le même objet et exécute `SELECT 1` ;
trois cycles fermeture + `$conn = null` + réinclusion créent chacun un nouvel
objet et appliquent `utf8`, sans `SELECT 1` sur un objet fermé. `.2` conserve la
première connexion entre les demandes initiales et la première lecture.
Il n’y a ni `require_once` pour ces réouvertures ni réouverture entre chaque SQL.

**Limites restantes :** pas de validation sur MySQL réel (les images PHP CLI
disponibles n’ont pas le pilote `mysqli`), ni sur le fichier privé de production,
les triggers éventuels, les versions d’appareils ou des workers réellement
concurrents. Les doublures ne valident pas la syntaxe/les plans SQL ; les requêtes
existantes restent inchangées, sauf la branche choisie pour le statut Remote.

Commandes de tests isolés :

```sh
php -n test/unit/statut_remote_transitions_test.php
php -n test/unit/occupation_connexions_test.php
php -n test/unit/monitoring_index_connexions_test.php
php -n test/unit/ack_worker_connexions_test.php
```

## Fichiers applicatifs à déployer — liste exacte

Huit fichiers, en conservant leurs chemins relatifs :

```text
scriptsphp/enregistreStatutRemote.php
scriptsphp/getStreamDeviceConfig.php
scriptsphp/getAppareilsSurSurface.php
scriptsphp/getMonitoringFilesIndex.php
syncscript/syncPushTempsCam.php
syncscript/syncPushTempsCam.1.php
syncscript/syncPushTempsCam.2.php
api/sync/ack_worker.php
```

Les quatre fichiers de tests et ce rapport sont des livrables de validation,
pas des fichiers à publier sur le serveur web. Ne pas remplacer
`defenvvar.php`, `defenvvar.sample.php`, `.env.php`, `Database.php` ou le schéma.

## Validation courte après un éventuel déploiement

À effectuer par l’exploitant autorisé, avec un appareil/compte de test identifié :

1. **Avant remplacement :** sauvegarder les huit fichiers effectivement en place,
   hors du répertoire public, en conservant chemins, permissions et empreintes.
   Vérifier le fichier privé sans le modifier : une inclusion après `$conn = null`
   doit ouvrir une connexion ordinaire et réappliquer son charset, sans
   déclaration de fonction/classe non protégée qui rendrait la réinclusion
   fatale. Le corps local et l’exemple passent ces critères ; la version privée
   reste à contrôler. Un écart est un motif de report, pas de modification
   improvisée du mécanisme central.
2. **Statut Remote :** sur le seul appareil de test, envoyer un état inchangé,
   puis une transition (`10→20`), puis `20→0` et `0→0`. Vérifier en lecture SQL
   que `codeEtat` suit les valeurs reçues, que `dernierMaJ` avance à chaque appel
   et que `dernierModif` reste stable sur l’état inchangé. Espacer les appels
   d’au moins une seconde pour distinguer les horodatages. Vérifier la réponse
   habituelle MOD1/MOD2 et la visibilité du nouvel état dans l’interface.
3. **Long polls :** pour Stream puis chaque variante Cam réellement utilisée,
   laisser une requête sans données passer au moins deux pauses, puis provoquer
   par l’interface habituelle un changement de configuration ou un événement
   de test. Vérifier la réponse JSON, les identifiants, le nouvel `updatedAt`,
   les paramètres caméra, les périodes et les abonnements attendus. Avec `.2`,
   créer une demande d’ajout vidéo sur la surface de test et vérifier son passage
   `progression=1→2`, son chrono et sa présence dans la réponse ; une demande
   d’une autre surface doit rester intacte.
4. **Lecture appareils et monitoring :** vérifier une caméra avec vidéo présente,
   une vidéo absente et un remote ; vérifier l’index authentifié avec fichiers,
   une page suivante et un jeton erroné (403). Les chemins et métadonnées doivent
   rester exploitables. Observer que les appels HTTP/fichiers ne maintiennent
   plus la connexion SQL.
5. **Worker :** arrêter proprement l’ancienne instance selon le gestionnaire
   existant, attendre la fin du lot en cours puis relancer une seule instance
   sur le nouveau fichier. Vérifier un ACK attendu, puis l’inactivité : pas de
   doublon, pas de croissance anormale des réessais et aucune connexion pendant
   `sleep(1)`. Un worker CLI déjà lancé ne recharge pas spontanément son code.
6. Pendant 10 à 15 minutes, comparer les mesures ci-dessous et les journaux PHP/
   HTTP. Rechercher spécialement `mysqli object is already closed`, erreurs
   de réinclusion, 5xx, `max_user_connections` et réponses JSON altérées.

## Retour arrière ciblé

1. En cas d’erreurs de connexion/réinclusion, réponses altérées ou régression
   fonctionnelle, arrêter proprement le worker de cette version et attendre la
   fin de son lot ; ne pas lancer une seconde instance en parallèle.
2. Restaurer uniquement les huit sauvegardes réalisées avant remplacement, aux
   mêmes chemins, avec les permissions d’origine. Le correctif Remote peut
   aussi être retiré seul en restaurant seulement `enregistreStatutRemote.php`
   si la régression lui est attribuée. Ne pas toucher aux secrets ni au schéma.
3. Appliquer la procédure habituelle d’invalidation du cache PHP/OPcache si
   nécessaire. Laisser les requêtes déjà actives se terminer, puis vérifier
   sur de nouvelles requêtes la version restaurée et relancer une seule
   instance du worker restauré.
4. Refaire les contrôles essentiels : statut, JSON des long polls, liste des
   appareils, index et ACK ; surveiller erreurs et connexions.

Aucune migration ni restauration de base n’est nécessaire. Le retour arrière
de code ne doit pas annuler les états ou demandes vidéo légitimement écrits
entre-temps. Restaurer l’ancien Remote réintroduit son bogue : conserver les
relevés de validation, sans tenter de reconstruire automatiquement les anciens
horodatages de transition.

## Mesure avant/après un éventuel déploiement

Procédure à exécuter par l’exploitant autorisé ; aucune commande ci-dessous
n’a été exécutée en production dans ce mandat.

1. Avec un compte d’observation autorisé, relever la version, les valeurs de
   `max_connections` et `max_user_connections`, ainsi que la limite spécifique
   du compte applicatif (elle peut différer de la valeur globale). Relever les
   versions PHP/client et vérifier l’absence de préfixe persistant dans le
   fichier privé. Ne pas copier ses secrets dans le rapport.
2. Garder **une seule session d’observation** ouverte pendant 10 à 15 minutes,
   avec échantillonnage toutes les 1 à 2 s. Répéter sur une période comparable
   après la modification : mêmes appareils, trafic et worker. Éviter une
   nouvelle connexion d’observation par échantillon.
3. Relever les compteurs et les sessions du compte applicatif :

```sql
SHOW GLOBAL STATUS WHERE Variable_name IN
 ('Threads_connected', 'Threads_running', 'Connections',
  'Max_used_connections', 'Aborted_connects');

SELECT USER, COMMAND, COUNT(*) AS connexions,
       MAX(TIME) AS age_etat_max_secondes
FROM information_schema.PROCESSLIST
WHERE USER = '<compte_applicatif>'
GROUP BY USER, COMMAND;
```

La visibilité de `PROCESSLIST` dépend des droits : sans visibilité globale,
ne pas interpréter un résultat partiel comme une absence de connexions.
Exclure la session d’observation si elle utilise le même compte.

4. Comparer moyenne, maximum, p95 des connexions du compte et nombre de
   sessions `Sleep`. La somme `connexions × intervalle` estime les
   connexion-secondes occupées. `TIME` mesure la durée de l’état courant,
   pas l’âge de la connexion ni la durée HTTP. `Threads_running` seul ne
   représente pas les connexions dormantes qui consomment aussi la limite.
5. Comparer le delta `Connections` par seconde : il peut augmenter avec le
   polling reconnecté, alors que les connexions simultanées diminuent.
   `Max_used_connections` est un maximum depuis le démarrage/réinitialisation,
   pas le maximum propre à chaque fenêtre. Ne pas réinitialiser les compteurs.
6. Corréler les fenêtres avec les journaux HTTP (volume par endpoint, latences,
   429/5xx) et PHP (`max_user_connections`), ainsi que les acquittements en
   attente. Sans les journaux HTTP, MySQL ne permet pas d’attribuer une session
   à un endpoint avec certitude.
7. En validation, maintenir plusieurs long polls sans données : les pauses de
   5 s ne devraient plus produire autant de sessions `Sleep`. Simuler un
   serveur vidéo lent, vérifier que les connexions sont libérées avant les
   HEAD. Vérifier le worker inactif : aucune connexion pendant sa pause.
   Éviter tout test de charge agressif en production avant le tournoi.

Un résultat favorable est une baisse des connexions simultanées et des
connexion-secondes, sans hausse des erreurs, perte de changements d’état,
doublons d’acquittement ou latence fonctionnelle excessive. Conserver le diff
et prévoir le retour aux seuls fichiers modifiés si ces critères régressent.
