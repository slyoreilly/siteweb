import re
import sqlite3
import unittest
from pathlib import Path


class CamerasSurfaceTest(unittest.TestCase):
    def test_associations_par_surface(self):
        chemin = Path(__file__).resolve().parents[2] / "api/lib/cameras_surface.php"
        requete = re.search(r'REQUETE_CAMERAS_SURFACE = "([^"]+)"', chemin.read_text(encoding="utf-8"))[1]
        with sqlite3.connect(":memory:") as bd:
            bd.executescript("""
                CREATE TABLE TableArena (arenaId INTEGER PRIMARY KEY);
                CREATE TABLE Telephones (telId INTEGER PRIMARY KEY);
                CREATE TABLE abonAppareilSurface (surfaceId INTEGER, telId INTEGER, role INTEGER);
                INSERT INTO TableArena VALUES (1), (2), (3), (4);
                INSERT INTO Telephones VALUES (0), (10), (20), (30), (40), (50);
                INSERT INTO abonAppareilSurface VALUES
                    (1, 20, 101), (1, 20, 111), (1, 10, 0),
                    (1, 30, 1), (1, 0, 101), (1, 999, 101),
                    (3, 40, 112), (4, 50, 3), (4, 999, 101);
            """)
            self.assertEqual(bd.execute(requete, (1,)).fetchall(), [(None,), (10,), (20,)])
            self.assertEqual(bd.execute(requete, (2,)).fetchall(), [(None,)])
            self.assertEqual(bd.execute(requete, (4,)).fetchall(), [(None,)])
            self.assertEqual(bd.execute(requete, (5,)).fetchall(), [])
            bd.execute("DELETE FROM abonAppareilSurface WHERE surfaceId = 3")
            self.assertEqual(bd.execute(requete, (3,)).fetchall(), [(None,)])


if __name__ == "__main__":
    unittest.main()
