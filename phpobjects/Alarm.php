<?php
class Alarm{

    public $alarmId;
    public $target;
    public $telId;
    public $operand;
    public $value;
    public $sensorType;
    public $ringing;
    public $keyValue;
    public $alarmType;

    /////////////////////////////
    //
    //  The alarm carries a message as output
    public $message;




    public function __construct(){
        require_once 'Database.php';
        require_once 'Message.php';
        $this->message=array();
        $this->message['alarmCheckSuccess']=false;
    }

    public function loadAlarmsFromTelId($telId){
        $db = Database::getDB();
        if (!($db instanceof mysqli) || !@$db->ping()) {
            error_log('Alarm::loadAlarmsFromTelId skipped: DB unavailable. ' . Database::$lastError);
            return false;
        }

        $query = "SELECT *
                FROM Alarms
                    WHERE telId='{$telId}'
                ";
        $ret = $db->query($query);
        if ($ret === false) {
            error_log($db->error . " SELECT");
            return false;
        }

        while ($rangSel = mysqli_fetch_array($ret))
        {
            $this->setData(
                $rangSel['alarmId'],
                $rangSel['target'],
                $rangSel['telId'],
                $rangSel['operand'],
                $rangSel['sensorType'],
                $rangSel['value'],
                $rangSel['ringing'],
                $rangSel['cv'],
                $rangSel['alarmClass']
            );
        }

        return true;
    }

    private function checkTimeActive($slot){
        if(strcasecmp(date("l"),$slot->start->day)==0){
            $timeStart = strtotime($slot->start->day." ".$slot->start->time);
        }else{
            $timeStart = strtotime("last ".$slot->start->day." ".$slot->start->time);
        }
        if(strcasecmp(date("l"),$slot->stop->day)==0){
            $timeStop = strtotime($slot->stop->day." ".$slot->stop->time);
        }else{
            if(strtotime("last ".$slot->stop->day." ".$slot->stop->time)>=$timeStart){
                $timeStop=strtotime("last ".$slot->stop->day." ".$slot->stop->time);
            } else{
                $timeStop=strtotime("next ".$slot->stop->day." ".$slot->stop->time);
            }
        }
        error_log(date("l"));
        error_log($slot->start->day);
        error_log(strcasecmp(date("l"),$slot->start->day)==0);

        error_log($timeStart."  ".time()."  ".$timeStop);

                if($timeStart<time()&& $timeStop>time()){
                    return true;
                } else{
                    return false;
                }

    }

    public function setData($alarmId, $target, $telId, $operand,$sensorType,$value, $ringing, $keyValue,$alarmType){
        $this->alarmId=$alarmId ;
        $this->target=json_decode($target) ;
        $this->telId=$telId ;
        $this->operand=$operand ;
        $this->sensorType=$sensorType ;
        $this->value=$value ;
        $this->ringing=$ringing ;
        $this->keyValue=json_decode($keyValue) ;
        $this->alarmType=$alarmType ;
    }

