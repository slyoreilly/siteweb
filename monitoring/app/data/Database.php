<?php
class Database
{
    private static $init = FALSE;
    public static $connPdo = null;
    public static $lastError = '';

    /**
     * Returns a live mysqli connection or null if unavailable.
     * Reconnects automatically if previous handle was closed.
     */
    public static function getDB(){
        self::initialize();
        return (self::$connPdo instanceof mysqli) ? self::$connPdo : null;
    }

    public function query($query_string){
        $db = self::getDB();
        if (!($db instanceof mysqli)) {
            return false;
        }
        return mysqli_query($db, $query_string);
    }

    public static function initialize()
    {
        require '../scriptsphp/defenvvar.php';

        if (self::$connPdo instanceof mysqli) {
            if (@self::$connPdo->ping()) {
                self::$init = TRUE;
                return;
            }
            self::$connPdo = null;
            self::$init = FALSE;
        }

        self::$lastError = '';
        $conn = @new mysqli($db_host, $db_user, $db_pwd, $database);
        if ($conn->connect_errno) {
            self::$lastError = $conn->connect_error;
            self::$connPdo = null;
            self::$init = FALSE;
            return;
        }

        self::$connPdo = $conn;
        self::$init = TRUE;
    }
}

?>
