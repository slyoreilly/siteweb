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
        if (self::$init===TRUE)return;
        self::$init = TRUE;
        self::$connPdo = new mysqli("localhost", "syncsta1_u01", "test", "syncsta1_900");
    }
}

?>