<?php

global $conn;

$workEnv = getenv('WORK_ENV');
if($workEnv=="production"){
    $db_host="localhost";
    $db_user="syncsta1_u01";
    $db_pwd="test";
    $image_loc="clientfiles/";
    $fileserver_loc_rep="/home/";
    $fileserver_baseurl="https://syncstats.ca/";
    $db_pwd="test";
    $database = 'syncsta1_900';
    $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
} else if($workEnv=="development"){
    $db_host="mysql-server";
    $db_user="syncsta1_u01";
    $db_pwd="test";
    $db_port="3306";
    $image_loc="devclientfiles/";
    $fileserver_loc_rep="/var/www/html/";
    $fileserver_baseurl="https://syncstats.ddns.net/";
    $database = 'syncsta1_901';
    $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database, $db_port);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
} else{
    $db_host="localhost";
    $db_user="syncsta1_u01";
    $db_pwd="test";
    $image_loc="devclientfiles/";
    $fileserver_loc_rep="/var/www/html/";
    $fileserver_baseurl="https://syncstats.ddns.net/";
    $db_port=3306;

    $database = 'syncsta1_901';
    $conn = mysqli_connect($db_host, $db_user, $db_pwd, $database, $db_port);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, "utf8");
?>
