<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$username = $_POST['username'];
$telId = $_POST['telId'];
$telHeure = $_POST['telHeure'];
//$vielledate = $_POST['vielledate'];

if (!mysql_connect($db_host, $db_user, $db_pwd))
	die("Can't connect to database");

if (!mysql_select_db($database)) {
	echo "<h1>Database: {$database}</h1>";
	echo "<h1>Table: {$table}</h1>";
	die("Can't select database");

}
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

// Retrieve all the data from the "example" table
$resultUser = mysql_query("SELECT * FROM TableUser") or die(mysql_error());
while ($rangeeUser = mysql_fetch_array($resultUser)) {
	if (!strcmp($rangeeUser['username'], $username)) {$userSelect = $rangeeUser['noCompte'];
	}
	// Prend le ID du user pour trouver les ligues abonn�es.
}

$resultAbon = mysql_query("SELECT * FROM AbonnementLigue ORDER BY ligueid") or die(mysql_error());

$AbonSelect = array();
$dernierLogApp = array();
while ($rangeeAbon = mysql_fetch_array($resultAbon)) {
	if ($rangeeAbon['userid'] == $userSelect) {array_push($AbonSelect, $rangeeAbon['ligueid']);
		//array_push($AbonSelect, $dernierLogApp['dernierLogApp']);
	}
}

$qAbonArb = "SELECT * FROM abonArbitreLigue 
								JOIN TableArbitre
									ON (TableArbitre.arbitreId=abonArbitreLigue.arbitreId)
								JOIN TableUser
									ON 	(TableArbitre.userId=TableUser.noCompte)
								WHERE TableArbitre.userId='{$userSelect}'
								ORDER BY ligueId";
$resultAbonArb = mysql_query($qAbonArb) or die(mysql_error() . $qAbonArb);

while ($rangeeAbonArb = mysql_fetch_array($resultAbonArb)) {
	if (!in_array($rangeeAbonArb['ligueId'], $AbonSelect)) {array_push($AbonSelect, $rangeeAbonArb['ligueId']);
	//	array_push($dernierLogApp, $rangeeAbonArb['dernierLogApp']);
	}
}

// On obtient un array de ligueID auquel userSelect est abonn�.
$serveurHeure = time()*1000;

if(isset($_POST["telHeure"])){
$query = "INSERT INTO TempsSync (telId,telHeure,serveurHeure,username) ".
		"VALUES ('{$telId}','{$telHeure}','{$serveurHeure}','{$username}')";
		mysql_query($query) or die("Erreur: "+$query+"\n"+mysql_error());
}
$ligueSelect = array();
$equipeSelect = array();
$joueurSelect = array();

$noLigue = 0;
$Iligue = 0;
$Iequipe = 0;
/*

 */header("HTTP/1.1 200 OK");
//echo " ".count($AbonSelect);
while ($Iligue < count($AbonSelect)) {
	$resultLigue = mysql_query("SELECT * FROM {$tableLigue} ORDER BY ID_Ligue") or die(mysql_error());

	$Iligue++;

}//Fin du scan des ligues auquel l'utilisateur est abbonn�.

//$JSONstring = 	"{\"ligues\": ".json_encode($AbonSelect);
//	$JSONstring .= ", \"dernierLogApp\":".json_encode($dernierLogApp);
//	$JSONstring .= ", \"heure\":\"".time()."\"}";
$repSite = array();
$repSite['heure'] = time();



//echo json_encode($Sommaire);
echo json_encode($repSite);
mysql_close();
?>