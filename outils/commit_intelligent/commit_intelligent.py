import subprocess
import sys
from pathlib import Path

from configuration_depot import lire_configuration
from formateur_commit import generer_message_commit
from heuristiques import (
    detecter_changements,
    detecter_intentions,
    detecter_portee,
    detecter_type_commit,
    evaluer_confiance,
    generer_resume,
)
from regles_risques import detecter_risques


class ErreurGit(RuntimeError):
    def __init__(self, commande, code_retour=None, sortie="", erreur="", message=None):
        self.commande = commande
        self.code_retour = code_retour
        self.sortie = sortie or ""
        self.erreur = erreur or ""
        detail = message or self.erreur.strip() or self.sortie.strip() or "commande git echouee"
        super().__init__(f"{' '.join(commande)} a echoue: {detail}")


def executer_git(arguments, capturer=True):
    commande = ["git", *arguments]
    try:
        resultat = subprocess.run(
            commande,
            text=True,
            encoding="utf-8",
            errors="replace",
            capture_output=capturer,
            check=False,
        )
    except OSError as erreur:
        raise ErreurGit(commande, message=f"impossible d'executer la commande: {erreur}") from erreur

    if resultat.returncode != 0:
        raise ErreurGit(
            commande,
            code_retour=resultat.returncode,
            sortie=resultat.stdout,
            erreur=resultat.stderr,
        )

    return (resultat.stdout or "") if capturer else ""


def verifier_depot_git():
    try:
        racine = executer_git(["rev-parse", "--show-toplevel"]).strip()
    except ErreurGit as erreur:
        print(f"Impossible de trouver le depot Git: {erreur}", file=sys.stderr)
        return None
    return Path(racine)


def changements_disponibles():
    statut = executer_git(["status", "--porcelain"]) or ""
    return bool(statut.strip())


def detecter_branche_courante():
    branche = executer_git(["branch", "--show-current"]).strip()
    if branche:
        return branche
    return "(DETACHED HEAD)"


def afficher_erreur_git(erreur):
    print(f"Commande Git: {' '.join(erreur.commande)}", file=sys.stderr)
    print(f"Code retour: {erreur.code_retour}", file=sys.stderr)
    print("stderr Git:", file=sys.stderr)
    print(erreur.erreur.strip() or "(vide)", file=sys.stderr)

    sortie = erreur.sortie.strip()
    if sortie:
        print("stdout Git:", file=sys.stderr)
        print(sortie, file=sys.stderr)

    texte = f"{erreur.erreur}\n{erreur.sortie}".lower()
    raison = "erreur Git a diagnostiquer avec la sortie ci-dessus"
    if "nothing to commit" in texte or "no changes added" in texte:
        raison = "aucun changement a commiter apres la preparation"
    elif "author identity unknown" in texte or "unable to auto-detect email address" in texte:
        raison = "identite Git utilisateur non configuree"
    elif "conflict" in texte or "unmerged" in texte:
        raison = "conflit ou fichiers non fusionnes dans l'index"
    elif "pre-commit" in texte or "hook" in texte:
        raison = "un hook Git local a refuse le commit"
    elif "no upstream branch" in texte or "has no upstream branch" in texte:
        raison = "branche sans upstream Git configure"
    elif "rejected" in texte or "fetch first" in texte:
        raison = "push refuse par le distant, probablement besoin de pull/rebase"
    elif "authentication failed" in texte or "permission denied" in texte:
        raison = "authentification ou permission Git distante refusee"
    elif "could not resolve host" in texte or "failed to connect" in texte:
        raison = "reseau ou distant Git inaccessible"

    print(f"Raison probable: {raison}", file=sys.stderr)


def main():
    racine = verifier_depot_git()
    if not racine:
        return 1

    try:
        if not changements_disponibles():
            print("Aucun changement a commiter.")
            return 0
    except ErreurGit as erreur:
        print(f"Impossible de lire le statut Git: {erreur}", file=sys.stderr)
        return 1

    configuration = lire_configuration(racine)

    try:
        branche = detecter_branche_courante()
    except ErreurGit as erreur:
        print("Impossible de detecter la branche Git courante.", file=sys.stderr)
        afficher_erreur_git(erreur)
        return 1

    try:
        print("Ajout des changements locaux...")
        executer_git(["add", "."], capturer=False)

        fichiers = [
            ligne.strip()
            for ligne in executer_git(["diff", "--cached", "--name-only"]).splitlines()
            if ligne.strip()
        ]
    except ErreurGit as erreur:
        print(f"Impossible de preparer les changements: {erreur}", file=sys.stderr)
        return 1

    if not fichiers:
        print("Aucun changement staged apres git add.")
        return 0

    try:
        diff_stat = executer_git(["diff", "--cached", "--stat"]) or ""
        diff = executer_git(["diff", "--cached"]) or ""
    except ErreurGit as erreur:
        print("Impossible de lire le diff staged.", file=sys.stderr)
        print(f"Diagnostic Git: {erreur}", file=sys.stderr)
        print("Le commit intelligent est annule, sans validation automatique.", file=sys.stderr)
        return 1

    type_commit = detecter_type_commit(fichiers, diff_stat, diff)
    portee = detecter_portee(fichiers, diff_stat, diff, configuration)
    resume = generer_resume(type_commit, portee, fichiers, diff_stat, diff)
    intentions = detecter_intentions(fichiers, diff_stat, diff, portee)
    changements = detecter_changements(fichiers, diff_stat, diff)
    risques = detecter_risques(fichiers, diff, configuration)
    confiance = evaluer_confiance(fichiers, risques)
    message = generer_message_commit(type_commit, portee, resume, intentions, changements, risques, confiance)

    print("")
    print("========================================")
    print("COMMIT INTELLIGENT")
    print("========================================")
    print("")
    print(f"BRANCHE : {branche.upper()}")
    print("")
    print(message)
    print("========================================")

    try:
        sortie_commit = executer_git(["commit", "-m", message])
    except ErreurGit as erreur:
        print("Impossible de creer le commit.", file=sys.stderr)
        afficher_erreur_git(erreur)
        return 1

    if sortie_commit.strip():
        print(sortie_commit.strip())

    print("Commit cree avec succes.")

    print("")
    print(f"Push vers origin/{branche}...")
    try:
        sortie_push = executer_git(["push"])
    except ErreurGit as erreur:
        print("Le commit est cree, mais le push a echoue.", file=sys.stderr)
        afficher_erreur_git(erreur)
        return 1

    if sortie_push.strip():
        print(sortie_push.strip())

    print("Push reussi.")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
