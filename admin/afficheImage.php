<?php


 // just so we know it is broken
 error_reporting(E_ALL);

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";
$database = 'syncsta1_900';




 // some basic sanity checks
 if(isset($_GET['ficId']) && is_numeric($_GET['ficId'])) {


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");



     // get the image from the db
     $sql = "SELECT * FROM TableFichier WHERE ficId=" .$_GET['ficId'] . ";";

     // the result of the query
     $result = mysqli_query($conn,$sql) or die("Invalid query: " . mysql_error());
while($rangee=mysqli_fetch_array($result))
{


     // set the header for the image
     header("Content-type: ".$rangee['type']);
     echo $rangee['content'];
}
     // close the db link
     mysqli_close($conn);
 }
 else {
     echo 'Please use a real id number';
 }
?>