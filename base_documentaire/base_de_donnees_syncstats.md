# Base de donnees SyncStats

## Objet du document

Ce document decrit la base de donnees telle qu'elle ressort du dump SQL fourni et du code PHP du depot. Le schema est majoritairement legacy, avec beaucoup de tables `MyISAM` sans cles etrangeres declaratives. Les relations ci-dessous sont donc documentees selon deux niveaux :

- relations certaines : observees dans le code applicatif
- relations probables : deduites des colonnes et conventions de nommage

## Vue d'ensemble fonctionnelle

```text
Ligue
  -> TableSaison
  -> abonEquipeLigue -> TableEquipe
  -> abonJoueurLigue -> TableJoueur
  -> TableMatch -> TableEvenement0
                 -> Clips
                 -> RapportMatch
                 -> Snapshots
                 -> MatchAVenir
  -> AbonnementLigue -> TableUser
  -> abonLigueArena -> TableArena
  -> EventType / CamActionTemplate

TableEquipe
  -> abonJoueurEquipe -> TableJoueur
  -> TableMatch.eq_dom / TableMatch.eq_vis

Telephones.telId
  -> StatutCam / StatutRemote / Controle / TempsSync / SensorLog / Alarms
  -> abonAppareilMatch / abonAppareilSurface / Monitoring
```

## Tables coeur metier

| Domaine | Tables principales | Role deduit |
|---|---|---|
| Ligue et saison | `Ligue`, `TableSaison`, `Calendriers` | Definition des ligues, fenetres de saison et structures de calendrier |
| Equipes et joueurs | `TableEquipe`, `TableJoueur`, `Positions`, `Presences` | Referentiel sportif, effectifs, positions et presences par match |
| Matchs et evenements | `TableMatch`, `MatchAVenir`, `TableEvenement0`, `EventType`, `EventTypeDetail`, `PlusMoins`, `RapportMatch` | Planification, score, journal d'evenements et calcul de statistiques |
| Abonnements et droits | `AbonnementLigue`, `abonEquipeLigue`, `abonJoueurEquipe`, `abonJoueurLigue`, `abonArbitreLigue`, `abonLigueArena` | Rattachement utilisateur, equipe, joueur, arbitre et arena a une ligue sur une plage de validite |
| Video et captation | `Video`, `Clips`, `DemandeAjoutVideo`, `CamActionTemplate`, `Stream` | Videos completes, clips, demandes d'extraction et regles automatiques de camera |
| Appareils et monitoring | `Telephones`, `StatutCam`, `StatutRemote`, `TempsSync`, `Controle`, `SensorType`, `SensorLog`, `Alarms`, `AlarmClass`, `Monitoring` | Inventaire d'appareils, etat terrain, telemetrie et alertes |
| Messagerie et contenu | `TableMessage`, `ExpeditionMessage`, `ReceptionMessage`, `RegleMessage`, `Galerie`, `TableFichier`, `boiteContexte` | Messages, pieces jointes, galerie et contenu contextuel |

## Relations certaines

### 1. Ligue comme racine metier

Relations observees dans le code :

- `Ligue.ID_Ligue` -> `TableSaison.ligueRef`
- `Ligue.ID_Ligue` -> `TableMatch.ligueRef`
- `Ligue.ID_Ligue` -> `AbonnementLigue.ligueid`
- `Ligue.ID_Ligue` -> `abonEquipeLigue.ligueId`
- `Ligue.ID_Ligue` -> `abonJoueurLigue.ligueId`
- `Ligue.ID_Ligue` -> `abonArbitreLigue.ligueId`
- `Ligue.ID_Ligue` -> `abonLigueArena.ligueId`
- `Ligue.ID_Ligue` -> `EventType.LeagueId`
- `Ligue.ID_Ligue` -> `CamActionTemplate.LeagueId`

Ces liens apparaissent notamment dans `syncscript/traiteSync.php`, `syncscript/construitLigue.php`, `stats2/equipe2JSON.php`, `stats2/getArena.php` et plusieurs API recentes.

### 2. Match comme pivot d'execution

- `TableMatch.match_id` est la cle interne du match
- `TableMatch.matchIdRef` est la cle de synchronisation externe utilisee par le mobile
- `TableMatch.eq_dom` -> `TableEquipe.equipe_id`
- `TableMatch.eq_vis` -> `TableEquipe.equipe_id`
- `TableEvenement0.match_event_id` -> `TableMatch.matchIdRef`
- `Clips.matchId` -> `TableMatch.matchIdRef`

Le code de `api/upsertMatch.php`, `api/upsertEvenements.php`, `syncscript/syncTempsCam.php` et plusieurs scripts `stats2` confirme ce double identifiant du match.

### 3. Equipes et joueurs

- `abonEquipeLigue.equipeId` -> `TableEquipe.equipe_id`
- `abonJoueurEquipe.joueurId` -> `TableJoueur.joueur_id`
- `abonJoueurEquipe.equipeId` -> `TableEquipe.equipe_id`
- `abonJoueurLigue.joueurId` -> `TableJoueur.joueur_id`
- `TableEvenement0.joueur_event_ref` -> `TableJoueur.joueur_id`

