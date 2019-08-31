<?php
class Message{

    public $recipients=array();
    public $sender;
    public $title;
    public $body;

    

    public function __construct(){
        require_once 'Database.php';

    }

    public function setData($recipients, $sender,$title,$body){
        $this->recipients=$recipients ;
        $this->sender=$sender ;
        $this->title=$title ;
        $this->body=$body ;
    }

    public function setBody($body){
        $this->body=$body ;
    }
    public function setTitle($body){
        $this->title=$title ;
    }
    public function setSender($sender){
        $this->sender=$sender ;
    }


    public function db_dump(){
      
 //           $connPdo = new mysqli("localhost", "syncsta1_u01", "test", "syncsta1_900");
            $queryIns = "INSERT INTO SensorLog (sensorTypeId, telId,value,chrono) ".
            "VALUES ('{$this->sensorType}','{$this->telId}','{$this->value}','{$this->chrono}')";

            $retVal =  mysqli_query(Database::getDB(),$queryIns) or die("Erreur: ".$queryIns."\n".mysqli_error(Database::getDB()));
 
        return  $retVal ;
    }

    public function sendEmails($emails){
        foreach($emails as $email){

        //$reponse="\n\n"."Vous ne pouvez pas rpondre à ce courriel".$retour."&mode=reply";


          $subject = $this->titre;
      //$body = "Expediteur: ".$this->sender. "\n\n"."Message: ".$corps.$reponse;
         $headers = 'From: noreply@syncstats.com' . "\r\n" ;
     //'Reply-To: no reply' . "\r\n" ;
 
         $success =mail($email, $subject, $this->body,$headers);

    }




    }

}


?>