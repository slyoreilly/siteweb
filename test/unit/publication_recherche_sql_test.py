"""Requêtes réelles de la route, sur fixtures SQLite sans connexion Legacy.

SQLite remplace uniquement GREATEST/LEAST ; la collation MySQL reste à vérifier.
"""
import re
import sqlite3
import unittest
from pathlib import Path

RACINE = Path(__file__).resolve().parents[2]
SOURCE = (RACINE / 'api/getPublicationRechercheJoueurs.php').read_text(encoding='utf-8')
REQUETES = dict(re.findall(r'\$(sqlIdentites|sqlEquipes) = "([^"]+)";', SOURCE))
DEBUT, FIN = '2026-01-01', '2026-12-31'


class RechercheJoueursTest(unittest.TestCase):
    def setUp(self):
        self.bd = sqlite3.connect(':memory:')
        self.bd.row_factory = sqlite3.Row
        self.addCleanup(self.bd.close)
        self.bd.create_function('GREATEST', -1, lambda *v: None if None in v else max(v))
        self.bd.create_function('LEAST', -1, lambda *v: None if None in v else min(v))
        self.bd.executescript('''
            CREATE TABLE TableJoueur (joueur_id INTEGER PRIMARY KEY, NomJoueur TEXT,
                NumeroJoueur TEXT, position TEXT);
            CREATE TABLE TableEquipe (equipe_id INTEGER PRIMARY KEY, nom_equipe TEXT);
            CREATE TABLE abonJoueurEquipe (joueurId INTEGER, equipeId INTEGER,
                permission INTEGER, debutAbon TEXT, finAbon TEXT);
            CREATE TABLE abonEquipeLigue (equipeId INTEGER, ligueId INTEGER,
                permission INTEGER, debutAbon TEXT, finAbon TEXT);
            INSERT INTO TableEquipe VALUES (10, 'Équipe cible'), (20, 'Autre équipe'), (30, 'Hors ligue');
            INSERT INTO abonEquipeLigue VALUES
                (10, 1, 10, '2026-01-01', '2026-12-31'),
                (20, 1, 10, '2026-01-01', '2026-12-31'),
                (30, 2, 10, '2026-01-01', '2026-12-31');
        ''')

    def joueur(self, identifiant, nom='Joueur', equipe=20, debut=DEBUT, fin=FIN, permission=10):
        self.bd.execute('INSERT OR IGNORE INTO TableJoueur VALUES (?, ?, ?, ?)',
                        (identifiant, nom, '0', 'C'))
        self.bd.execute('INSERT INTO abonJoueurEquipe VALUES (?, ?, ?, ?, ?)',
                        (identifiant, equipe, permission, debut, fin))

    def rechercher(self, nom='Joueur', cible=10, debut=DEBUT, fin=FIN):
        motif = '%' + nom.strip().replace('!', '!!').replace('%', '!%').replace('_', '!_') + '%'
        return self.bd.execute(REQUETES['sqlIdentites'], (cible, 1, debut, fin, motif)).fetchall()

    def equipes(self, identifiants, debut=DEBUT, fin=FIN):
        sql = REQUETES['sqlEquipes'].replace('$marqueurs', ','.join('?' * len(identifiants)))
        return self.bd.execute(sql, (1, debut, fin, *identifiants)).fetchall()

    def test_inscriptions_multiples_et_absence_abon_joueur_ligue(self):
        # La table abonJoueurLigue n'existe même pas dans cette fixture.
        for equipe in (10, 10, 20, 30):
            self.joueur(1, equipe=equipe)
        self.bd.execute('INSERT INTO abonEquipeLigue SELECT * FROM abonEquipeLigue')
        lignes = self.rechercher()
        self.assertEqual([(j['id'], j['presentDansEquipeCible']) for j in lignes], [(1, 1)])
        self.assertEqual({e['id'] for e in self.equipes([1])}, {10, 20})
        self.assertEqual(len(self.equipes([1])), 2)

    def test_periodes_separees_dans_une_meme_saison(self):
        self.bd.execute("UPDATE abonEquipeLigue SET debutAbon='2026-07-01' WHERE equipeId=10")
        self.joueur(1, equipe=10, fin='2026-06-30')
        self.joueur(1, equipe=20)
        self.joueur(2, equipe=10, fin='2026-06-30')
        lignes = self.rechercher()
        self.assertEqual([(j['id'], j['presentDansEquipeCible']) for j in lignes], [(1, 0)])
        self.assertEqual([e['id'] for e in self.equipes([1])], [20])

    def test_bornes_inclusives_et_instant_courant(self):
        self.joueur(1, equipe=10, debut=FIN, fin='2027-01-01')
        self.joueur(2, debut='2025-01-01', fin=DEBUT)
        self.joueur(3, debut='2027-01-01', fin='2028-01-01')
        self.assertEqual([j['id'] for j in self.rechercher()], [1, 2])
        self.assertEqual([j['id'] for j in self.rechercher(debut=FIN, fin=FIN)], [1])
        self.assertEqual([e['id'] for e in self.equipes([1], FIN, FIN)], [10])

    def test_permissions_et_autres_ligues(self):
        self.joueur(1, permission=31)
        self.joueur(2, equipe=30)
        self.joueur(3, equipe=10)
        self.bd.execute('UPDATE abonEquipeLigue SET permission=31 WHERE equipeId=10')
        self.assertEqual(self.rechercher(), [])
        self.assertEqual(self.equipes([1, 2, 3]), [])

    def test_priorite_avant_limite_et_troncature_par_identite(self):
        for identifiant in range(1, 56):
            self.joueur(identifiant, nom='Joueur A')
        for _ in range(80):
            self.joueur(999, nom='Joueur Z cible', equipe=10)
        lignes = self.rechercher()
        self.assertEqual(len(lignes), 51)
        self.assertEqual([j['id'] for j in lignes[:50]], [999, *range(1, 50)])
        self.assertEqual(len(self.equipes([j['id'] for j in lignes[:50]])), 50)
        self.bd.execute('DELETE FROM abonJoueurEquipe WHERE joueurId BETWEEN 50 AND 55')
        self.assertEqual(len(self.rechercher()), 50)

    def test_homonymes_et_cible_facultative(self):
        self.joueur(2)
        self.joueur(1)
        self.assertEqual([j['id'] for j in self.rechercher(cible=None)], [1, 2])
        self.assertTrue(all(j['presentDansEquipeCible'] == 0 for j in self.rechercher(cible=None)))

    def test_plus_de_50_candidats_dans_la_cible(self):
        self.joueur(1, nom='Joueur A autre')
        for identifiant in range(100, 155):
            self.joueur(identifiant, nom='Joueur Z cible', equipe=10, permission=30)
        lignes = self.rechercher()
        self.assertEqual([j['id'] for j in lignes[:50]], list(range(100, 150)))
        self.assertTrue(all(j['presentDansEquipeCible'] == 1 for j in lignes))

    def test_dates_nulles_et_periodes_inversees(self):
        self.joueur(1, debut=None)
        self.joueur(2, fin=None)
        self.joueur(3, debut=FIN, fin=DEBUT)
        self.joueur(4, equipe=10)
        self.bd.execute('UPDATE abonEquipeLigue SET finAbon=NULL WHERE equipeId=10')
        self.assertEqual(self.rechercher(), [])
        self.assertEqual(self.equipes([1, 2, 3, 4]), [])

    def test_caracteres_speciaux_et_injection(self):
        for identifiant, nom in enumerate(["Élodie O'Neil", 'Joueur 100%_!', 'Joueur 100abc',
                                          "Joueur ' OR 1=1 --"], 1):
            self.joueur(identifiant, nom=nom)
        for motif, attendu in [("O'Neil", 1), ('100%_!', 2), ("' OR 1=1 --", 4), ('Élodie', 1)]:
            with self.subTest(motif=motif):
                self.assertEqual([j['id'] for j in self.rechercher(motif)], [attendu])
        self.assertEqual(self.rechercher('Absent'), [])


if __name__ == '__main__':
    unittest.main()
