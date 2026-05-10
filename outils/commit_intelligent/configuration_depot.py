from pathlib import Path


CONFIGURATION_PAR_DEFAUT = {
    "type_depot": "generique",
    "portees": [],
    "chemins_risque_eleve": [],
}


def _valeur_simple(texte):
    texte = texte.strip()
    if not texte:
        return ""
    if (texte.startswith('"') and texte.endswith('"')) or (
        texte.startswith("'") and texte.endswith("'")
    ):
        return texte[1:-1]
    return texte


def _lire_yaml_minimal(contenu):
    configuration = {}
    cle_courante = None

    for ligne in contenu.splitlines():
        ligne_sans_commentaire = ligne.split("#", 1)[0].rstrip()
        if not ligne_sans_commentaire.strip():
            continue

        ligne = ligne_sans_commentaire.strip()
        if ligne.startswith("- ") and cle_courante:
            configuration.setdefault(cle_courante, []).append(_valeur_simple(ligne[2:]))
            continue

        if ":" not in ligne:
            continue

        cle, valeur = ligne.split(":", 1)
        cle = cle.strip()
        valeur = valeur.strip()
        cle_courante = cle

        if valeur:
            configuration[cle] = _valeur_simple(valeur)
        else:
            configuration[cle] = []

    return configuration


def lire_configuration(chemin_depot=None):
    racine = Path(chemin_depot or ".").resolve()
    chemin_configuration = racine / ".commitintelligent.yml"
    configuration = dict(CONFIGURATION_PAR_DEFAUT)

    if not chemin_configuration.exists():
        return configuration

    try:
        contenu = chemin_configuration.read_text(encoding="utf-8")
        donnees = _lire_yaml_minimal(contenu)
    except OSError:
        return configuration

    for cle in CONFIGURATION_PAR_DEFAUT:
        if cle in donnees:
            configuration[cle] = donnees[cle]

    return configuration
