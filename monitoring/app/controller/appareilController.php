<?php
class appareilController extends Controller
{

    
    public function index($key='',$params=''){
        require_once MODEL.'Appareil.php';
   //     echo('---1----');
        $this->model('Appareils');
  //  echo('---2----');
    $this->model->loadAppareils();
   // echo('---3----');
    $appList=$this->model->getAppareilList();
//echo('---4----');
    if($key=='user'&&$params!=''){
        foreach($appList as $elementApp => $app) {
            foreach($app as $valueKey => $value) {
                if($valueKey == 'userId' && $value != $params){
                    unset($appList[$elementApp]);
                } 
            }
        }
    }
    $this->view('appareil/index',[
        'name' =>$name,
        'appareilList'=>$appList
    ]);
    $this->view->render();

    }


    public function voir($camId='',$params=''){
        //require_once MODEL.'Appareil.php';
        $this->model('Appareil');
        
 $this->model->loadAppareilDeCamId($camId);
 $params =$this->model;
//echo('Doce: '.json_encode($params));
        $this->view('appareil/voir',[
        'name' =>$name,
        'params'=>$params
    ]);
    $this->view->render();

    }


}

?>