CHEMINS_RISQUE_ELEVE = [
    "migrations",
    "vault",
    "inventory",
    "network_security_config",
    "sync",
    "chrono",
    "video",
    "docker-compose",
    "Jenkinsfile",
    ".htaccess",
    "php.ini",
    "composer.lock",
    "scriptTransfert.sh",
    "scriptsphp",
    "db",
    "api",
]


MOTS_RISQUE_DIFF = [
    ("DELETE", "suppression SQL detectee"),
    ("DROP TABLE", "operation destructrice SQL possible"),
    ("ALTER TABLE", "migration ou changement de schema"),
    ("TRUNCATE", "vidage de table possible"),
    ("password", "secret ou mot de passe possible"),
    ("passwd", "secret ou mot de passe possible"),
    ("token", "jeton ou secret possible"),
    ("secret", "secret possible"),
    ("private_key", "cle privee possible"),
    ("network_security_config", "configuration reseau modifiee"),
    ("chmod 777", "permission tres permissive detectee"),
]


def _contient(fragment, texte):
    fragment = fragment or ""
    texte = texte or ""
    return fragment.lower() in texte.lower()


def detecter_risques(fichiers, diff, configuration=None):
    fichiers = fichiers or []
    diff = diff or ""
    configuration = configuration or {}
    chemins_configuration = configuration.get("chemins_risque_eleve", [])
    chemins_risque = CHEMINS_RISQUE_ELEVE + list(chemins_configuration)
    risques = []

    for fichier in fichiers:
        for chemin in chemins_risque:
            if _contient(chemin, fichier):
                risques.append(f"chemin sensible modifie: {fichier}")
                break

    for motif, libelle in MOTS_RISQUE_DIFF:
        if _contient(motif, diff):
            risques.append(libelle)

    texte = "\n".join(fichiers + [diff[:12000]]).lower()
    if "chrono" in texte or "temps" in texte or "timestamp" in texte:
        risques.append("logique chrono sensible")
    if "sync" in texte or "upsert" in texte:
        risques.append("logique de synchronisation sensible")
    if "auth" in texte or "session" in texte or "login" in texte:
        risques.append("surface auth/session touchee")

    if len(fichiers) >= 12:
        risques.append(f"commit large: {len(fichiers)} fichiers touches")

    lignes_supprimees = sum(1 for ligne in diff.splitlines() if ligne.startswith("-") and not ligne.startswith("---"))
    if lignes_supprimees >= 80:
        risques.append(f"suppression importante: {lignes_supprimees} lignes retirees")

    return list(dict.fromkeys(risques))
