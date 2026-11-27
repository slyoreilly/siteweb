import re
import sqlite3
import unittest
from pathlib import Path


class CamerasSurfaceTest(unittest.TestCase):
    def setUp(self):
        chemin = Path(__file__).resolve().parents[2] / 'api/lib/cameras_surface.php'
        self.requete = re.search(
            r'REQUETE_CAMERAS_SURFACE = "([^"]+)"',
            chemin.read_text(encoding='utf-8'),
        )[1]
        # SQLite ne connaît pas INTERVAL : seule l'expression d'horloge MySQL
        # est adaptée, sans changer les jointures ni les prédicats métier.
        self.requete = self.requete.replace(
            'UTC_TIMESTAMP() - INTERVAL 7 DAY', "'2026-11-20 12:00:00'"
        ).replace('UTC_TIMESTAMP()', "'2026-11-27 12:00:00'")
        self.bd = sqlite3.connect(':memory:')
        self.addCleanup(self.bd.close)
        self.bd.executescript('''
            CREATE TABLE TableArena (arenaId INTEGER PRIMARY KEY);
            CREATE TABLE StatutCam (
                telId INTEGER, camId INTEGER, arenaId INTEGER, dernierMaJ DATETIME
            );
            CREATE TABLE abonAppareilSurface (surfaceId INTEGER, telId INTEGER, role INTEGER);
            INSERT INTO TableArena VALUES (1), (2), (3);
        ''')

    def ajouter_camera(self, telephone, camera, surface=1, connexion='2026-11-26 12:00:00'):
        self.bd.execute('INSERT INTO StatutCam VALUES (?, ?, ?, ?)',
                        (telephone, camera, surface, connexion))

    def associer(self, telephone, surface=1, role=101):
        self.bd.execute('INSERT INTO abonAppareilSurface VALUES (?, ?, ?)',
                        (surface, telephone, role))

    def consulter(self, surface=1):
        return self.bd.execute(self.requete, (surface,)).fetchall()

    def test_plusieurs_cameras_et_deux_mecanismes_legacy_sans_inventaire_telephones(self):
        self.ajouter_camera(123456789012345, 24)
        self.ajouter_camera(223456789012345, 12)
        self.ajouter_camera(323456789012345, 35, surface=2)
        self.associer(323456789012345)
        self.associer(123456789012345)
        self.associer(123456789012345, role=111)
        self.ajouter_camera(423456789012345, 12)
        self.ajouter_camera(523456789012345, 99, surface=3)
        self.assertEqual(self.consulter(), [(12,), (24,), (35,)])
        self.assertEqual(self.consulter(2), [(35,)])
        self.assertEqual(self.consulter(3), [(99,)])

    def test_seuil_inclusif_sept_jours_et_anciennes_associations_conservees(self):
        for telephone, connexion in [
            (1, '2026-11-27 12:00:00'),
            (2, '2026-11-20 12:00:00'),
            (3, '2026-11-20 11:59:59'),
            (4, '2026-11-19 12:00:00'),
            (5, '2026-10-28 12:00:00'),
            (6, '2026-10-28 11:59:59'),
            (7, None),
            (8, '2026-11-27 12:00:01'),
        ]:
            self.ajouter_camera(telephone, telephone, connexion=connexion)
            self.associer(telephone)
        avant = self.bd.execute('SELECT * FROM abonAppareilSurface').fetchall()
        self.assertEqual(self.consulter(), [(1,), (2,)])
        self.assertEqual(self.bd.execute('SELECT * FROM abonAppareilSurface').fetchall(), avant)

    def test_roles_des_abonnements_et_absence_de_statut(self):
        for telephone, role in [(1, 0), (2, -1), (3, 100), (4, 111),
                                (5, 112), (6, 1), (7, 99), (8, None)]:
            self.ajouter_camera(telephone, telephone, surface=2)
            self.associer(telephone, role=role)
        self.associer(999)
        self.assertEqual(self.consulter(), [(1,), (2,), (3,), (4,), (5,)])
        # Le lien déclaré reste indépendant des rôles d'abonnement.
        self.assertEqual(self.consulter(2), [(i,) for i in range(1, 9)])

    def test_identifiants_invalides_surfaces_vides_ou_inconnues(self):
        for telephone, camera in [(1, None), (2, 0), (3, -1), (0, 90)]:
            self.ajouter_camera(telephone, camera)
            self.associer(telephone)
        self.assertEqual(self.consulter(), [(None,)])
        self.assertEqual(self.consulter(3), [(None,)])
        self.assertEqual(self.consulter(999), [])

    def test_retrait_abonnement_et_connexion_ulterieure_sans_reassociation(self):
        self.ajouter_camera(1, 24, surface=2, connexion='2026-10-01 12:00:00')
        self.associer(1)
        self.assertEqual(self.consulter(), [(None,)])
        self.bd.execute("UPDATE StatutCam SET dernierMaJ = '2026-11-27 12:00:00'")
        self.assertEqual(self.consulter(), [(24,)])
        self.bd.execute('DELETE FROM abonAppareilSurface WHERE surfaceId = 1')
        self.assertEqual(self.consulter(), [(None,)])
        self.assertEqual(self.consulter(2), [(24,)])


if __name__ == '__main__':
    unittest.main()
