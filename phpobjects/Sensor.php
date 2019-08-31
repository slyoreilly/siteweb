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
      
 //           $connPdo = new mysqli("localhost", "syncsta1_u01", "test", "syncsta1_900");
            $queryIns = "INSERT INTO SensorLog (sensorTypeId, telId,value,chrono) ".
            "VALUES ('{$this->sensorType}','{$this->telId}','{$this->value}','{$this->chrono}')";

            $retVal =  mysqli_query(Database::getDB(),$queryIns) or die("Erreur: ".$queryIns."\n".mysqli_error(Database::getDB()));
 
        return  $retVal ;
    }

}


?>