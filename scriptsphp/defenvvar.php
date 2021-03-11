<?php 

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


} else if($workEnv=="development"){
    $db_host="localhost";
    $db_user="syncsta1_u01";
    $db_pwd="test";
    $image_loc="devclientfiles/";
    $fileserver_loc_rep="/var/www/html/";
    $fileserver_baseurl="https://syncstats.ddns.net/";
    
    $database = 'syncsta1_901';


} else{
    $db_host="localhost";
    $db_user="syncsta1_u01";
    $db_pwd="test";
    $image_loc="devclientfiles/";
    $fileserver_loc_rep="/var/www/html/";
    $fileserver_baseurl="https://syncstats.ddns.net/";
    
    
    $database = 'syncsta1_901';

}

?>