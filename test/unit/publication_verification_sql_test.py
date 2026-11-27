"""SELECT de production sur SQLite isolé : ne valide pas le schéma déployé."""
import json
import re
import sqlite3
import unittest
from pathlib import Path

RACINE = Path(__file__).resolve().parents[2]
SOURCE = (RACINE / 'api/lib/publication_verification.php').read_text(encoding='utf-8')
SQL = re.search(r'\$sql = "(.*?)";', SOURCE, re.S).group(1)


class VerificationExacteTest(unittest.TestCase):
    def setUp(self):
        self.bd = sqlite3.connect(':memory:')
        self.addCleanup(self.bd.close)
        self.bd.executescript('''
            CREATE TABLE Ligue (ID_Ligue INTEGER, Nom_Ligue TEXT);
            CREATE TABLE TableSaison (saisonId INTEGER, ligueRef INTEGER, premierMatch TEXT, dernierMatch TEXT);
            CREATE TABLE TableEquipe (equipe_id INTEGER, nom_equipe TEXT);
            CREATE TABLE TableJoueur (joueur_id INTEGER, NomJoueur TEXT, NumeroJoueur TEXT, position TEXT);
            CREATE TABLE abonEquipeLigue (equipeId INTEGER, ligueId INTEGER, permission INTEGER, debutAbon TEXT, finAbon TEXT);
            CREATE TABLE abonJoueurEquipe (abonJouEq INTEGER, joueurId INTEGER, equipeId INTEGER, permission INTEGER, debutAbon TEXT, finAbon TEXT);
            CREATE TABLE abonJoueurLigue (abonJouLig INTEGER, joueurId INTEGER, ligueId INTEGER, permission INTEGER, debutAbon TEXT, finAbon TEXT);
            INSERT INTO Ligue VALUES (1, 'Test'), (9, 'Autre ligue');
            INSERT INTO TableSaison VALUES (2, 1, '2026-01-01', '2026-12-31');
            INSERT INTO TableEquipe VALUES (10, 'Cible'), (20, 'A'), (30, 'Autre ligue');
            INSERT INTO abonEquipeLigue VALUES (10,1,10,'2026-01-01','2026-12-31'),
                (20,1,10,'2026-01-01','2026-12-31'), (30,9,10,'2026-01-01','2026-12-31');
            INSERT INTO TableJoueur VALUES (7,'Sélectionné','0','C'), (8,'Affilié non sélectionné','8','A');
            INSERT INTO abonJoueurEquipe VALUES (70,7,10,10,'2026-01-01','2026-12-31'),
                (71,7,20,10,'2026-01-01','2026-12-31'), (72,7,30,10,'2026-01-01','2026-12-31'),
                (80,8,10,10,'2026-01-01','2026-12-31');
            INSERT INTO abonJoueurLigue VALUES (90,7,9,10,'2026-01-01','2026-12-31');
        ''')

    def lire(self, ids=(7,), equipe=10):
        avant = list(self.bd.iterdump())
        sql = SQL.replace('$liste', ','.join('?' for _ in ids) if ids else 'NULL')
        valeurs = [1, 1, 2, equipe, *ids, *ids, equipe, *ids, *ids]
        groupes = {}
        for type_ligne, donnees in self.bd.execute(sql, valeurs):
            groupes.setdefault(type_ligne, []).append(json.loads(donnees))
        self.assertEqual(avant, list(self.bd.iterdump()))
        return groupes

    def test_identifiants_et_affiliations_completes(self):
        r = self.lire()
        self.assertEqual([j['id'] for j in r['joueur']], [7])
        self.assertEqual({a['equipeId'] for a in r['joueurEquipe']}, {10, 20, 30})
        self.assertEqual({a['ligueId'] for a in r['equipeLigue']}, {1, 9})
        self.assertEqual(r['joueurLigue'][0]['ligueId'], 9)

    def test_identite_hors_cible(self):
        self.bd.execute('DELETE FROM abonJoueurEquipe WHERE equipeId=10 AND joueurId=7')
        self.assertEqual(self.lire()['joueur'][0]['id'], 7)

    def test_periodes_multiples_conservees(self):
        self.bd.execute('INSERT INTO abonJoueurEquipe SELECT * FROM abonJoueurEquipe WHERE abonJouEq=70')
        self.assertEqual(len(self.lire()['joueurEquipe']), 4)

    def test_aucune_troncature_a_50(self):
        self.bd.executemany('INSERT INTO TableJoueur VALUES (?, ?, NULL, NULL)',
                            [(i, 'Homonyme') for i in range(100, 200)])
        self.assertEqual(len(self.lire(tuple(range(100, 200)))['joueur']), 100)

    def test_absence_n_est_pas_creation(self):
        self.assertNotIn('joueur', self.lire((999,)))

    def test_creation_equipe_seulement_preparee(self):
        r = self.lire((), None)
        self.assertNotIn('equipe', r)
        self.assertNotIn('joueur', r)
        self.assertIn('saison', r)

    def test_repetition_et_affilie_selectionne(self):
        self.assertEqual(self.lire((8,)), self.lire((8,)))
        self.assertEqual([j['id'] for j in self.lire((8,))['joueur']], [8])

    def test_periode_historique_et_permission_conservees(self):
        self.bd.execute("UPDATE abonJoueurEquipe SET permission=40, finAbon='2025-01-01' WHERE abonJouEq=70")
        self.assertEqual(self.lire()['joueurEquipe'][0]['permission'], 40)


if __name__ == '__main__':
    unittest.main()
