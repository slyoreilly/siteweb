<?php
class Database
{
    /** TRUE if static variables have been initialized. FALSE otherwise
    */
    private static $init = FALSE;
    /** The mysqli connection object
    */
    public static $connPdo;
    /** initializes the static class variables. Only runs initialization once.
    * does not return anything.
    */
public static function getDB(){
    Database::initialize();
    return self::$connPdo;
}

    public static function initialize()
    {
        require '../scriptsphp/defenvvar.php';
        if (self::$init===TRUE)return;
        self::$init = TRUE;
        self::$connPdo = mysqli($db_host, $db_user, $db_pwd, $database);
    }
}

?>