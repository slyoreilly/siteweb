# Relais agent-ci-cd - monitoringUpload.php

Agent destinataire : `agent-ci-cd`

## Preuve observee

L investigation SyncCam a confirme que le client atteint correctement l endpoint legacy :

- `upload_debut`
- `prepare_ok`
- `request_start`
- HTTP `200`
- `success=true` cote SyncCam

Mais le corps de reponse de `/syncscript/monitoringUpload.php` contenait des warnings PHP :

- `mkdir(): Permission denied`
- `file_put_contents(): Failed to open stream`

Effet operationnel : les uploads semblent reussir cote SyncCam, mais les fichiers ne sont pas ecrits dans `/monitoring/<telId>/<YYYY_M_D>/`.

## Fichiers concernes

- `syncscript/monitoringUpload.php`
- `monitoring/`
- Fichiers attendus par le monitoring :
  - `synccamlog.txt`
  - `BDLogFile.txt`
  - `syncamlog.txt`
  - `logDBSS.txt`
- Contrat de lecture existant : `base_documentaire/contrat_index_fichiers_monitoring.md`
- Test applicatif ajoute : `test/unit/monitoring_upload_test.php`

## Correction applicative deja faite

`monitoringUpload.php` retourne maintenant une reponse JSON structuree :

- succes reel : HTTP `200`, `ok=true`, `success=true`, `bytesWritten`, `path`;
- echec requete : HTTP `400`, `ok=false`, `success=false`, `error.code`;
- echec stockage : HTTP `500`, `ok=false`, `success=false`, `error.code`, `error.details.path`, `error.details.phpError`.

Les echecs `mkdir()` et `file_put_contents()` ne peuvent plus etre rapportes comme des succes.

## Correction IaC/deploiement requise

Verifier et corriger l etat de `/monitoring` dans l environnement servi par PHP :

- le repertoire `/monitoring` doit exister dans le document root du site;
- le parent doit permettre a l utilisateur PHP/web d y creer `/<telId>/<YYYY_M_D>/`;
- les sous-repertoires crees doivent rester lisibles par le serveur web pour l index monitoring;
- si le site tourne en conteneur, verifier le montage Docker qui porte `/monitoring`;
- si le deploiement provisionne ce chemin par Ansible, ajouter ou corriger la tache qui fixe owner/group/mode;
- ne pas masquer les erreurs PHP dans une reponse HTTP `200`.

Hypothese la plus probable a valider : owner/group ou permissions du repertoire `monitoring/` incompatibles avec l utilisateur PHP effectif.

## Validation attendue

Depuis l environnement deploiement/staging, executer deux uploads reels vers :

```text
POST /syncscript/monitoringUpload.php
multipart:
  params={"telId":"<camera-test>"}
  fichier=synccamlog.txt
```

puis :

```text
POST /syncscript/monitoringUpload.php
multipart:
  params={"telId":"<camera-test>"}
  fichier=BDLogFile.txt
```

Verifier :

- reponse HTTP `200`;
- JSON `ok=true`, `success=true`;
- `bytesWritten > 0`;
- presence effective de `/monitoring/<camera-test>/<YYYY_M_D>/synccamlog.txt`;
- presence effective de `/monitoring/<camera-test>/<YYYY_M_D>/BDLogFile.txt`;
- fichiers visibles par `GET /scriptsphp/getMonitoringFilesIndex.php`.

Simuler aussi un echec d ecriture dans l environnement de validation :

- retirer temporairement l ecriture au repertoire cible, ou monter un volume read-only;
- refaire un upload;
- verifier HTTP `500`, `ok=false`, `success=false`, `error.code` dans `monitoring_directory_create_failed`, `monitoring_directory_not_writable` ou `monitoring_file_write_failed`;
- confirmer qu aucun `success=true` n est emis quand le fichier n est pas ecrit.
