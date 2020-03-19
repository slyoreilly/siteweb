<?php 

$workEnv = getenv('WORK_ENV');
if($workEnv=="production"){
    $db_host="localhost";
    $db_user="syncsta1_u01";
    $db_pwd="test";
    
    $database = 'syncsta1_900';


} else if($workEnv=="development"){
    $db_host="localhost";
    $db_user="syncsta1_u01";
    $db_pwd="test";
    
    $database = 'syncsta1_901';


} else{
    $db_host="localhost";
    $db_user="syncsta1_u01";
    $db_pwd="test";
    
    $database = 'syncsta1_901';

}

?>