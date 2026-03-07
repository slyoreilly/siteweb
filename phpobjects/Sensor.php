<?php
class Sensor{

    public $sensorType;
    public $name;
    public $telId;
    public $value;
    public $chrono;

    public function __construct(){
        require_once 'Database.php';
    }

    public function setData($sensorType, $telId,$value,$chrono){
        $this->sensorType=$sensorType ;
        $this->telId=$telId ;
        $this->value=$value ;
        $this->chrono=$chrono ;
    }

    public function db_dump(){
        $conn = Database::getDB();
        if (!($conn instanceof mysqli)) {
            error_log('Sensor::db_dump skipped: DB unavailable. ' . Database::$lastError);
            return false;
        }

        if (!@$conn->ping()) {
            error_log('Sensor::db_dump skipped: DB connection closed/unreachable.');
            return false;
        }

        $queryIns = "INSERT INTO SensorLog (sensorTypeId, telId,value,chrono) " .
            "VALUES ('{$this->sensorType}','{$this->telId}','{$this->value}','{$this->chrono}')";

        $retVal = $conn->query($queryIns);
        if ($retVal === false) {
            error_log("Erreur capteur: {$queryIns}\n" . $conn->error);
            return false;
        }

        return $retVal;
    }

}

?>
