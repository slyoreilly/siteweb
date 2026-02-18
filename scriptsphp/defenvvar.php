<?php

global $conn;

// Toujours définir les variables d’environnement
$workEnv = getenv('WORK_ENV') ?: 'development';

$db_user = "syncsta1_u01";
$db_pwd  = "test";

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


// 🔥 Connexion UNIQUEMENT si non existante
if (!isset($conn) || !($conn instanceof mysqli)) {

    if ($db_port !== null) {
        $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database, $db_port);
    } else {
        $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
    }

    if (!$conn) {
        error_log("DB Connection failed: " . mysqli_connect_error());
        http_response_code(500);
        exit;
    }

    mysqli_set_charset($conn, "utf8");

    // Fermeture automatique à la fin du script
    register_shutdown_function(function() use (&$conn) {
        if ($conn instanceof mysqli) {
            mysqli_close($conn);
        }
    });
}
?>

