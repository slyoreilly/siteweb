<?php


///     gestionnaireVideoSJHT.php  
//
//		Gestionnaire des vidéos sur le serveur JustHost
//



////  Connexions Databases

require '../scriptsphp/defenvvar.php';

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
$nomFic = $_SESSION['nomFic'];
$ligueId = $_SESSION['ligueId'];
$forfaitId = $_SESSION['forfaitId'];

//////    Viande

//////    Viande

$serveurCible = "5.39.81.14";


$url = 'http://'.$serveurCible.'/gestionnaireVideoSOVH.php';
$http = new HttpRequest($url, HttpRequest::METH_POST);
$http->setOptions(array(
    'timeout' => 10,
    'redirect' => 4
));
$http->addPostFields(array(
    $nomFic => 'nomFic',
   $ligueId => 'ligueId',
   $forfaitId => 'forfaitId'
));

$response = $http->send();
echo $response->getBody();



?>
