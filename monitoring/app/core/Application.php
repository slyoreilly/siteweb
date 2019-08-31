<?php
class Application{
    protected $controller = 'homeController';
    protected $action ='action';
    protected $params = [];

    public function __construct(){
 //       echo "1";
      $this->prepareURL();
 //     echo CONTROLLER.$this->controller.'.php';
      if(file_exists(CONTROLLER.$this->controller.'.php')){
 //       echo ("0");
        $this->controller = new $this->controller;
//        echo ("2");
          if(method_exists($this->controller, $this->action)){
//            echo ("1");
            call_user_func_array([$this->controller, $this->action],$this->params);              
          }
//          $this->controller->index();

      }
      //echo $this->controller .'<br/>'. $this->action.'<br/>'. $this->params ;
    }

    protected function prepareURL(){
     //   echo "0";
        $request = trim($_SERVER['REQUEST_URI'],'/');
        if(!empty($request)){
            $url=explode('/',$request);
            $this->controller=isset($url[1])?$url[1].'Controller':'homeController';
            $this->action=isset($url[2])?$url[2]:'index';
            unset($url[0],$url[1],$url[2]);
            $this->params=!empty($url)?array_values($url):[];
//            var_dump($url);
        }
    }

}



?>