<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);



if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}

$position = $_POST['position'];
$courriel = $_POST['courriel'];
$match = $_POST['match'];



 $to = "soreilly@syncstats.com";
 $subject = $titre;
 $body = "Expediteur: ".$_POST['courriel']. "\n\n"."Position: ".$position. "\n\n"."Match: ".$match;

     $headers = 'From: noreply@syncstats.com' . "\r\n" ;
     //'Reply-To: no reply' . "\r\n" ;
 
 $success =mail($to, $subject, $body,$headers);

if($success)
{ echo "Message envoyé à: ".$courriel."\n".$subject."\n".$body;}
else
{ echo "Echec d'envoie à: ".$courriel."\n".$subject."\n".$body;} 



if(count($arrRecept)==0){
	echo "Erreur, count(arrRecpet)=0), arrRecept = ".$arrRecept;
	
}


?>
<?php  ?>
