<?php
class alarmController extends Controller
{

    
    public function index($camId='',$name=''){
        require_once MODEL.'Alarm.php';
        require_once MODEL.'Message.php';

        $this->model('Alarms');
        $this->model->loadAlarmsFromCamId($camId);
  //  $this->model->getAlarmList();
//        echo 'ID is :'.$id.',  le nom est:'.$name;
//        echo '<h1>Monitoring</h1>';
        $this->view('alarm/index',[
            'name' =>$name,
            'alarmList'=>$this->model->getAlarmList()
        ]);
        
        $this->view->render();

    }


    public function creer($camId='',$params=''){
        //require_once MODEL.'Appareil.php';
        $this->model('Appareil');
        
 $this->model->loadAppareilDeCamId($camId);
 $params =$this->model;
//echo('Doce: '.json_encode($params));
        $this->view('alarm/creer',[
        'name' =>$name,
        'params'=>$params
    ]);
    $this->view->render();

    }
}

?>