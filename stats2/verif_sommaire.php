<html>
<head>
<title> Processing...</title>
<style type="text/css">
body {
	font-family:verdana,arial,sans-serif;
	font-size:10pt;
	margin:30px;
	background-color:#DAEDFF;
	}
</style>

</head>

<?php
$marqueur = $_POST['marqueur'];
$passeur1 = $_POST['passeur1'];
$passeur2 = $_POST['passeur2'];
$chrono = $_POST['chrono'];
$equipe = $_POST['equipe'];
if(empty($marqueur))
{
print("<center>Le '<b>Il n'y a pas de marqueur identifié!</b>' est vide !</center>");
exit();
}


$con = mysql_connect("localhost","syncsta1_u01","test");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

mysql_select_db("syncsta1_900", $con);

if(!mysql_query("INSERT INTO TableSommaire (marqueur, passeur1, passeur2, chrono, equipe)
VALUES ('".$marqueur."', '".$passeur1."', '".$passeur2."', '".$chrono."', '".$equipe."')"));

mysql_close($con);

#if($redirect=='1'){
#}else{
#include('page_erreur.html');
#}

?> 
<?php
include('editframe.php');
?> 

</html>
