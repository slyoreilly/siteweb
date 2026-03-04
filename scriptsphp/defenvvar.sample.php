<?php

global $conn;

$workEnv = getenv('WORK_ENV') ?: 'development';

$db_user = 'CHANGE_DB_USER';
$db_pwd = 'CHANGE_DB_PASSWORD';

if ($workEnv === 'production') {
    $db_host = 'localhost';
    $database = 'CHANGE_DB_NAME_PROD';
    $db_port = null;

    $image_loc = 'clientfiles/';
    $fileserver_loc_rep = '/home/';
    $fileserver_baseurl = 'https://syncstats.ca/';
} else {
    $db_host = ($workEnv === 'development') ? 'mysql-server' : 'localhost';
    $database = 'CHANGE_DB_NAME_DEV';
    $db_port = 3306;

    $image_loc = 'devclientfiles/';
    $fileserver_loc_rep = '/var/www/html/';
    $fileserver_baseurl = 'https://syncstats.ddns.net/';
}

// Paramètres Dolibarr nécessaires au script de facturation.
if (!getenv('DOLIBARR_BASE_URL')) {
    putenv('DOLIBARR_BASE_URL=https://dolibarr.example.com');
}
if (!getenv('DOLIBARR_API_KEY')) {
    putenv('DOLIBARR_API_KEY=CHANGE_DOLIBARR_API_KEY');
}
if (!getenv('DOLIBARR_PAGE_SIZE')) {
    putenv('DOLIBARR_PAGE_SIZE=100');
}

// Création ou recréation de la connexion si nécessaire
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
