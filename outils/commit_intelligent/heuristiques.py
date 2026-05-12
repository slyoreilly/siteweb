MOTS_CLES_PAR_PORTEE = {
    "android": ["AndroidManifest.xml", "build.gradle", "gradle", "SyncAdapter", "Room", "MVVM"],
    "infra": ["nginx", "ansible", "docker", "jenkins", "coredns", "Jenkinsfile", "Dockerfile"],
    "backend": ["auth", "sync", "DTO", "migrations", "event store", "repository", "dao"],
    "video": ["multi-row", "concat", "chrono", "delta", "nettoyage", "video", "vidsdispo", "clip"],
    "api": ["api/", "upsert", "endpoint", "json", "dto", "evenement", "event"],
    "php": [".php", "phpobjects", "scriptsphp", "composer", "namespace", "require_once"],
    "web": [".html", ".css", ".js", "style", "stylesheets", "images", "frontend"],
    "db": ["db/", "sql", "migration", "schema", "table", "alter table"],
    "sync": ["sync", "upsert", "transfert", "replication", "evenements"],
    "config": [".htaccess", "php.ini", ".env", "config", "syncstatsconfig.php"],
    "scripts": ["scripts/", "scriptsphp", "scriptserveur", ".sh", ".ps1"],
    "ui": ["layout", "fragment", "viewmodel", "adapter", "res/layout", "ui"],
    "tests": ["test", "spec", "junit", "mock"],
    "docs": ["docs/", ".md", "README", "documentation", "base_documentaire"],
}


MOTS_CLES_TYPE = {
    "fix": ["fix", "corrige", "bug", "erreur", "crash", "null", "exception", "regression", "notice", "warning"],
    "feat": ["ajout", "nouveau", "feature", "cree", "enable", "support", "endpoint"],
    "refactor": ["refactor", "renomme", "deplace", "simplifie", "nettoyage", "factorise"],
    "docs": [".md", "docs/", "documentation", "README", "base_documentaire"],
    "test": ["test", "spec", "junit", "mock"],
    "build": ["gradle", "docker", "jenkins", "Dockerfile", "Jenkinsfile", "composer.lock"],
    "chore": ["configuration", ".idea", ".gitignore", "config"],
}


def _texte_analyse(fichiers, diff_stat, diff):
    fichiers = fichiers or []
    diff_stat = diff_stat or ""
    diff = diff or ""
    extrait_diff = diff[:12000] if isinstance(diff, str) else ""
    return "\n".join(list(fichiers) + [diff_stat, extrait_diff]).lower()


def _compter_occurrences(texte, mots_cles):
    score = 0
    for mot in mots_cles:
        if mot.lower() in texte:
            score += 1
    return score


def detecter_type_commit(fichiers, diff_stat, diff):
    fichiers = fichiers or []
    diff = diff or ""
    texte = _texte_analyse(fichiers, diff_stat, diff)
    scores = {
        type_commit: _compter_occurrences(texte, mots_cles)
        for type_commit, mots_cles in MOTS_CLES_TYPE.items()
    }

    if any(fichier.endswith((".md", ".txt")) for fichier in fichiers) and max(scores.values(), default=0) <= 1:
        return "docs"

    ajouts = sum(1 for ligne in diff.splitlines() if ligne.startswith("+") and not ligne.startswith("+++"))
    suppressions = sum(1 for ligne in diff.splitlines() if ligne.startswith("-") and not ligne.startswith("---"))
    if ajouts > suppressions * 2 and ajouts > 20:
        scores["feat"] = scores.get("feat", 0) + 1
    if suppressions > ajouts and "nettoyage" in texte:
        scores["refactor"] = scores.get("refactor", 0) + 2
    if any(fichier.endswith((".yml", ".yaml", ".ini", ".json")) for fichier in fichiers):
        scores["chore"] = scores.get("chore", 0) + 1

    type_commit, score = max(scores.items(), key=lambda element: element[1])
    return type_commit if score > 0 else "chore"


def detecter_portee(fichiers, diff_stat, diff, configuration=None):
    fichiers = fichiers or []
    configuration = configuration or {}
    texte = _texte_analyse(fichiers, diff_stat, diff)
    portees_configuration = configuration.get("portees", [])
    scores = {}

    for portee in portees_configuration:
        scores[portee] = _compter_occurrences(texte, [portee])

    for portee, mots_cles in MOTS_CLES_PAR_PORTEE.items():
        scores[portee] = scores.get(portee, 0) + _compter_occurrences(texte, mots_cles)

    if len(fichiers) == 1:
        parties = fichiers[0].replace("\\", "/").split("/")
        if len(parties) >= 2:
            scores[parties[-2].lower()] = scores.get(parties[-2].lower(), 0) + 1

    portee, score = max(scores.items(), key=lambda element: element[1])
    return portee if score > 0 else "general"


