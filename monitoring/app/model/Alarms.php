<?php
class Alarms{

    protected $alarmList;

    public function __construct(){
//        require_once 'Database.php';
 //       require_once 'Message.php';
        $this->alarmList=[];
    }

    public function loadAlarmsFromCamId($camId){
       // $ret = Database::query
   //    echo("1");
            $ret = mysqli_query(Database::getDB(),
                "SELECT Alarms.*, SensorType.name
				FROM Alarms
                JOIN SensorType ON
                    Alarms.sensorType=SensorType.sensorTypeId
                JOIN StatutCam ON StatutCam.telId=Alarms.telId
                    WHERE camId='{$camId}'
                    ")or die(mysqli_error(Database::getDB())." SELECT");
    //   echo("2");
    //   echo(mysqli_num_rows($ret));

       while ($rangSel = mysqli_fetch_array($ret))
			{
                $mAlarm = new Alarm;
                $mAlarm->setData(
                    $rangSel['alarmId'],
                    $rangSel['target'],
                    $rangSel['telId'],
                    $rangSel['operand'],
                    $rangSel['name'],
                    $rangSel['value'],
                    $rangSel['ringing'],
                    $rangSel['cv'],
                    $rangSel['alarmClass']
                );
            array_push($this->alarmList, $mAlarm);
            
            }


    }


    public function getAlarmList(){
        return $this->alarmList;
    }
    
}


?>