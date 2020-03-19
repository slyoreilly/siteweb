<?php
require '../scriptsphp/defenvvar.php';nt0';
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
	
	mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");
	

function trouveIDParNomUser($nomUser)
{
$fResultUser = mysql_query("SELECT noCompte 
								FROM TableUser 
								WHERE username='{$nomUser}'")
or die(mysql_error());  
$rU = mysql_fetch_row($fResultUser);
if (mysql_num_rows($fResultUser)>0)
{
return $rU[0];
}
else{return -1;}

}


$expediteur = trouveIDParNomUser($_POST['expediteur']);
$recepteur = $_POST['recepteur'];
$titre = mysql_real_escape_string($_POST['titre']);
$corps = mysql_real_escape_string($_POST['corps']);
$cleValeur = $_POST['cleValeur'];


	$retour = mysql_query("INSERT INTO TableMessage (expediteur, recepteur, titre, corps, dateEmission,cleValeur) 
VALUES ('{$expediteur}','{$recepteur}','{$titre}','{$corps}',NOW(),'{$cleValeur}')")or die(mysql_error()." INSERT INTO");

?>
<?php  ?>
