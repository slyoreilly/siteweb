def generer_message_commit(type_commit, portee, resume, intentions, changements, risques, confiance):
    entete = f"{type_commit}({portee}): {resume}"
    lignes = [
        entete,
        "",
        "Intention:",
    ]

    if intentions:
        lignes.extend(f"- {intention}" for intention in intentions)
    else:
        lignes.append("- garder le depot coherent avec le changement local")

    lignes.extend([
        "",
        "Changements:",
    ])

    if changements:
        lignes.extend(f"- {changement}" for changement in changements)
    else:
        lignes.append("- changements locaux detectes")

    lignes.extend(["", "Risques:"])
    if risques:
        lignes.extend(f"- {risque}" for risque in risques)
    else:
        lignes.append("- aucun risque simple detecte")

    lignes.extend(["", "Confiance:", f"- {confiance}"])
    return "\n".join(lignes) + "\n"
