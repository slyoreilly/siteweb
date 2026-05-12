# Commit intelligent

Systeme local ultra leger pour creer un commit Git automatique et pousser immediatement les changements.

## Commande manuelle

Depuis la racine du depot:

```powershell
.\outils\commit_intelligent\commit_intelligent.ps1
```

Ou directement:

```powershell
py outils\commit_intelligent\commit_intelligent.py
```

## Comportement

Le script detecte la branche courante, execute `git add .`, analyse le diff staged, genere un message de commit, lance `git commit -m`, puis lance `git push`.

Il n'y a aucune confirmation, aucun editeur Git, aucun hook Git ajoute et aucune interaction utilisateur.

## Commit automatique

Le message suit ce format:

```text
fix(video): corrige delta multi-row

Intention:
- stabiliser le flux video
- reduire les erreurs de sequence temporelle

Changements:
- ajuste logique video

Risques:
- logique chrono sensible

Confiance:
- moyenne
```

## Push automatique

Apres le commit, le script affiche la destination attendue puis execute:

```powershell
git push
```

Si le push echoue, le commit local est conserve et la sortie Git est affichee avec une raison probable.

## Robustesse UTF-8

Tous les appels Git passent par `executer_git(...)` avec `encoding="utf-8"`, `errors="replace"` et `text=True`.

Le diff est toujours protege contre les valeurs nulles et tronque pour les heuristiques afin de rester rapide, meme avec de gros changements ou des fichiers binaires.

## Limite volontaire

Ce systeme attire l'attention sur les risques simples. Il ne bloque pas, ne valide pas le code et ne remplace pas votre jugement.
