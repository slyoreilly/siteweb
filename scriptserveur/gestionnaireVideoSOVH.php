<?php


///     gestionnaireVideoSJHT.php  
//
//		Gestionnaire des vidéos sur le serveur JustHost
//



////  Connexions Databases

$db_host="syncstatstv";
$db_user="syl";
$db_pwd="pr351d3nt";

$database = 'syncsta1_100';

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
	



////    Réception des arguments


//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
//$params = array();
//$params =json_decode($_POST['videos'],true);

session_start();
$nomFic = $_POST['nomFic'];
$ligueId = $_POST['ligueId'];

//////    Viande

$serveurCible = "syncstats.com";

/*
$url = 'http://'.$serveurCible.'/scriptserveur/gestionnaireVideoSOVH.php';
$http = new HttpRequest($url, HttpRequest::METH_POST);
$http->setOptions(array(
    'timeout' => 10,
    'redirect' => 4
));
$http->addPostFields(array(
    $nomFic => 'nomFic',
   $ligueId => 'ligueId'
));

$response = $http->send();
echo $response->getBody();
*/



	

?>
