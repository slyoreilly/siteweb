<?php


 // just so we know it is broken
 error_reporting(E_ALL);

 require '../scriptsphp/defenvvar.php';




 // some basic sanity checks
 if(isset($_GET['ficId']) && is_numeric($_GET['ficId'])) {


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