Le chargement de ligue dans `syncscript/construitLigue.php` passe systematiquement par ces tables de rattachement. Elles portent donc la verite fonctionnelle, davantage que les colonnes denormalisees dans `TableJoueur`.

### 4. Utilisateurs, arbitrage et arenas

- `AbonnementLigue.userid` -> `TableUser.noCompte`
- `TableArbitre.userId` -> `TableUser.noCompte`
- `abonArbitreLigue.arbitreId` -> `TableArbitre.arbitreId`
- `abonLigueArena.arenaId` -> `TableArena.arenaId`
- `abonLigueArena.gabaritId` -> `Gabarits.gabaritId`

`syncscript/traiteSync.php` determine les ligues accessibles a un utilisateur en combinant `AbonnementLigue` et les abonnements arbitres.

### 5. Parc d'appareils et monitoring

- `telId` est la cle transversale du sous-systeme terrain
- `Telephones.telId` est relie fonctionnellement a `StatutCam`, `StatutRemote`, `TempsSync`, `Controle`, `SensorLog`, `Alarms`, `abonAppareilMatch`, `abonAppareilSurface` et `Monitoring`
- `StatutCam.arenaId` et `StatutRemote.arenaId` pointent fonctionnellement vers `TableArena.arenaId`

Le monitoring exploite surtout `StatutCam` comme etat courant, tandis que `Telephones` joue le role d'inventaire de base.

## Relations probables ou polymorphes

- `TableJoueur.equipe_id_ref` -> `TableEquipe.equipe_id`, mais le code privilegie les tables d'abonnement pour les liens actifs
- `MatchAVenir.matchId`, `RapportMatch.matchId`, `Snapshots.matchId` et `abonAppareilMatch.matchId` semblent referencer `TableMatch.matchIdRef`
- `Presences.matchId` semble pointer vers `TableMatch.match_id`, tandis que `Presences.positionId` pointe vers `Positions.positionId`
- `Video.reference` est polymorphe et peut representer une reference d'evenement ou de clip selon le type de video
- `EventType.ActiveCamActionTemplate` semble correspondre a `CamActionTemplate.CamActionTemplateId`
- `DemandeAjoutVideo.eventId` renvoie probablement vers un evenement ou un clip selon `typeEvenement`

## Schema logique recommande pour la lecture

| Entite | Cle technique | Cle metier / usage applicatif | Commentaires |
|---|---|---|---|
| Ligue | `ID_Ligue` | nom, sport, contexte dans `cleValeur` | Racine de la plupart des exports et synchronisations |
| Equipe | `equipe_id` | nom, logo, couleur | Rattachee a une ligue par `abonEquipeLigue` |
| Joueur | `joueur_id` | numero, position, profil | Rattache a une equipe ou a une ligue via tables d'abonnement |
| Match | `match_id` | `matchIdRef` | `matchIdRef` sert de reference partagee avec le mobile et les evenements |
| Evenement | `event_id` | `match_event_id` + `code` + `souscode` | Journal terrain, base des stats et des clips |
| Appareil | variable selon table | `telId` | `telId` est la cle transversale du sous-systeme materiel |

## Flux applicatifs deduits

1. L'utilisateur mobile s'authentifie via `TableUser` puis recupere ses ligues via `AbonnementLigue` et, au besoin, via `abonArbitreLigue`.
2. La ligue est construite pour le mobile a partir de `Ligue`, `abonEquipeLigue`, `TableEquipe`, `abonJoueurEquipe` et `TableJoueur`.
3. Les matchs sont synchronises dans `TableMatch` avec une double identite : `match_id` interne et `matchIdRef` externe.
4. Les evenements terrain sont ecrits dans `TableEvenement0`. Les codes proviennent de `EventType` ou `EventTypeDetail`.
5. Les scripts de calcul derivent ensuite classements, meneurs, gardiens, plus/minus et rapports a partir de `TableMatch` et `TableEvenement0`.
6. La video se rattache ensuite au match et aux evenements via `Clips`, `Video` et `DemandeAjoutVideo`.
7. Le parc terrain remonte son etat par `StatutCam`, `StatutRemote`, `SensorLog` et `Alarms`.

## Points d'attention techniques

- Le schema melange du legacy `MyISAM` sans integrite referentielle et des tables plus recentes en `InnoDB` comme `CamActionTemplate`, `EventTypeDetail`, `Positions`, `Presences`, `DemandeAjoutVideo` et `Stream`
- Plusieurs champs `text` servent d'identifiants metier alors qu'ils seraient aujourd'hui mieux modelises par des `varchar` indexes
- La presence simultanee de `TableEvenement` et `TableEvenement0` suggere une evolution historique. Le code actuel utilise surtout `TableEvenement0`
- Les colonnes `cleValeur`, `settings`, `defaultSettings`, `contexte` et parfois `target` jouent le role de payload JSON ou semi-structure
- Pour une refonte future, la priorite serait d'ajouter des cles etrangeres explicites au moins sur le coeur `Ligue / Equipe / Joueur / Match / Evenement / User`
