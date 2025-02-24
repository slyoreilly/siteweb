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


// Retrieve all the data from the "example" table
$resultUser = mysqli_query($conn, "SELECT * FROM TableUser") or die(mysqli_error($conn));
while ($rangeeUser = mysqli_fetch_array($resultUser)) {
	if (!strcmp($rangeeUser['username'], $username)) {$userSelect = $rangeeUser['noCompte'];
	}
	// Prend le ID du user pour trouver les ligues abonn�es.
}

$resultAbon = mysqli_query($conn,"SELECT * FROM AbonnementLigue ORDER BY ligueid") or die(mysqli_error($conn));

$AbonSelect = array();
$dernierLogApp = array();
while ($rangeeAbon = mysqli_fetch_array($resultAbon)) {
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
$resultAbonArb = mysqli_query($conn,$qAbonArb) or die(mysqli_error($conn) . $qAbonArb);

while ($rangeeAbonArb = mysqli_fetch_array($resultAbonArb)) {
	if (!in_array($rangeeAbonArb['ligueId'], $AbonSelect)) {array_push($AbonSelect, $rangeeAbonArb['ligueId']);
	//	array_push($dernierLogApp, $rangeeAbonArb['dernierLogApp']);
	}
}

// On obtient un array de ligueID auquel userSelect est abonn�.
$serveurHeure = time()*1000;

if(isset($_POST["telHeure"])){
$query = "INSERT INTO TempsSync (telId,telHeure,serveurHeure,username) ".
		"VALUES ('{$telId}','{$telHeure}','{$serveurHeure}','{$username}')";
		mysqli_query($conn, $query) or die("Erreur: "+$query+"\n"+mysqli_error($conn));
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
	$resultLigue = mysqli_query($conn,"SELECT * FROM {$tableLigue} ORDER BY ID_Ligue") or die(mysqli_error($conn));

	$Iligue++;

}//Fin du scan des ligues auquel l'utilisateur est abbonn�.

//$JSONstring = 	"{\"ligues\": ".json_encode($AbonSelect);
//	$JSONstring .= ", \"dernierLogApp\":".json_encode($dernierLogApp);
//	$JSONstring .= ", \"heure\":\"".time()."\"}";
$repSite = array();
$repSite['heure'] = time();



//echo json_encode($Sommaire);
echo json_encode($repSite);
mysqli_close($conn);
?>