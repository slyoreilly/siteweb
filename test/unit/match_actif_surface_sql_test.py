import re
import sqlite3
import unittest
from pathlib import Path


class MatchActifSurfaceTest(unittest.TestCase):
    def test_consultation_par_surface(self):
        source = (Path(__file__).resolve().parents[2] / "api/lib/match_actif_surface.php").read_text()
        requete = re.search(r'REQUETE_MATCH_ACTIF_SURFACE = "([^"]+)"', source).group(1)
        with sqlite3.connect(":memory:") as bd:
            bd.executescript("""
                CREATE TABLE TableArena (arenaId INTEGER PRIMARY KEY);
                CREATE TABLE TableMatch (
                    match_id INTEGER PRIMARY KEY, matchIdRef TEXT, arenaId INTEGER, statut TEXT
                );
                CREATE TABLE TableEvenement0 (
                    match_event_id TEXT, code INTEGER, souscode INTEGER, chrono INTEGER
                );
                INSERT INTO TableArena VALUES (1), (2);
                INSERT INTO TableMatch VALUES
                    (101, 'planifie', 1, NULL),
                    (102, 'spontane', 1, '10'),
                    (103, 'final_historique', 1, 'F'),
                    (104, 'final_moderne', 1, '30'),
                    (105, 'fin_recue_avant_statut', 1, '10'),
                    (106, 'autre_surface', 2, '10');
                INSERT INTO TableEvenement0 VALUES
                    ('planifie', 3, 0, 1700000000000),
                    ('final_historique', 10, 0, 1700000000000),
                    ('final_moderne', 10, 0, 1700000000000),
                    ('fin_recue_avant_statut', 10, 10, 1700000000000);
            """)

            def consulter(arena_id):
                return set(bd.execute(requete, (arena_id,)).fetchall())

            self.assertEqual(consulter(99), set())
            self.assertEqual(consulter(1), {(1, 102, None)})
            bd.executescript("""
                INSERT INTO TableEvenement0 VALUES
                    ('planifie', 10, 0, 1700000001000),
                    ('planifie', 10, 0, 1700000002000);
            """)
            self.assertEqual(consulter(1), {(1, 101, 1700000001000), (1, 102, None)})
            bd.execute("UPDATE TableMatch SET statut = '30' WHERE match_id = 102")
            self.assertEqual(consulter(1), {(1, 101, 1700000001000)})
            bd.execute("INSERT INTO TableEvenement0 VALUES ('planifie', 10, 10, 1700000003000)")
            self.assertEqual(consulter(1), {(1, None, None)})
            self.assertEqual(consulter(2), {(2, 106, None)})


if __name__ == "__main__":
    unittest.main()