def detecter_changements(fichiers, diff_stat, diff):
    fichiers = fichiers or []
    diff_stat = diff_stat or ""
    diff = diff or ""
    changements = []
    extensions = sorted({fichier.rsplit(".", 1)[-1] for fichier in fichiers if "." in fichier})
    texte = _texte_analyse(fichiers, diff_stat, diff)

    if fichiers:
        changements.append(f"met a jour {len(fichiers)} fichier(s)")
    if extensions:
        changements.append("types touches: " + ", ".join(extensions[:5]))

    lignes = [ligne.strip() for ligne in diff_stat.splitlines() if "|" in ligne]
    for ligne in lignes[:3]:
        changements.append(ligne)

    if "network_security_config" in texte:
        changements.append("modifie la configuration de securite reseau")
    if "viewmodel" in texte:
        changements.append("ajuste la logique de ViewModel")
    if "dao" in texte or "repository" in texte:
        changements.append("ajuste la couche de donnees")
    if "video" in texte or "chrono" in texte:
        changements.append("ajuste la logique video ou temporelle")
    if "upsert" in texte or "sync" in texte:
        changements.append("ajuste la synchronisation des donnees")
    if ".php" in texte or "api/" in texte:
        changements.append("ajuste la logique PHP/API")
    if ".html" in texte or ".css" in texte or ".js" in texte:
        changements.append("met a jour le comportement web")

    return list(dict.fromkeys(changements))[:6]


def detecter_intentions(fichiers, diff_stat, diff, portee):
    fichiers = fichiers or []
    portee = portee or "general"
    texte = _texte_analyse(fichiers, diff_stat, diff)
    intentions = []

    if portee == "video" or "video" in texte or "chrono" in texte:
        intentions.append("ameliorer la robustesse video")
    if "nettoyage" in texte or "delete" in texte or "efface" in texte:
        intentions.append("reduire les risques de saturation ou d'accumulation")
    if portee == "sync" or "sync" in texte or "upsert" in texte or "ack" in texte:
        intentions.append("stabiliser le flux de synchronisation")
    if "upload" in texte or "transfert" in texte:
        intentions.append("reduire les blocages de transfert")
    if portee in ("api", "backend", "php") or ".php" in texte or "api/" in texte:
        intentions.append("fiabiliser le comportement serveur")
    if portee == "db" or "migration" in texte or "schema" in texte:
        intentions.append("aligner la structure des donnees")
    if portee in ("web", "ui") or ".html" in texte or ".css" in texte or ".js" in texte:
        intentions.append("ameliorer l'experience web")
    if portee in ("config", "infra", "build") or "docker" in texte or "gradle" in texte:
        intentions.append("stabiliser la configuration d'execution")
    if portee == "docs" or any(fichier.endswith((".md", ".txt")) for fichier in fichiers):
        intentions.append("clarifier le cadre operationnel")
    if portee == "tests" or "test" in texte:
        intentions.append("renforcer la validation du comportement")

    if not intentions:
        intentions.append("garder le depot coherent avec le changement local")

    return list(dict.fromkeys(intentions))[:4]


def generer_resume(type_commit, portee, fichiers, diff_stat, diff):
    diff = diff or ""
    texte = _texte_analyse(fichiers, diff_stat, diff)

    if portee == "video" and "multi-row" in texte:
        return "ameliore la gestion des segments multi-row"
    if portee == "video" and "delta" in texte:
        return "corrige le calcul delta video"
    if portee == "video":
        return "ajuste la logique video"
    if portee == "sync":
        return "ajuste la synchronisation"
    if portee == "api" and type_commit == "fix":
        return "corrige un comportement API"
    if portee == "api":
        return "met a jour les endpoints API"
    if portee == "php":
        return "ajuste la logique PHP"
    if portee == "db":
        return "ajuste la couche donnees"
    if portee == "web":
        return "met a jour le site web"
    if portee == "config":
        return "ajuste la configuration du depot"
    if portee == "scripts":
        return "met a jour les scripts locaux"
    if portee == "ui":
        return "met a jour l'interface"
    if portee == "infra":
        return "ajuste l'infrastructure locale"
    if type_commit == "docs":
        return "met a jour la documentation"
    if type_commit == "test":
        return "ajuste les tests"
    if type_commit == "build":
        return "ajuste la configuration de build"
    if type_commit == "fix":
        return "corrige un comportement localise"
    if type_commit == "feat":
        return "ajoute un comportement"
    if type_commit == "refactor":
        return "simplifie le code existant"

    return "met a jour le depot"


def evaluer_confiance(fichiers, risques):
    fichiers = fichiers or []
    risques = risques or []
    if not fichiers:
        return "faible"
    if len(fichiers) <= 4 and not risques:
        return "elevee"
    if len(fichiers) <= 10 and len(risques) <= 2:
        return "moyenne"
    return "faible"
