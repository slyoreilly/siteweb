<?php 
 Print "Hello, World!";

 ?>

 
<?php
$con = mysql_connect("localhost","syncsta1_u01","test");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
else
{
Print "Connection réussie!!!";
}

if (mysql_query("CREATE DATABASE syncsta1_900",$con))
  {
  echo "Base de données créées ";
  }
else
  {
  echo "Erreur en créant la base de données: " . mysql_error();
  }

// Créer les tables

mysql_select_db("syncsta1_900", $con);
$sql = "CREATE TABLE TableJoueur
(
NomJoueur varchar(15),
NumeroJoueur int,
Equipe varchar(15)
)";

mysql_select_db("syncsta1_900", $con);
$sql2 = "CREATE TABLE TableMatch
(
MatchId varchar(30),
Evenement int,
)";



// Execute query
mysql_query($sql,$con);
mysql_query($sql2,$con);

mysql_close($con);
?> 