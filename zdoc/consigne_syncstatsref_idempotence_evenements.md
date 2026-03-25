# Consigne Agent `syncstatsref` - Idempotence Evenements

Objectif:
- Eviter les doublons lors des retries reseau vers `POST /api/upsertEvenements.php`.

Contexte serveur deja en place:
- Le serveur applique maintenant une idempotence forte sur le triplet:
  - `telID`
  - `id` (id local evenement)
  - `deviceInstanceId` (ou `installationId`)
- Si ce triplet est deja recu, le serveur ne recree pas l'evenement et renvoie l'existant.

Travail demande cote `syncstatsref`:
1. Adapter tous les appels vers `POST /api/upsertEvenements.php` pour inclure, pour chaque evenement:
   - `telID`: identifiant stable de l'appareil
   - `id`: identifiant local de l'evenement (deja present dans la plupart des cas)
   - `deviceInstanceId`: identifiant d'instance locale (doit changer apres reset/reinstallation)
2. Garantir que `id` reste stable entre retries d'un meme evenement local.
3. Ne jamais regeneraliser `deviceInstanceId` a chaque lancement normal:
   - le regenerer seulement lors d'une nouvelle base locale/reinstallation.
4. Verifier que les retries en doublon reçoivent un `EventComId` identique avec action `reused`.

Important:
- Il n'existe pas de endpoint `upsertEvenementInstant` dans ce repo.
- Le endpoint present est `POST /api/getEvenementInstant.php` (lecture instantanee, pas upsert).

Verifications minimales attendues:
1. Meme evenement envoye 2 fois avant reponse: 1 seule ligne creee dans `TableEvenement0`.
2. Reponse serveur:
   - premier envoi: `action=created`
   - doublon: `action=reused`
3. Apres reset BD locale (nouveau `deviceInstanceId`), un `id` local recycle ne bloque pas le nouvel evenement.

