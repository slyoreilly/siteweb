import re
import sqlite3
import unittest
from pathlib import Path

RACINE = Path(__file__).resolve().parents[2]


class MatchActifSurfaceTest(unittest.TestCase):
    def setUp(self):
        source = (RACINE / 'api/lib/match_actif_surface.php').read_text(encoding='utf-8')
        self.requete = re.search(r'REQUETE_MATCH_ACTIF_SURFACE = "([^"]+)"', source).group(1)
        self.bd = sqlite3.connect(':memory:')
        self.addCleanup(self.bd.close)
        # Horloge SQL figée, indépendante du fuseau du poste.
        self.bd.create_function('current_timestamp', 0, lambda: '2026-11-26 12:00:00')
        self.bd.executescript('''
            CREATE TABLE TableArena (arenaId INTEGER PRIMARY KEY);
            CREATE TABLE TableMatch (
                match_id INTEGER PRIMARY KEY, matchIdRef TEXT, arenaId INTEGER,
                statut TEXT, date DATETIME
            );
            CREATE TABLE TableEvenement0 (
                match_event_id TEXT, code INTEGER, souscode INTEGER, chrono INTEGER
            );
            INSERT INTO TableArena VALUES (1), (2);
        ''')

    def ajouter_match(self, identifiant, date='2026-11-26 11:00:00', statut='10', surface=1):
        self.bd.execute('INSERT INTO TableMatch VALUES (?, ?, ?, ?, ?)',
                        (identifiant, str(identifiant), surface, statut, date))

    def evenement(self, identifiant, souscode=0, chrono=1700000001000, code=10):
        self.bd.execute('INSERT INTO TableEvenement0 VALUES (?, ?, ?, ?)',
                        (str(identifiant), code, souscode, chrono))

    def consulter(self, surface=1):
        return self.bd.execute(self.requete, (surface,)).fetchall()

    def test_un_seul_match_ouvert(self):
        self.ajouter_match(101)
        self.assertEqual(self.consulter(), [(1, 101, None)])

    def test_plusieurs_ouverts_priorite_date_pas_chrono_ni_identifiant(self):
        self.ajouter_match(102, '2026-11-25 11:00:00')
        self.ajouter_match(101)
        self.evenement(102, chrono=1800000000000)
        self.evenement(101)
        self.assertEqual(self.consulter(), [(1, 101, 1700000001000)])

    def test_match_futur_exclu_meme_avec_evenement_de_debut(self):
        self.ajouter_match(101)
        self.ajouter_match(102, '2026-11-26 12:00:01')
        self.evenement(102)
        self.assertEqual(self.consulter(), [(1, 101, None)])

    def test_debut_exactement_maintenant_admissible(self):
        self.ajouter_match(101, '2026-11-26 12:00:00')
        self.assertEqual(self.consulter(), [(1, 101, None)])

    def test_statuts_finaux_plus_recents_exclus(self):
        self.ajouter_match(101, '2026-11-20 11:00:00')
        for identifiant, statut in [(102, 'F'), (103, '30')]:
            self.ajouter_match(identifiant, statut=statut)
            self.evenement(identifiant)
        self.assertEqual(self.consulter(), [(1, 101, None)])

    def test_evenement_fin_prevaut_sur_statut_ouvert(self):
        self.ajouter_match(101, '2026-11-20 11:00:00')
        self.ajouter_match(102)
        self.evenement(102)
        self.evenement(102, souscode=10)
        self.assertEqual(self.consulter(), [(1, 101, None)])

    def test_match_ouvert_depuis_plusieurs_jours(self):
        self.ajouter_match(101, '2020-01-01 00:00:00')
        self.assertEqual(self.consulter(), [(1, 101, None)])

    def test_aucun_match_admissible(self):
        self.assertEqual(self.consulter(), [(1, None, None)])
        self.ajouter_match(101, statut=None)
        self.ajouter_match(102, '2026-11-27 00:00:00')
        self.ajouter_match(103, statut='F')
        self.assertEqual(self.consulter(), [(1, None, None)])

    def test_dates_identiques_plus_grand_identifiant(self):
        for identifiant in [103, 101, 102]:
            self.ajouter_match(identifiant)
        self.assertEqual(self.consulter(), [(1, 103, None)])
        self.evenement(103, souscode=10)
        self.assertEqual(self.consulter(), [(1, 102, None)])

    def test_criteres_historiques_et_premier_chrono_positif(self):
        for statut in [None, '', '1P', '2']:
            with self.subTest(statut=statut):
                self.bd.execute('DELETE FROM TableMatch')
                self.bd.execute('DELETE FROM TableEvenement0')
                self.ajouter_match(101, statut=statut)
                self.evenement(101, code=3)
                self.assertEqual(self.consulter(), [(1, None, None)])
                for chrono in [0, -1, 1700000002000, 1700000001000]:
                    self.evenement(101, chrono=chrono)
                self.assertEqual(self.consulter(), [(1, 101, 1700000001000)])

    def test_debut_sans_chrono_positif_reste_ouvert(self):
        self.ajouter_match(101, statut=None)
        self.evenement(101, chrono=0)
        self.assertEqual(self.consulter(), [(1, 101, None)])

    def test_surface_et_date_absente(self):
        self.ajouter_match(101, surface=2)
        self.ajouter_match(102, date=None)
        self.assertEqual(self.consulter(), [(1, None, None)])
        self.assertEqual(self.consulter(2), [(2, 101, None)])
        self.assertEqual(self.consulter(99), [])

    def test_plan_local_avec_index_proposes(self):
        self.bd.executescript((RACINE / 'db/2026-11-26_match_actif_surface_index.sql').read_text(encoding='utf-8'))
        self.ajouter_match(101)
        plan = [ligne[3] for ligne in self.bd.execute('EXPLAIN QUERY PLAN ' + self.requete, (1,))]
        self.assertTrue(any('idx_match_surface_date_id' in ligne for ligne in plan), plan)
        self.assertEqual(sum('idx_evenement_match_code_souscode_chrono' in ligne for ligne in plan), 3, plan)
        self.assertFalse(any('USE TEMP B-TREE' in ligne or 'SCAN e' in ligne for ligne in plan), plan)
        self.assertEqual(self.consulter(), [(1, 101, None)])


if __name__ == '__main__':
    unittest.main()
