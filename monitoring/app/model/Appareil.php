<?php
class Appareil{

    public $telId;
    public $camId;
    public $userId;
    public $dernierMaJ;
    public $batterie;
    public $memoire;
    public $temperature;
    public $arenaId;
    public $version;
    public $codeEtat;


    public function __construct(){
//        require_once 'Database.php';
 //       require_once 'Message.php';
    }

    public function loadAppareilDeCamId($camId){
      //  echo ('camId:'.$camId);
             $ret = mysqli_query(Database::getDB(),
                 "SELECT StatutCam.*
                 From StatutCam
                     WHERE camId='{$camId}'
                     ")or die(mysqli_error(Database::getDB())." SELECT");
 
        while ($rangSel = mysqli_fetch_array($ret))
             {
                $this->telId=$rangSel['telId'] ;
                $this->camId=$rangSel['camId'];
                $this->userId=$rangSel['userId'];
                $this->dernierMaJ=$rangSel['dernierMaJ'];
                $this->batterie=$rangSel['batterie'];
                $this->memoire=$rangSel['memoire'];
                $this->temperature=$rangSel['temperature'];
                $this->arenaId=$rangSel['arenaId'];
                $this->version=$rangSel['version'];
                $this->codeEtat=$rangSel['codeEtat'];
             }
//             echo ('camId2:'.json_encode($this));

 
     }
     public function setData($telId, $camId, $userId, $dernierMaJ, $batterie, $memoire, $temperature,$arenaId, $version, $codeEtat){
        $this->telId=$telId ;
        $this->camId=$camId;
        $this->userId=$userId;
        $this->dernierMaJ=$dernierMaJ;
        $this->batterie=$batterie;
        $this->memoire=$memoire;
        $this->temperature=$temperature;
        $this->arenaId=$arenaId;
        $this->version=$version;
        $this->codeEtat=$codeEtat;
     }
 
    public function create(){
      
 //           $connPdo = new mysqli("localhost", "syncsta1_u01", "test", "syncsta1_900");
            $queryIns = "INSERT INTO SensorLog (sensorTypeId, telId,value,chrono) ".
            "VALUES ('{$this->sensorType}','{$this->telId}','{$this->value}','{$this->chrono}')";

            $retVal =  mysqli_query(Database::getDB(),$queryIns) or die("Erreur: ".$queryIns."\n".mysqli_error(Database::getDB()));
 
        return  $retVal ;
    }


    
}


?>