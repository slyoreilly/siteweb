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
        global $conn;

        // Reuse global connection from defenvvar.php when already alive.
        if ($conn instanceof mysqli && @$conn->ping()) {
            self::$connPdo = $conn;
            self::$init = TRUE;
            return;
        }

        $defenvPath = __DIR__ . '/../scriptsphp/defenvvar.php';
        if (!is_file($defenvPath)) {
            throw new RuntimeException('Missing defenvvar.php: ' . $defenvPath);
        }

        // Avoid re-running side effects in defenvvar.php.
        require_once $defenvPath;

        if ($conn instanceof mysqli && @$conn->ping()) {
            self::$connPdo = $conn;
            self::$init = TRUE;
            return;
        }

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