    public function checkAlarm($sensor){
        $applyAlarm=true;
        if(strcmp($this->sensorType,$sensor->sensorType)!=0){
            $applyAlarm=false;
        }
        if($applyAlarm){
            $foundActiveSlot = false;
            if(!is_null($this->keyValue)){
            if(array_key_exists ('timeActive' , $this->keyValue)){
                error_log(json_encode( $this->keyValue));
                foreach ( $this->keyValue->timeActive as $slot){
                    if($this->checkTimeActive($slot)){
                        $foundActiveSlot = true;
                        }
                    }
                }
            }
            $applyAlarm =$foundActiveSlot;
        }

        if($applyAlarm){
        if(strcmp($this->alarmType,"1")==0){
            if(strcmp($this->operand,'leq')==0){
                if(floatval($sensor->value)<=floatval($this->value)){
                    $this->message['alarmCheckSuccess']=true;
                    $this->message['alarmCheckPass']=true;
                }else{
                    $this->message['alarmCheckSuccess']=true;
                    $this->message['alarmCheckPass']=false;
                }

            }
            if(strcmp($this->operand,'geq')==0){
                if(floatval($sensor->value)>=floatval($this->value)){
                    $this->message['alarmCheckSuccess']=true;
                    $this->message['alarmCheckPass']=true;
                }else{
                    $this->message['alarmCheckSuccess']=true;
                    $this->message['alarmCheckPass']=false;
                }

            }
            if(strcmp($this->operand,'lt')==0){
                if(floatval($sensor->value)<floatval($this->value)){
                    $this->message['alarmCheckSuccess']=true;
                    $this->message['alarmCheckPass']=true;
                }else{
                    $this->message['alarmCheckSuccess']=true;
                    $this->message['alarmCheckPass']=false;
                }

            }
            if(strcmp($this->operand,'gt')==0){
                if(floatval($sensor->value)>floatval($this->value)){
                    $this->message['alarmCheckSuccess']=true;
                    $this->message['alarmCheckPass']=true;
                }else{
                    $this->message['alarmCheckSuccess']=true;
                    $this->message['alarmCheckPass']=false;
                }

            }
            if(strcmp($this->operand,'equal')==0){
                if(floatval($sensor->value)==floatval($this->value)){
                    $this->message['alarmCheckSuccess']=true;
                    $this->message['alarmCheckPass']=true;
                }else{
                    $this->message['alarmCheckSuccess']=true;
                    $this->message['alarmCheckPass']=false;
                }

            }
           // error_log("Aie... ".$this->ringing);
           // error_log(! $this->message['alarmCheckPass']);
           // error_log(! $this->message['alarmCheckPass'] && strcmp($this->ringing,"0")==0);
            if(!$this->message['alarmCheckPass'] && strcmp($this->ringing,"0")==0){
                $this->ring();
                $this->alert();
            }
            if( $this->message['alarmCheckPass'] && strcmp($this->ringing,"1")==0){
                $this->shut();
            }


            return true;
        }
        else {
            return false;
        }
    }

    }
    public function ring(){
         $queryUp = "UPDATE Alarms 
             SET ringing=1 
             WHERE alarmId= '{$this->alarmId}'";
         $db = Database::getDB();
         if (!($db instanceof mysqli) || !@$db->ping()) {
             error_log('Alarm::ring skipped: DB unavailable. ' . Database::$lastError);
             return false;
         }
         if ($db->query($queryUp) === false) {
             error_log("Erreur: ".$queryUp."\n".$db->error);
             return false;
         }
         return true;

    }
    public function shut(){
        $queryUp = "UPDATE Alarms 
        SET ringing=0
        WHERE alarmId= '{$this->alarmId}'";
        $db = Database::getDB();
        if (!($db instanceof mysqli) || !@$db->ping()) {
            error_log('Alarm::shut skipped: DB unavailable. ' . Database::$lastError);
            return false;
        }
        if ($db->query($queryUp) === false) {
            error_log("Erreur: ".$queryUp."\n".$db->error);
            return false;
        }
        return true;

    }

    public function alert(){
        $message = new Message();
        $message->setSender('SyncStats Monitoring System');
        $message->setTitle('Alarm!');
        $message->setBody('Une de vos alarmes ('.$this->alarmId.' du telephone ('.$this->telId.') est en probleme. Contactez SyncStats.');
        $message->sendEmails($this->target->email);

   }


    public function create(){
      
 //           $connPdo = new mysqli("localhost", "syncsta1_u01", "test", "syncsta1_900");
            $queryIns = "INSERT INTO SensorLog (sensorTypeId, telId,value,chrono) ".
            "VALUES ('{$this->sensorType}','{$this->telId}','{$this->value}','{$this->chrono}')";

            $db = Database::getDB();
            if (!($db instanceof mysqli) || !@$db->ping()) {
                error_log('Alarm::create skipped: DB unavailable. ' . Database::$lastError);
                return false;
            }

            $retVal = $db->query($queryIns);
            if ($retVal === false) {
                error_log("Erreur: ".$queryIns."\n".$db->error);
                return false;
            }
 
        return  $retVal ;
    }
    

}


?>

