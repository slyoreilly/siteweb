<?php

global $conn;

$workEnv = getenv('WORK_ENV') ?: 'development';

$db_user = "syncsta1_u01";
$db_pwd  = "<MOT DE PASSE>";

if ($workEnv == "production") {

    $db_host = "localhost";
    $database = 'syncsta1_900';

    $image_loc = "clientfiles/";
    $fileserver_loc_rep = "/home/";
    $fileserver_baseurl = "https://syncstats.ca/";

    $db_port = null;

} else {

    $db_host = ($workEnv == "development") ? "mysql-server" : "localhost";
    $database = 'syncsta1_901';
    $db_port = 3306;

    $image_loc = "devclientfiles/";
    $fileserver_loc_rep = "/var/www/html/";
    $fileserver_baseurl = "https://syncstats.ddns.net/";
}

// Paramètres Dolibarr nécessaires au script de facturation.
if (!getenv('DOLIBARR_BASE_URL')) {
    putenv('DOLIBARR_BASE_URL=https://dolibarr.example.com');
}
if (!getenv('DOLIBARR_API_KEY')) {
    putenv('DOLIBARR_API_KEY=CHANGE');
}
if (!getenv('DOLIBARR_PAGE_SIZE')) {
    putenv('DOLIBARR_PAGE_SIZE=100');
}

// Création ou recréation de la connexion si nécessaire
// Parametres sync inbound/ack avec valeurs par defaut selon l'environnement.
// Ces valeurs sont surchargees automatiquement si les variables d'environnement
// existent deja (getenv prioritaire).
$syncInboundTokenDefault = ($workEnv === 'production') ? 'change-me-inbound-prod' : 'change-me-inbound-dev';
$syncAckUrlDefault = ($workEnv === 'production')
    ? 'https://syncstats.live/api/sync/ack'
    : 'https://localhost:44324/api/sync/ack';
$syncAckTokenDefault = ($workEnv === 'production') ? 'change-me-ack-prod' : 'change-me-ack-dev';

if (!getenv('SYNC_INBOUND_TOKEN')) {
    putenv('SYNC_INBOUND_TOKEN=' . $syncInboundTokenDefault);
}
if (!getenv('SYNC_ACK_URL')) {
    putenv('SYNC_ACK_URL=' . $syncAckUrlDefault);
}
if (!getenv('SYNC_ACK_TOKEN')) {
    putenv('SYNC_ACK_TOKEN=' . $syncAckTokenDefault);
}
if (!getenv('SYNC_ACK_HEADER')) {
    putenv('SYNC_ACK_HEADER=X-Sync-Token');
}
if (!getenv('SYNC_ACK_TIMEOUT_SECONDS')) {
    putenv('SYNC_ACK_TIMEOUT_SECONDS=8');
}
if (!getenv('SYNC_ACK_MAX_ATTEMPTS')) {
    putenv('SYNC_ACK_MAX_ATTEMPTS=6');
}
if (!isset($conn) || !($conn instanceof mysqli)) {
    if ($db_port !== null) {
        $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database, $db_port);
    } else {
        $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
    }

    if (!$conn) {
        error_log('DB Connection failed: ' . mysqli_connect_error());
        http_response_code(500);
        exit;
    }

    mysqli_set_charset($conn, 'utf8');
} else {
    if (!@mysqli_query($conn, 'SELECT 1')) {
        if ($db_port !== null) {
            $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database, $db_port);
        } else {
            $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
        }

        if (!$conn) {
            error_log('DB Reconnection failed: ' . mysqli_connect_error());
            http_response_code(500);
            exit;
        }

        mysqli_set_charset($conn, 'utf8');
    }
}
