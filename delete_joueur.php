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
$NomJoueur = $_POST['NomJoueur'];
$NumeroJoueur = $_POST['NumeroJoueur'];
$Equipe = $_POST['Equipe'];
if(empty($NomJoueur))
{
print("<center>Le '<b>Nom du Joueur</b>' est vide !</center>");
exit();
}


$con = mysql_connect("localhost","root","");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

mysql_select_db("une_bd", $con);

if(!mysql_query("INSERT INTO TableJoueur (NomJoueur, NumeroJoueur, Equipe)
VALUES ('".$NomJoueur."', '".$NumeroJoueur."', '".$Equipe."')"));

mysql_close($con);

#if($redirect=='1'){
#}else{
#include('page_erreur.html');
#}

?> 
<?php
include('montre_bdj.php');

?> 

</html>
