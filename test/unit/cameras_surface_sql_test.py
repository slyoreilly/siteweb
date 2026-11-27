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
                CREATE TABLE StatutCam (telId INTEGER, camId INTEGER);
                CREATE TABLE abonAppareilSurface (surfaceId INTEGER, telId INTEGER, role INTEGER);
                INSERT INTO TableArena VALUES (1), (2), (3), (4);
                INSERT INTO Telephones VALUES (0), (10), (20), (30), (40), (50);
                INSERT INTO StatutCam VALUES (0, 90), (10, 10), (20, 20),
                    (30, 30), (40, 40), (50, 50), (999, 99);
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

    def test_identifiants_diffusion_distincts_des_identifiants_materiels(self):
        chemin = Path(__file__).resolve().parents[2] / "api/lib/cameras_surface.php"
        requete = re.search(r'REQUETE_CAMERAS_SURFACE = "([^"]+)"', chemin.read_text(encoding="utf-8"))[1]
        with sqlite3.connect(":memory:") as bd:
            bd.executescript("""
                CREATE TABLE TableArena (arenaId INTEGER PRIMARY KEY);
                CREATE TABLE Telephones (telId INTEGER PRIMARY KEY);
                CREATE TABLE StatutCam (telId INTEGER, camId INTEGER);
                CREATE TABLE abonAppareilSurface (surfaceId INTEGER, telId INTEGER, role INTEGER);
                INSERT INTO TableArena VALUES (1), (2);
                INSERT INTO Telephones VALUES
                    (123456789012345), (223456789012345), (323456789012345),
                    (423456789012345), (523456789012345), (623456789012345);
                INSERT INTO StatutCam VALUES
                    (123456789012345, 23), (223456789012345, 12),
                    (323456789012345, 23), (423456789012345, NULL),
                    (523456789012345, 0), (623456789012345, -1);
                INSERT INTO abonAppareilSurface VALUES
                    (1, 123456789012345, 101), (1, 123456789012345, 111),
                    (1, 223456789012345, 0), (1, 323456789012345, 101),
                    (2, 423456789012345, 101), (2, 523456789012345, 101),
                    (2, 623456789012345, 101);
            """)
            self.assertEqual(bd.execute(requete, (1,)).fetchall(), [(12,), (23,)])
            self.assertEqual(bd.execute(requete, (2,)).fetchall(), [(None,)])
            bd.execute("DELETE FROM StatutCam")
            self.assertEqual(bd.execute(requete, (1,)).fetchall(), [(None,)])


if __name__ == "__main__":
    unittest.main()
