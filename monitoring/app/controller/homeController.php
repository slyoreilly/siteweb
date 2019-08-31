<?php
class homeController extends Controller{

    public function index($id='',$name=''){
 //       echo 'ID is :'.$id.',  le nom est:'.$name;
//        echo '<h1>Monitoring</h1>';
$this->view('home/index',[
    'name' =>$name,
    'id'=>$id
]);
$this->view->render();

    }
    public function aboutus(){
        echo "<h1>C'est nous!</h1>";

//        echo '<h1>Monitoring</h1>';
$this->view('home/aboutUs',[
]);
$this->view->render();

    }


}




?>