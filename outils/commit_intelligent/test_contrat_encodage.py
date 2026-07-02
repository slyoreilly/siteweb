"""Tests de validation du contrat d'encodage de la chaine commit intelligent.

Execution :  py outils/commit_intelligent/test_contrat_encodage.py

Verifie deux proprietes :
  1. Preservation  : entree == sortie a travers les frontieres texte (fichier UTF-8
     et capture de sortie d'un sous-processus, comme pour Git).
  2. Detection     : un flux d'octets corrompus declenche une anomalie et n'est
     jamais "repare" silencieusement.

Aucune des sorties ne doit faire apparaitre : Ã, â€™, � (sur entree saine).
"""

from __future__ import annotations

import os
import subprocess
import sys
import tempfile

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from contrat_encodage import detecter_anomalies  # noqa: E402

CAS = {
    "cas1_presence": "présence",
    "cas2_equipe_montreal": "Équipe Montréal",
    "cas3_apostrophe_typographique": "l’usage de l’outil",
    "cas4_emoji": "build vidéo \U0001F3A5 prêt",
}

MARQUEURS_INTERDITS = ("Ã", "â€™", "�")


def _roundtrip_fichier(texte: str) -> str:
    """Ecrit en UTF-8 sans BOM puis relit en UTF-8."""
    fd, chemin = tempfile.mkstemp(suffix=".txt")
    os.close(fd)
    try:
        with open(chemin, "w", encoding="utf-8", newline="") as f:
            f.write(texte)
        with open(chemin, "rb") as f:
            brut = f.read()
        assert not brut.startswith(b"\xef\xbb\xbf"), "BOM interdit en sortie"
        with open(chemin, "r", encoding="utf-8") as f:
            return f.read()
    finally:
        os.unlink(chemin)


def _roundtrip_subprocess(texte: str) -> str:
    """Simule la frontiere Git : un enfant emet la chaine en UTF-8, capturee
    avec les memes parametres que executer_git()."""
    code = "import sys; sys.stdout.reconfigure(encoding='utf-8'); sys.stdout.write(sys.argv[1])"
    env = dict(os.environ)
    env["PYTHONUTF8"] = "1"
    env["PYTHONIOENCODING"] = "utf-8"
    res = subprocess.run(
        [sys.executable, "-c", code, texte],
        capture_output=True,
        text=True,
        encoding="utf-8",
        errors="replace",
        env=env,
    )
    return res.stdout


def _capture_octets_corrompus() -> str:
    """Emet des octets CP1252 bruts (é = 0xE9), invalides en UTF-8, captures
    avec errors='replace' comme la chaine reelle."""
    code = "import sys; sys.stdout.buffer.write(b'pr\\xe9sence')"
    res = subprocess.run(
        [sys.executable, "-c", code],
        capture_output=True,
        text=True,
        encoding="utf-8",
        errors="replace",
    )
    return res.stdout


def executer() -> int:
    echecs: list[str] = []

    # 1. Preservation + absence de mojibake sur entree saine.
    for nom, attendu in CAS.items():
        for frontiere, obtenu in (
            ("fichier", _roundtrip_fichier(attendu)),
            ("subprocess", _roundtrip_subprocess(attendu)),
        ):
            if obtenu != attendu:
                echecs.append(f"{nom}/{frontiere}: entree != sortie ({obtenu!r})")
            for marqueur in MARQUEURS_INTERDITS:
                if marqueur in obtenu:
                    echecs.append(f"{nom}/{frontiere}: marqueur interdit {marqueur!r}")
            if detecter_anomalies(obtenu):
                echecs.append(f"{nom}/{frontiere}: faux positif de detection")

    # 2. Detection : un flux corrompu doit etre signale (et non repare en silence).
    corrompu = _capture_octets_corrompus()
    anomalies = detecter_anomalies(corrompu)
    if "�" not in corrompu:
        echecs.append("detection: le flux corrompu aurait du contenir U+FFFD")
    if not anomalies:
        echecs.append("detection: aucune anomalie levee sur flux corrompu")

    # Rapport.
    print("=" * 50)
    print("TEST CONTRAT ENCODAGE - chaine commit intelligent")
    print("=" * 50)
    for nom, attendu in CAS.items():
        print(f"  [{nom}] entree preservee : {attendu!r}")
    print(f"  [detection] flux corrompu -> {anomalies}")
    print("-" * 50)
    if echecs:
        print(f"ECHEC ({len(echecs)}) :")
        for e in echecs:
            print(f"  - {e}")
        return 1
    print("SUCCES : preservation et detection conformes au contrat.")
    return 0


if __name__ == "__main__":
    raise SystemExit(executer())
