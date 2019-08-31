<?php
class Appareils{

    protected $appareilList;

    public function __construct(){
//        require_once 'Database.php';
 //       require_once 'Message.php';
        $this->appareilList=[];
    }

    public function loadAppareils(){
       // $ret = Database::query
//       echo("1");
            $ret = mysqli_query(Database::getDB(),
                "SELECT StatutCam.*
                From StatutCam
                    WHERE 1
                    ")or die(mysqli_error(Database::getDB())." SELECT");
  //     echo("2");
   //    echo(mysqli_num_rows($ret));

       while ($rangSel = mysqli_fetch_array($ret))
			{
                $mApp = new Appareil;
                $mApp->setData(
                    $rangSel['telId'],
                    $rangSel['camId'],
                    $rangSel['userId'],
                    $rangSel['dernierMaJ'],
                $rangSel['batterie'],
               $rangSel['memoire'],
                $rangSel['temperature'],
                $rangSel['arenaId'],
                $rangSel['version'],
                $rangSel['codeEtat']
                );
            array_push($this->appareilList, $mApp);
            
            }


    }


    public function getAppareilList(){
        return $this->appareilList;
    }
    
}


?>