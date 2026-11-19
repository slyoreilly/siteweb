"""Tests des vraies routes PHP dans une copie isolée, sans secrets ni SQL.

Exécution : python test/unit/legacy_token_test.py [chemin-vers-php]
"""
import json
import os
from pathlib import Path
import shutil
import subprocess
import sys
import tempfile
import unittest

RACINE = Path(__file__).resolve().parents[2]
PHP = sys.argv.pop(1) if len(sys.argv) > 1 else shutil.which('php')


@unittest.skipUnless(PHP, 'PHP CLI requis')
class AuthentificationLegacy(unittest.TestCase):
    def test_configuration_locale_effective(self):
        if not (RACINE / '.env.php').is_file():
            self.skipTest('Configuration privée locale absente')
        for route in ('getCamerasParSurface.php', 'getMatchActifParSurface.php'):
            with self.subTest(route=route):
                env = os.environ.copy()
                env.pop('LEGACY_MATCH_ACTIF_TOKEN', None)
                env['WORK_ENV'] = 'development'
                code = (
                    "require '" + (RACINE / 'scriptsphp/chargeSecrets.php').as_posix() + "';"
                    "$jeton=getenv('LEGACY_MATCH_ACTIF_TOKEN');"
                    "if (!is_string($jeton) || trim($jeton)==='') { exit(2); }"
                    "$_SERVER['REQUEST_METHOD']='GET';"
                    "$_SERVER['HTTP_AUTHORIZATION']='Bearer '.$jeton;"
                    "$_GET=['arenaId'=>'invalide'];"
                    "ob_start(); require '" + (RACINE / 'api' / route).as_posix() + "';"
                    "ob_end_clean(); echo http_response_code();")
                resultat = subprocess.run([PHP, '-n', '-r', code], env=env,
                                          capture_output=True, text=True)
                self.assertEqual(0, resultat.returncode, 'Chargement privé en échec')
                self.assertEqual('400', resultat.stdout, 'Le jeton local doit authentifier la route')

    def test_chargement_et_routes(self):
        with tempfile.TemporaryDirectory(prefix='legacy-jeton-') as temporaire:
            copie = Path(temporaire)
            shutil.copytree(RACINE / 'api', copie / 'api')
            (copie / 'scriptsphp').mkdir()
            shutil.copy(RACINE / 'scriptsphp/chargeSecrets.php', copie / 'scriptsphp')
            # Toute ouverture SQL pendant un refus fait échouer le test.
            (copie / 'scriptsphp/defenvvar.php').write_text(
                '<?php throw new RuntimeException("SQL interdit dans ce test");')
            cas = [
                (None, None, '', 503),
                (None, '   ', 'Bearer incorrect', 503),
                (None, 'fictif-fichier', '', 401),
                (None, 'fictif-fichier', 'Bearer incorrect', 401),
                (None, 'fictif-fichier', 'Basic fictif-fichier', 401),
                (None, 'fictif-fichier', 'Bearer fictif-fichier', 400),
                ('fictif-serveur', 'fictif-fichier', 'Bearer fictif-fichier', 401),
                ('fictif-serveur', 'fictif-fichier', 'Bearer fictif-serveur', 400),
                ('', 'fictif-fichier', 'Bearer fictif-fichier', 503),
                ('fictif-serveur', None, 'Bearer fictif-serveur', 400),
            ]
            for environnement in ('development', 'production'):
                for format_secret in ('putenv', 'tableau'):
                    for serveur, fichier, autorisation, attendu in cas:
                        prive = copie / '.env.php'
                        prive.unlink(missing_ok=True)
                        if fichier is not None:
                            contenu = (
                                "putenv('LEGACY_MATCH_ACTIF_TOKEN=" + fichier + "');"
                                if format_secret == 'putenv' else
                                "return ['LEGACY_MATCH_ACTIF_TOKEN' => '" + fichier + "'];")
                            prive.write_text('<?php ' + contenu)
                        for route in ('getCamerasParSurface.php', 'getMatchActifParSurface.php'):
                            with self.subTest(env=environnement, format=format_secret,
                                              route=route, cas=(serveur, fichier, autorisation)):
                                env = os.environ.copy()
                                env.pop('LEGACY_MATCH_ACTIF_TOKEN', None)
                                env['WORK_ENV'] = environnement
                                if serveur is not None:
                                    env['LEGACY_MATCH_ACTIF_TOKEN'] = serveur
                                chemin = (copie / 'api' / route).as_posix()
                                code = (
                                    "$_SERVER['REQUEST_METHOD']='GET';"
                                    "$_SERVER['HTTP_AUTHORIZATION']=" + json.dumps(autorisation) + ';'
                                    "$_GET=['arenaId'=>'invalide'];"
                                    "ob_start(); require '" + chemin + "';"
                                    "$corps=ob_get_clean(); echo json_encode([http_response_code(),json_decode($corps,true)]);")
                                resultat = subprocess.run([PHP, '-n', '-r', code], env=env,
                                                          capture_output=True, text=True, check=True)
                                statut, corps = json.loads(resultat.stdout)
                                self.assertEqual(attendu, statut)
                                self.assertEqual({503: 'service_non_configure', 401: 'non_autorise',
                                                  400: 'arena_id_invalide'}[attendu], corps['etat'])


if __name__ == '__main__':
    unittest.main()
