<?php

// Réservé aux trois syncPushTempsCam : aucun changement de la couche SQL globale.
final class AttenteLongPollCam
{
    private $debut;
    private $echeance;
    private $configuration;
    private $echecs = 0;
    private $indisponible = false;
    private $initialisationEnCours = false;

    public function __construct($timeoutMillisecondes)
    {
        $timeout = is_numeric($timeoutMillisecondes) ? (float)$timeoutMillisecondes : 0;
        // Historique : timeout=60000 -> 30 s, timeout<=30000 -> une pause de 5 s.
        // Les connexions et les reprises consomment désormais ce même budget.
        $budget = min(30, max(5, ceil(($timeout - 30000) / 5000) * 5));
        $this->debut = hrtime(true) / 1000000000;
        $this->echeance = $this->debut + $budget;
    }

    private function tempsRestant()
    {
        return max(0, $this->echeance - hrtime(true) / 1000000000);
    }

    public function initialiser(&$connexion, $reponseInitiale)
    {
        // Le fichier privé reste l'autorité de configuration et n'est inclus qu'une fois.
        // Son éventuel exit initial doit aussi produire un JSON compatible, sans double réponse.
        $this->initialisationEnCours = true;
        register_shutdown_function(function () use (&$connexion, $reponseInitiale) {
            if ($this->initialisationEnCours) {
                $this->indisponible = true;
                $this->journaliser('initialisation_interrompue');
                $this->fermer($connexion);
                $this->repondre($reponseInitiale);
            }
        });

        try {
            @require __DIR__ . '/../scriptsphp/defenvvar.php';
            // defenvvar.php expose $conn globalement ; ses paramètres sont locaux à l'inclusion.
            global $conn;
            $connexion = $conn;
            if (!($connexion instanceof mysqli)) {
                throw new RuntimeException('Configuration SQL indisponible');
            }
            if (!isset($db_host, $db_user, $db_pwd, $database)) {
                throw new RuntimeException('Paramètres SQL historiques indisponibles');
            }
            $this->configuration = array(
                'hote' => $db_host,
                'utilisateur' => $db_user,
                'motDePasse' => $db_pwd,
                'base' => $database,
                'port' => isset($db_port) ? (int)$db_port : (int)ini_get('mysqli.default_port'),
                'charset' => mysqli_character_set_name($connexion)
            );
            return true;
        } catch (Throwable $erreur) {
            $this->indisponible = true;
            $this->journaliser('initialisation_echouee', (int)$erreur->getCode());
            $this->fermer($connexion);
            return false;
        } finally {
            $this->initialisationEnCours = false;
        }
    }

    public function ouvrir(&$connexion)
    {
        if ($connexion instanceof mysqli) {
            return true;
        }
        while ($this->echecs < 3 && $this->tempsRestant() >= 3) {
            $nouvelleConnexion = null;
            $codeErreur = 0;
            try {
                $nouvelleConnexion = @mysqli_init();
                if (!$nouvelleConnexion
                    || !@mysqli_options($nouvelleConnexion, MYSQLI_OPT_CONNECT_TIMEOUT, 2)
                    || !@mysqli_options($nouvelleConnexion, MYSQLI_OPT_READ_TIMEOUT,
                        max(1, (int)floor($this->tempsRestant()) - 2))) {
                    throw new RuntimeException('Initialisation de connexion impossible');
                }
                $configuration = $this->configuration;
                if (!@mysqli_real_connect($nouvelleConnexion, $configuration['hote'],
                    $configuration['utilisateur'], $configuration['motDePasse'],
                    $configuration['base'], $configuration['port'])
                    || !@mysqli_set_charset($nouvelleConnexion, $configuration['charset'])) {
                    throw new RuntimeException('Réouverture de connexion impossible',
                        (int)$nouvelleConnexion->connect_errno);
                }
                $connexion = $nouvelleConnexion;
                $reprise = $this->indisponible;
                $this->indisponible = false;
                if ($reprise) {
                    $this->journaliser('reconnexion_retablie');
                }
                return true;
            } catch (Throwable $erreur) {
                $codeErreur = (int)$erreur->getCode();
            }
            $this->fermer($nouvelleConnexion);
            $connexion = null;
            $this->indisponible = true;
            $this->echecs++;
            $this->journaliser('reconnexion_echouee', $codeErreur);
            // Au plus trois échecs par requête HTTP, avec 2 s puis 5 s sans connexion.
            $pause = $this->echecs === 1 ? 2 : 5;
            if ($this->echecs >= 3 || $this->tempsRestant() < $pause + 3) {
                break;
            }
            usleep($pause * 1000000);
        }
        return false;
    }

    public function fermer(&$connexion)
    {
        if ($connexion instanceof mysqli) {
            try {
                @mysqli_close($connexion);
            } catch (Throwable $erreur) {
                $this->journaliser('fermeture_echouee', (int)$erreur->getCode());
            }
        }
        $connexion = null;
    }

    public function attendre()
    {
        $pause = min(5, $this->tempsRestant());
        if ($pause > 0) {
            usleep((int)floor($pause * 1000000));
        }
        // Réserver jusqu'à 2 s pour connecter et 1 s de marge ; pas de dernier essai précipité.
        return $this->tempsRestant() >= 3;
    }

    public function journaliser($motif, $code = 0)
    {
        $script = basename(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'syncPushTempsCam');
        error_log('[long_poll_cam] script=' . $script . ' motif=' . $motif
            . ' echecs=' . $this->echecs . ' code=' . (int)$code
            . ' duree_ms=' . (int)round((hrtime(true) / 1000000000 - $this->debut) * 1000));
    }

    public function repondre($reponse)
    {
        http_response_code($this->indisponible ? 503 : 200);
        header('Content-Type: application/json; charset=utf-8');
        if ($this->indisponible) {
            header('Retry-After: 5');
        }
        $reponse['heure'] = time();
        echo json_encode($reponse);
    }
}
