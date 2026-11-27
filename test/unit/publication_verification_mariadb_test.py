"""Dialecte réel sur MariaDB jetable, sans réseau, sans volume ni base déployée.

Prérequis : image mariadb:10.6 déjà présente et accès au moteur Docker local.
Réutilise les fixtures SQLite et le SELECT de production du test SQL unitaire.
"""
import json
import re
import subprocess
import time
import uuid

from publication_verification_sql_test import SQL, VerificationExacteTest


def executer(*arguments, entree=None, verifier=True):
    return subprocess.run(arguments, input=entree, text=True, encoding='utf-8',
                          capture_output=True, check=verifier)


def main():
    nom = 'publication-verification-test-' + uuid.uuid4().hex[:12]
    # Secret de fixture uniquement, conteneur sans port ni réseau.
    mot_de_passe = 'fixture-publication-isolee'
    executer('docker', 'run', '--rm', '--detach', '--pull=never', '--network', 'none',
             '--name', nom, '--env', 'MARIADB_ROOT_PASSWORD=' + mot_de_passe, 'mariadb:10.6')
    try:
        client = ['docker', 'exec', '--interactive', nom, 'mariadb', '--user=root',
                  '--password=' + mot_de_passe, '--default-character-set=utf8mb4',
                  '--batch', '--raw', '--skip-column-names']
        for _ in range(50):
            if executer(*client, entree='SELECT 1;', verifier=False).returncode == 0:
                break
            time.sleep(0.5)
        else:
            raise RuntimeError('MariaDB de test indisponible')

        cas = VerificationExacteTest()
        cas.setUp()
        try:
            export = '\n'.join(cas.bd.iterdump())
            export = export.replace('BEGIN TRANSACTION;', '').replace('COMMIT;', '')
            export = re.sub(r'"([A-Za-z_][A-Za-z_0-9]*)"', r'`\1`', export)
            total = 0
            for moteur in ['MyISAM', 'InnoDB']:
                base = 'fixture_' + moteur.lower()
                executer(*client, entree=f'CREATE DATABASE {base} CHARACTER SET utf8mb4;'
                         f'USE {base}; SET default_storage_engine={moteur};\n' + export)
                for ids, equipe in [((7,), 10), ((8,), 10), ((), None), ((999,), 10)]:
                    liste = ','.join('?' for _ in ids) if ids else 'NULL'
                    valeurs = iter([1, 1, 2, equipe, *ids, *ids, equipe, *ids, *ids])
                    sql = re.sub(r'\?', lambda _: 'NULL' if (v := next(valeurs)) is None else str(v),
                                 SQL.replace('$liste', liste))
                    resultat = executer(*client, entree=f'USE {base}; START TRANSACTION READ ONLY;'
                                         + sql + '; ROLLBACK;').stdout
                    groupes = {}
                    for ligne in resultat.splitlines():
                        type_ligne, donnees = ligne.split('\t', 1)
                        groupes.setdefault(type_ligne, []).append(json.loads(donnees))
                    assert groupes == cas.lire(ids, equipe), (moteur, ids, 'Résultat divergent')
                    total += 1
                moteurs = executer(*client, entree=f'SELECT DISTINCT ENGINE FROM information_schema.TABLES '
                                   f"WHERE TABLE_SCHEMA='{base}';").stdout.strip()
                assert moteurs == moteur, (moteur, moteurs)
            print(f'{total} consultations MariaDB 10.6 réussies (MyISAM et InnoDB fictifs).')
        finally:
            cas.doCleanups()
    finally:
        # Le nom aléatoire est celui du seul conteneur créé par ce processus.
        executer('docker', 'stop', '--time', '2', nom)


if __name__ == '__main__':
    main()
