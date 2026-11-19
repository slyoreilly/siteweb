# Configuration du jeton des routes Legacy

Les deux routes chargent `scriptsphp/chargeSecrets.php` avant `getenv()` et
avant toute connexion SQL. `defenvvar.php` et son modèle utilisent le même
chargeur. Le jeton n'a aucune valeur par défaut et ne reprend aucun autre secret.

## Configuration DEV et PROD

Sur chaque serveur, configurez `LEGACY_MATCH_ACTIF_TOKEN` dans l'environnement
du processus PHP (PHP-FPM, Apache ou conteneur), ou ajoutez à `.env.php` à la
racine du site, fichier privé ignoré par Git :

```php
<?php
putenv('LEGACY_MATCH_ACTIF_TOKEN=EXEMPLE_FICTIF_A_REMPLACER');
```

Remplacez cette valeur fictive par un secret dédié fourni par votre gestionnaire
de secrets. Le chargeur accepte aussi un tableau retourné par `.env.php` avec
la clé `LEGACY_MATCH_ACTIF_TOKEN`. Une variable du processus déjà définie est
prioritaire, même vide. Ne combinez pas ces deux méthodes de configuration.
Utilisez des secrets distincts en DEV et PROD et configurez le même secret dans
le client serveur correspondant. Ne mettez jamais le secret dans le navigateur.

Le fichier privé ne doit produire aucune sortie. Restreignez ses permissions au
compte de service et interdisez son accès HTTP dans la configuration du serveur.
Une valeur absente ou blanche renvoie 503 ; un Bearer absent ou incorrect renvoie
401. Une authentification correcte laisse ensuite s'appliquer la validation et
les requêtes SQL existantes.

## Déploiement manuel

1. Livrez les deux routes et `scriptsphp/chargeSecrets.php` ensemble.
2. Ajoutez `require_once __DIR__ . '/chargeSecrets.php';` au début du
   `scriptsphp/defenvvar.php` privé de chaque serveur (déjà ajouté localement).
   Ce fichier est ignoré par Git et exclu du transfert : le modèle ne le remplace
   pas automatiquement. Conservez ses autres réglages.
3. Configurez le secret privé selon les instructions ci-dessus. Le transfert
   exclut déjà `.env.*`. Rechargez le service PHP si son environnement a changé.
4. Vérifiez les deux URL avec un Bearer incorrect (401), puis avec le bon Bearer
   et un `arenaId` de test valide. Vérifiez aussi 503 en DEV sans configuration.
   Ne journalisez pas les en-têtes contenant le secret.

## Origine de l'omission et validation

L'ajout initial des routes a introduit `getenv()` sans modifier le modèle de
configuration. `defenvvar.php` n'était chargé qu'après l'authentification, dans
le rappel SQL. Les anciens tests couvraient les contrats métier et SQL, pas
le chargement des secrets ni les refus d'authentification. Aucun chargeur
`.env.php` n'était présent dans cette copie du dépôt ; la configuration déployée
non versionnée doit donc être vérifiée lors du déploiement.

`python test/unit/legacy_token_test.py [chemin-vers-php]` exécute les vraies routes
dans une copie temporaire avec des jetons fictifs, en DEV et PROD, pour les deux
formats privés, la priorité de l'environnement et les refus 401/503. Un Bearer
valide atteint la validation 400 sans connexion SQL. Sans PHP CLI, le test est
explicitement signalé comme ignoré.
