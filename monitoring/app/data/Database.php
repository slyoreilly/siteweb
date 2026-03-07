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
        global $conn;

        if ($conn instanceof mysqli && @$conn->ping()) {
            self::$connPdo = $conn;
            self::$init = TRUE;
            return;
        }

        $defenvPath = __DIR__ . '/../../../scriptsphp/defenvvar.php';
        if (!is_file($defenvPath)) {
            throw new RuntimeException('Missing defenvvar.php: ' . $defenvPath);
        }

        require_once $defenvPath;

        if ($conn instanceof mysqli && @$conn->ping()) {
            self::$connPdo = $conn;
            self::$init = TRUE;
            return;
        }

        if (self::$connPdo instanceof mysqli) {
            if (@self::$connPdo->ping()) {
                self::$init = TRUE;
                return;
            }
            self::$connPdo = null;
            self::$init = FALSE;
        }

        self::$lastError = '';
        $localConn = @new mysqli($db_host, $db_user, $db_pwd, $database);
        if ($localConn->connect_errno) {
            self::$lastError = $localConn->connect_error;
            self::$connPdo = null;
            self::$init = FALSE;
            return;
        }

        self::$connPdo = $localConn;
        $conn = $localConn;
        self::$init = TRUE;
    }
}

?>
