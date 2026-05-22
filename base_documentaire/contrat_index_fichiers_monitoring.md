# Contrat - Index des fichiers monitoring SyncCam

Ce document fige le contrat V1 de l'endpoint legacy :

```text
GET /scriptsphp/getMonitoringFilesIndex.php
```

Ce contrat est consomme par le poller du monitoring moderne. La structure ne doit pas etre changee sans coordination explicite avec l'agent monitoring.

## Authentification

Le secret serveur-a-serveur recommande est :

```text
MONITORING_FILES_INDEX_TOKEN
```

Le poller doit envoyer ce secret avec l'un des headers supportes :

```text
Authorization: Bearer <token>
X-Monitoring-Index-Token: <token>
X-Sync-Token: <token>
```

`MONITORING_FILES_INDEX_TOKEN` est le nom officiel a utiliser cote monitoring.

## Structure Officielle

La liste officielle des fichiers est :

```text
items[].dates[].files[]
```

Les fichiers ne sont pas retournes directement sous `items[]`. Le poller doit toujours parcourir :

```text
items -> dates -> files
```

## Champs Garantis

Champs garantis au niveau `items[]` :

- `telId`
- `camId`
- `dernierMaJ`

Champs garantis au niveau `items[].dates[]` :

- `date`
- `isoDate`

Champs garantis au niveau `items[].dates[].files[]` :

- `name`
- `size`
- `modifiedAt`
- `url`

Correspondance demandee par le monitoring :

```text
file.name       -> items[].dates[].files[].name
file.size       -> items[].dates[].files[].size
file.modifiedAt -> items[].dates[].files[].modifiedAt
file.url        -> items[].dates[].files[].url
```

## Exemple JSON

```json
{
  "ok": true,
  "generatedAt": "2026-05-21T21:00:00Z",
  "days": 2,
  "limit": 100,
  "offset": 0,
  "hasMore": false,
  "activeHours": 48,
  "allowedFiles": [
    "BDLogFile.txt",
    "synccamlog.txt",
    "syncamlog.txt",
    "logDBSS.txt"
  ],
  "items": [
    {
      "telId": "CAM123",
      "camId": "CAM123",
      "userId": "client",
      "dernierMaJ": "2026-05-21 16:55:00",
      "dates": [
        {
          "date": "2026_5_21",
          "isoDate": "2026-05-21",
          "files": [
            {
              "name": "BDLogFile.txt",
              "size": 184233,
              "modifiedAt": "2026-05-21T20:56:03Z",
              "url": "https://syncstats.com/monitoring/CAM123/2026_5_21/BDLogFile.txt"
            },
            {
              "name": "syncamlog.txt",
              "size": 42391,
              "modifiedAt": "2026-05-21T20:57:11Z",
              "url": "https://syncstats.com/monitoring/CAM123/2026_5_21/syncamlog.txt"
            }
          ]
        },
        {
          "date": "2026_5_20",
          "isoDate": "2026-05-20",
          "files": []
        }
      ]
    }
  ]
}
```

## Inputs V1

```text
days=1|2
limit=1..100
offset=0..1000000
telId=<optionnel>
```

`telId` est valide seulement s'il respecte :

```text
[A-Za-z0-9_.-]{1,80}
```

et ne contient pas `..`.

## Stabilite Du Contrat

Pour la V1, les points suivants sont stables :

- le chemin `items[].dates[].files[]`;
- les champs garantis listes plus haut;
- la pagination simple par `limit`, `offset`, `hasMore`;
- les noms des fichiers autorises;
- la limitation aux cameras recentes depuis `StatutCam`;
- la limitation aux dossiers aujourd'hui et hier.

Tout changement de structure, de nom de champ, de type de champ ou de semantique de pagination doit etre coordonne avec l'agent monitoring avant de modifier le code.
