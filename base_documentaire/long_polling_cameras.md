# Long polling caméra : connexions et transmission des demandes

Complément local du 25 novembre 2026, limité aux trois `syncPushTempsCam` et à
leurs consommateurs. Aucun commit, push, déploiement, accès production ou
changement global de `defenvvar.php`. Aucun endpoint supprimé ou renommé.

## Comportement implémenté

Un helper privé à ces trois endpoints, `syncscript/attenteLongPollCam.php`,
centralise leur attente et leurs reconnexions. Il n’est pas un endpoint ni une
nouvelle couche SQL générale.

- Le fichier historique de configuration est inclus **une fois**, pour la
  connexion initiale. Les variables d’hôte, utilisateur, mot de passe, base,
  port et le charset réellement appliqué sont conservés en mémoire pour cette
  seule exécution PHP. Aucun secret supplémentaire n’est écrit ou journalisé.
- Après chaque bloc SQL complet, fermeture et mise à `null` de la connexion.
  La requête HTTP reste active durant la pause, sans connexion MySQL.
- Une réouverture utilise `mysqli_init`, `mysqli_options` puis
  `mysqli_real_connect`, sans réinclure le fichier susceptible d’appeler `exit`.
  Le timeout de connexion est de 2 s ; le timeout de lecture est réglé sur le
  temps entier restant moins 2 s, avec un minimum de 1 s. Les options sont
  appliquées avant connexion conformément au
  [manuel PHP](https://www.php.net/manual/en/mysqli.options.php).
- Une connexion échouée, y compris par exception, est libérée. Au maximum trois
  échecs de réouverture par requête HTTP, avec pauses de 2 s puis 5 s, uniquement
  s’il reste la pause et au moins 3 s pour tenter la reprise. Aucune boucle de
  tentatives immédiates. Le compteur d’échecs ne se remet pas à zéro après un
  succès dans cette même requête.
- Une réussite permet de continuer le même long poll et conserve le seuil
  `recentSync` calculé au départ. Les requêtes SQL rapprochées partagent la
  connexion de l’itération.
- Après épuisement, HTTP **503**, `Retry-After: 5`, et JSON avec les mêmes clés
  et types que la réponse normale. La dernière réponse entièrement construite
  est conservée, notamment ses ligues ; à défaut, les tableaux sont vides.
  Aucun curseur métier ou état de découpage n’est avancé par cette réponse.
  Une reprise réussie produit de nouveau HTTP 200.
- Une exception pendant l’inclusion initiale produit également ce repli. Si le
  fichier historique appelle directement `exit` sans corps, un callback de fin
  de requête fournit le JSON 503, sans double réponse lors du parcours normal.
  Ce premier appel reste gouverné par la configuration historique ; il n’est
  pas réessayé en boucle par le helper.

### Temps HTTP et latence

Le budget est mesuré avec une horloge monotone, depuis le début du traitement
du long poll. La règle habituelle reste `timeout - 30000`, arrondie à la pause
historique de 5 s, avec minimum 5 s et plafond 30 s. Ainsi :

| Paramètre reçu | Budget d’attente/reprise |
| --- | --- |
| 0 ou 30000 | 5 s |
| 40000 | 10 s |
| 60000 | 30 s |
| Supérieur à 60000 | 30 s, plafond explicite |

Les pauses et les réouvertures consomment désormais ce même budget ; le
compteur historique ne comptait que les `sleep`. Il faut au moins 3 s restantes
pour lancer une nouvelle réouverture. Une pause finale peut être raccourcie.
La cadence normale reste de 5 s plus les traitements ; aucun sommeil n’est
ajouté après détection d’un événement pertinent ou d’une demande prête.

Ce budget empêche les reprises d’étirer volontairement un long poll au-delà
de sa fenêtre. **Il ne constitue pas un délai HTTP absolu garanti** : l’ouverture
initiale reste historique, les SQL existants ne sont pas réécrits et un ensemble
de requêtes lentes peut dépasser la fenêtre. Le timeout de lecture d’une
connexion s’applique par opération, pas à la somme des SQL. Il faut vérifier
la marge réelle sous charge par rapport aux clients de 50/60 s et au frontal.

## Demandes vidéo de `.2`

Une demande déjà sélectionnée et mise à `progression=2` est désormais restituée
depuis les données préparées en mémoire, sans relecture de `DemandeAjoutVideo`.
Les références, types événement/clip, chronos et le format des éléments JSON
restent identiques.

La première itération termine toujours les SQL nécessaires : événements
ordinaires, informations du match, aréna, abonnements et ligues lorsque le
paramètre `timeout` les demande. La réponse est construite avec tous ces
éléments, puis la connexion est fermée et le script répond. Une demande prête
est un motif de sortie au même titre qu’un événement ordinaire : **aucune pause
ni réouverture suivante**, même si cette dernière aurait été refusée.

Une demande seule ne prive donc pas la caméra des informations disponibles
au moment de la lecture. Les événements créés après cette réponse seront
recherchés par une requête suivante, comme après toute sortie anticipée normale.
Les ligues sont reconstruites à chaque lecture, ce qui évite leur accumulation
au fil des itérations.

Un consommateur ne fournissant pas de `nomMatch` numérique positif ne peut pas
recevoir le bloc de demandes selon ce contrat historique. Dans ce cas,
**les demandes restent à progression=1**, au lieu d’être marquées sans restitution.
Le vieux chemin asynchrone Java est concerné ; il fournit `arenaId`/`nomFic`,
mais pas `nomMatch`. Aucun nouveau champ ou changement de client n’est imposé.

Le protocole n’est pas devenu un acquittement fiable : une coupure HTTP après
l’UPDATE, un crash ou une erreur SQL pendant la préparation restante peuvent
encore empêcher la livraison. La réservation concurrente des demandes et la
déduplication historique ne sont pas refondues. Le correctif retire le risque
spécifique d’une pause/reconnexion **après** préparation, sans promettre une
livraison exactement une fois.

### Détection de demandes apparues pendant la boucle — non implémentée

Intérêt : rechercher les nouvelles demandes à chaque interrogation permettrait
une détection en environ 0 à 5 s plus traitement, au lieu d’attendre la prochaine
requête HTTP. Le gain serait surtout visible sur les longs polls de 30 s.

Conséquences à examiner avant décision : une sélection SQL supplémentaire par
itération, application correcte du match/de la surface/caméra, chronos des
demandes ajoutées tardivement, coexistence avec les événements ordinaires et
les appels concurrents, mêmes limites de réservation et de livraison. Il ne
faudrait ni retraiter les demandes préparées, ni marquer une demande que le
consommateur ne peut restituer. Ce changement mérite ses propres scénarios
fonctionnels et n’a pas été mélangé au correctif de reconnexion. Le test dédié
confirme que la sélection initiale reste exécutée une seule fois.

## Consommateurs et utilité des versions

Observation des sources et de l’historique Git local de `synccam2`, pas un
inventaire des APK installés.

| Endpoint | Consommation constatée | Générations possibles / décision |
| --- | --- | --- |
| `syncPushTempsCam.php` | Aucun appel actif trouvé. Une ancienne ligne de journalisation le mentionne dans du code commenté de `NetworkUtilities.java`. Référence présente dans l’historique dès novembre 2018. | Clients très anciens ou extérieurs aux sources consultées possibles. Candidat à l’archivage après preuve d’absence de trafic. Maintenu à son URL actuelle. |
| `syncPushTempsCam.1.php` | Ancienne méthode `parPushGetTemps` entièrement commentée, avec `timeout=60000`. Référence présente dans l’historique de mars 2019. | Anciennes générations de long polling ; aucune correspondance fiable avec un numéro d’APK établie. Candidat à l’archivage après observation. Maintenu. |
| `syncPushTempsCam.2.php` | `NetworkUtilities.getTemps` actif, `timeout=30000`, `nomMatch`, délai de lecture 60 s. Appelé par le mode full de l’adaptateur Kotlin actuel. Ancien `SyncAdapter.java.pushGetTemps` : `timeout=60000`, lecture 50 s, pas de `nomMatch`. Référence de cet ancien adaptateur en juillet 2021. | Anciennes générations Java et synchronisation complète actuelle. À conserver. Le mode match Kotlin utilise `getEvenements` depuis le changement du 27 février 2026, mais cela ne rend pas `.2` inutile. |

Points d’ancrage :

- `synccam2/app/src/main/java/com/syncstats/asynccam/SyncService.java` instancie
  l’adaptateur du package `adapters` (Kotlin), pas l’ancienne classe Java racine.
- `NetworkUtilities.java:getTemps` retourne le corps et ne rejette pas les HTTP
  503 avant lecture JSON ; le chemin asynchrone Java lit également le corps.
- Les tableaux `match`, `chronoBut`, `video`, `matchPeriode`, `matchs`, et
  `ligues` pour `.2`, sont conservés. `heure` reflète le moment de la réponse.
- Les consommateurs de ligues consultés procèdent par insertions/mises à jour,
  pas par effacement de toutes les ligues quand le tableau est vide. Les
  curseurs de découpage sont dérivés des données locales, pas de `heure`.
- Aucun changement Android n’est nécessaire pour lire ces réponses. Les vieux
  clients n’honorent pas nécessairement `Retry-After` ; aucune garantie de reprise
  immédiate ou de cadence entre deux requêtes n’est ajoutée côté client.

**Limite fonctionnelle préexistante à vérifier sur les APK du tournoi :** le
mode full Kotlin actuel lit les ligues/référentiels de `.2`, mais ne remet pas
son `matchPeriode` au découpage. Il poursuit avec `getEvenements` et les événements
de son dépôt local ; son `traiteVids` utilise ces événements locaux. À l’inverse,
les chemins Java historiques transmettent le `matchPeriode` reçu au traitement
des vidéos. La compatibilité de lecture du JSON ne prouve donc pas la prise en
charge des demandes vidéo Legacy par une version Kotlin récente. Ce correctif
ne porte pas les demandes Legacy vers le modèle d’événements récent : cette
évolution fonctionnelle serait distincte et dépasse l’optimisation ciblée.
En particulier, un passage à progression=2 pendant une synchronisation full
récente ne doit pas être assimilé à une demande reçue par le découpeur.

### Vérifier l’usage dans Apache avant un archivage

Sur chaque vhost/hôte recevant le trafic caméra, consulter les journaux actuels
**et les rotations compressées**, sans modifier la configuration dans ce mandat.
Exemple à adapter pour un format Common/Combined Apache standard :

```sh
zgrep -hE '"(GET|POST) /syncscript/syncPushTempsCam(\.[12])?\.php([? ]|$)' \
  /chemin/du/vhost/access.log* \
  | awk '{print $7}' | cut -d '?' -f 1 | sort | uniq -c
```

Examiner également dates/heures, statuts (`$9` dans ce format), IP et User-Agent
si présent. L’IP peut être partagée ; le User-Agent peut être générique. Le POST
n’expose normalement pas la version d’application ni le `telId` dans l’access
log : ne pas inventer une génération d’APK à partir de ces seuls champs.
Les durées ne sont exploitables que si le format existant contient `%D`/`%T`.
Une réécriture d’URL ou un proxy peut nécessiter la consultation de son journal.

Proposition d’archivage : classer `.php` et `.1.php` comme historiques dans la
documentation maintenant ; ne déplacer/renommer/supprimer aucun fichier tant
qu’une fenêtre couvrant le tournoi et le retour des appareils peu fréquents,
sur tous les vhosts, ainsi que l’inventaire des versions, n’établit pas leur
absence d’usage. Zéro requête sur une courte fenêtre ne suffit pas.

## Validation locale et limites

`test/unit/long_poll_cam_test.php` réutilise les jeux SQL représentatifs du test
`occupation_connexions_test.php`. Il exécute les scripts et le helper réels, avec
horloge virtuelle, appels MySQL doublés, aucun réseau ni base réelle. Le corps
du `defenvvar.php` local est exécuté en mémoire sans son chargement privé ;
hôte, utilisateur, mot de passe, base, port et charset réutilisés sont contrôlés.

Scénarios :

- Événement après trois pauses : détection à t=15 s et réponse sans nouvelle
  pause, pour les trois versions.
- Premier refus à t=5 s, réussite à t=7 s : poursuite du même HTTP, détection de
  l’événement à t=17 s. Parcours `false` et exception testés.
- Refus prolongés : tentatives à t=5, 7 et 12 s, puis JSON 503 ; deux tentatives
  seulement avec un budget de 10 s. Aucune connexion pendant les pauses.
- Demandes seules : une connexion, zéro pause, deux demandes restituées avec
  match, aréna, abonnements et ligues. Même résultat si toute réouverture aurait
  été refusée. Événement et demandes simultanés : tous sont conservés.
- Absence de match : aucune demande marquée. Demande apparue pendant la boucle :
  aucune sélection supplémentaire, conformément au périmètre retenu.
- Expiration sans données : 5 s avec `timeout=30000`, 30 s avec `timeout=60000`
  ou une valeur excessive ; conservation des ligues déjà lues lors d’un échec.
- Exception initiale et `exit` initial historiques : JSON 503 exploitable.

Commandes :

```sh
php -n test/unit/occupation_connexions_test.php
php -n test/unit/long_poll_cam_test.php
```

Les assertions contrôlent les références/chronos, les périodes, paramètres
caméra, abonnements, ligues et la forme JSON attendue par les consommateurs.
Elles vérifient aussi les identifiants numériques attendus par `JSONObject.getInt`
et le format complet des dates de match. Les **34 scénarios ciblés**, la suite
existante et les vérifications de syntaxe passent sous **PHP 7.4 et PHP 8.3**,
avec réseau désactivé et dépôt monté en lecture seule. `git diff --check` passe.
Elles ne remplacent pas un test sur APK, un test SQL réel, une mesure réseau
ou une validation du fichier privé de production. Les tests ne prouvent pas
la concurrence ni une livraison exactement une fois.

## Fichiers de ce complément et promotion éventuelle

Quatre fichiers applicatifs indissociables :

```text
syncscript/attenteLongPollCam.php
syncscript/syncPushTempsCam.php
syncscript/syncPushTempsCam.1.php
syncscript/syncPushTempsCam.2.php
```

Ne pas publier les tests ni les rapports. Installer le helper avant les trois
endpoints, ou promouvoir l’ensemble atomiquement. Conserver le fichier privé.
Avant promotion, vérifier que ce dernier expose les paramètres historiques
utilisés, que son charset est récupérable et qu’il n’ajoute pas des options TLS,
socket ou paramètres de session à reproduire lors d’une réouverture. Si tel est
le cas, adapter ce seul helper avant promotion ; ne pas déployer sur hypothèse.
Vérifier également les délais PHP/frontal et la présence de PHP 7.4+ et mysqli.

Retour arrière ciblé : restaurer les trois scripts sauvegardés avant ce
complément, puis retirer le helper seulement lorsqu’aucun script ne le référence
plus. Laisser finir les requêtes actives et appliquer la procédure habituelle
d’OPcache si nécessaire. Ne pas restaurer la base ni réinitialiser les demandes.

## Contrôle court pendant les prochains matchs

1. Identifier une caméra de test, son APK, sa surface et le match ; synchroniser
   les horloges de mesure. Vérifier dans Apache lequel des trois endpoints est
   réellement sollicité. Établir une petite référence de latence avant/après.
2. Durant un long poll sans données, créer quelques événements espacés par
   l’interface normale. Relever l’heure de création, la réponse HTTP et le début
   du traitement/découpage dans les journaux SyncCam existants. Sans incident,
   la détection serveur doit intervenir à la lecture suivante, environ 0–5 s
   plus SQL ; la disponibilité du fichier final dépend aussi du pipeline vidéo.
3. Créer une demande vidéo sur un match connu, puis une demande en même temps
   qu’un événement ordinaire. Pour une demande présente au début du HTTP,
   vérifier le signal PHP `[long_poll_cam] ... motif=demandes_pretes ids=...`,
   l’absence de palier de 5/30 s avant la réponse, la référence reçue et le
   découpage correspondant dans SyncCam. Vérifier aussi match/ligues/abonnements.
4. Rapprocher **chaque identifiant de demande** passé de progression 1 à 2 avec
   sa réponse et sa référence de découpage côté caméra ; `progression=2` ou un
   HTTP 200 seul ne prouve pas la livraison. Signaler toute demande sans trace
   de réception/découpage, et rechercher une coupure, erreur SQL ou incompatibilité
   de match. Ne pas la remettre automatiquement à 1 ni créer de doublon.
5. Pendant 10–15 minutes, surveiller les motifs `reconnexion_echouee`,
   `reconnexion_retablie`, les HTTP 503, les temps Apache si disponibles et les
   sessions SQL dormantes. Les pauses ne doivent plus occuper MySQL. Ne pas
   provoquer volontairement une saturation de production : réserver l’injection
   de pannes à la validation isolée.
