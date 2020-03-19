<?php
require '../scriptsphp/defenvvar.php';
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

$fic=$_POST['fic'];
$videoId=$_POST['videoId'];


 $to = "info@syncstats.com";
 $subject = "Restauration de video";
 $body = "Fichier: ".$fic. "\n\n"."videoId: ".$videoId;

     $headers = 'From: noreply@syncstats.com' . "\r\n" ;
     //'Reply-To: no reply' . "\r\n" ;
 
 $success =mail($to, $subject, $body,$headers);

if($success)
{ echo json_encode(array("succes"=>"1","time"=>time()));}
else
{ echo json_encode(array("succes"=>"0","time"=>time()));}


?>

