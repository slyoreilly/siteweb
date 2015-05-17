<?php


 // just so we know it is broken
 error_reporting(E_ALL);

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
$tableFichier = 'TableFichier';


 // some basic sanity checks
 if(isset($_GET['ficId']) && is_numeric($_GET['ficId'])) {
     //connect to the db
     $link = mysql_connect($db_host, $db_user, $db_pwd)
     or die("Could not connect: " . mysql_error());

     // select our database
     mysql_select_db($database) or die(mysql_error());

     // get the image from the db
     $sql = "SELECT * FROM TableFichier WHERE ficId=" .$_GET['ficId'] . ";";

     // the result of the query
     $result = mysql_query($sql) or die("Invalid query: " . mysql_error());
while($rangee=mysql_fetch_array($result))
{


     // set the header for the image
     header("Content-type: ".$rangee['type']);
     echo $rangee['content'];
}
     // close the db link
     mysql_close($link);
 }
 else {
     echo 'Please use a real id number';
 }
?>