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

    public static function initialize()
    {
        require '../scriptsphp/defenvvar.php';

        // Existing handle is usable: keep it.
        if (self::$connPdo instanceof mysqli) {
            if (@self::$connPdo->ping()) {
                self::$init = TRUE;
                return;
            }

            // Stale/closed handle: discard and reconnect.
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
