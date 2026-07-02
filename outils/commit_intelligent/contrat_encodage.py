"""Contrat canonique d'encodage (UTF-8 sans BOM) pour la chaine commit intelligent.

Applique l'ADR SYNC_ADR_ENCODAGE_001 :
- rendre les frontieres texte deterministes (UTF-8 explicite, jamais le defaut locale) ;
- ne JAMAIS reparer ni reinterpreter des octets silencieusement ;
- toute anomalie est conservee telle quelle puis signalee via ENCODING_POLICY_VIOLATION.

Ce module ne corrige aucun contenu. Il instrumente et rend explicite.
"""

from __future__ import annotations

import os
import sys

# Caractere de remplacement produit par un decodage UTF-8 sur octets invalides.
CARACTERE_REMPLACEMENT = "�"

# Motifs mojibake typiques (double-encodage UTF-8 relu comme CP1252/OEM).
_MOTIFS_MOJIBAKE = ("Ã", "Â", "â€™", "â€œ", "â€", "Ð", "▒")


def configurer_io_utf8() -> None:
    """Force stdout/stderr en UTF-8, de maniere idempotente et sans bloquer.

    N'altere pas le texte emis : rend seulement l'encodage de sortie explicite,
    independamment de la locale ou de la codepage console.
    """
    os.environ.setdefault("PYTHONUTF8", "1")
    os.environ.setdefault("PYTHONIOENCODING", "utf-8")
    for flux in (sys.stdout, sys.stderr):
        reconfigure = getattr(flux, "reconfigure", None)
        if reconfigure is None:
            continue
        try:
            # backslashreplace : jamais de remplacement silencieux par U+FFFD.
            reconfigure(encoding="utf-8", errors="backslashreplace")
        except (ValueError, OSError):
            # Flux deja fige ou redirige : on continue sans casser le flux metier.
            pass


def nom_repo_courant() -> str:
    """Nom du depot courant (basename du repertoire de travail)."""
    return os.path.basename(os.getcwd()) or "(inconnu)"


def detecter_anomalies(texte: str) -> list[str]:
    """Liste les anomalies d'encodage d'un texte DEJA decode. Ne modifie rien.

    Detection seulement : caractere de remplacement et motifs mojibake.
    """
    if not texte:
        return []
    anomalies: list[str] = []
    if CARACTERE_REMPLACEMENT in texte:
        anomalies.append("caractere_remplacement_U+FFFD")
    for motif in _MOTIFS_MOJIBAKE:
        if motif in texte:
            anomalies.append(f"mojibake:{motif}")
    return anomalies


def signaler_violation_encodage(
    *,
    repo: str,
    script: str,
    fichier: str,
    operation: str,
    encodage_detecte: str,
    action_prise: str,
    extrait: str = "",
) -> None:
    """Emet une ligne ENCODING_POLICY_VIOLATION sur stderr. Ne corrige rien.

    Le signal est non bloquant : le flux metier continue, mais l'anomalie cesse
    d'etre silencieuse (cf. ADR section 3.4 - politique de preservation).
    """
    extrait_court = (extrait or "").replace("\r", " ").replace("\n", " ")[:120]
    ligne = (
        "ENCODING_POLICY_VIOLATION"
        f" | repo={repo}"
        f" | script={script}"
        f" | fichier={fichier}"
        f" | operation={operation}"
        f" | encodage_detecte={encodage_detecte}"
        f" | action={action_prise}"
        f" | extrait={extrait_court!r}"
    )
    print(ligne, file=sys.stderr)


def inspecter_et_signaler(
    contenu: str,
    *,
    script: str,
    fichier: str,
    operation: str,
) -> list[str]:
    """Detecte les anomalies d'un flux capte et signale sans modifier le contenu.

    Retourne la liste des anomalies (vide si sain). Le contenu est preserve
    integralement par l'appelant : aucune correction n'est appliquee ici.
    """
    anomalies = detecter_anomalies(contenu)
    if anomalies:
        signaler_violation_encodage(
            repo=nom_repo_courant(),
            script=script,
            fichier=fichier,
            operation=operation,
            encodage_detecte="utf-8(decode)/" + ",".join(anomalies),
            action_prise="signale_conserve",
            extrait=contenu,
        )
    return anomalies
