<?php
require '../scriptsphp/defenvvar.php';


$progression = $_POST['progression'];
echo  $progression;
$saisonId = $_POST['saisonId'];
echo  "nn".$saisonId;
$mode = $_POST['mode'];
echo  $mode."000";
$nom = $_POST['nom'];
echo  $nom;
$donnees = json_encode($_POST['donnees']);
echo  $donnees;
//////////////////////////////////
//
//	Les queries
//

if (strcmp($mode, 'creer') == 0) {

	$query_set = "INSERT INTO Calendriers (
	saisonId, nom, donnees, progression
	) VALUES ( '{$saisonId}','{$nom}','{$donnees}','{$progression}')";

echo  $query_set;
	$retour = mysqli_query($conn, $query_set) or die("Erreur: " . $query_set . mysqli_error($conn) . $query_set);
	
}

if (strcmp($mode, 'modif') == 0) {

	$query_set = "UPDATE $table SET ";

	foreach ($valeur as $key => $value) {
		if (strcmp($value, 'NULL') == 0)
			$query_set .= $key . "=NULL,";
		else
			$query_set .= $key . "='" . mysqli_real_escape_string($conn, $value) . "',";
	}
	$query_set = substr($query_set, 0, -1);
	$query_set .= " WHERE ";
	$query_set .= $critere;

	echo mysqli_query($conn, $query_set) or die("Erreur: " . $query_set . mysqli_error($conn) . " , valeur: " . json_encode($valeur) . " , critère: " . json_encode($critere));
	echo $query_set;
}

if (strcmp($mode, 'efface') == 0) {

	$query_set = "DELETE FROM $table ";

	$query_set .= " WHERE ";
	$query_set .= $critere;

	echo mysqli_query($conn, $query_set) or die("Erreur Delete: " . $query_set . mysqli_error($conn) . " , critère: " . json_encode($critere));
	echo $query_set;
}



if (strcmp($mode, 'ecrase') == 0) {

	$query_set = "UPDATE $table SET ";

	foreach ($valeur as $key => $value) {
		if (strcmp($value, 'NULL') == 0)
			$query_set .= $key . "=NULL,";
		else
			$query_set .= $key . "='" . mysqli_real_escape_string($conn, $value) . "',";
	}
	$query_set = substr($query_set, 0, -1);
	$query_set .= " WHERE ";
	$query_set .= $critere;
	$isMAJ =mysqli_query($conn, $query_set) or die("Erreur ecrase: " . $query_set . mysqli_error($conn) . " , valeur: " . json_encode($valeur) . " , critère: " . json_encode($critere));
	echo $isMAJ;
	if (mysqli_affected_rows($conn, $isMAJ)<1)
	{
		$query_set = "INSERT INTO $table (";

		foreach ($valeur as $key => $value) {
			$query_set .= $key . ",";
		}
		$query_set = substr($query_set, 0, -1);
		$query_set .= ") VALUES (";
		foreach ($valeur as $key => $value) {
			if (strcmp($value, 'NULL') == 0)
				$query_set .= "NULL,";
			else
				$query_set .= "'" . mysqli_real_escape_string($conn, $value) . "',";
		}
		$query_set = substr($query_set, 0, -1);
		$query_set .= ")";

		$retour = mysqli_query($conn, $query_set) or die("Erreur ecrase: " . $query_set . mysqli_error($conn) . json_encode($valeur) . $_POST['valeur']);

	}

}

//mysqli_close($conn);
?>
