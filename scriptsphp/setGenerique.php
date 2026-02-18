<?php
require '../scriptsphp/defenvvar.php';


$valeur = json_decode(stripslashes($_POST['valeur']));
$table = $_POST['table'];
$mode = $_POST['mode'];
if(strcmp($table, "Ligue")==0 )
{

	$valeur = $_POST['valeur'];
}

if(strcmp($table, "TableArbitre")==0)
{
	$tmpVal=stripslashes($_POST['valeur']);
	//$valeur=str_replace('"[', '[',$tmpVal);
	//$valeur=str_replace(']"', ']',$valeur);
	$valeur=$_POST['valeur'];
	
	$valeur = json_decode($valeur,true);
	//echo $valeur;
	//echo "---";
	if(strcmp($valeur, "")==0)
	{
	//	echo "traitement valeur alternatif";
	$valeur=$tmpVal;
	$valeur = json_decode($valeur,true);
	
	}
	//echo $valeur;

	//echo json_encode($valeur);
}
$critere = $_POST['critere'];
if(strcmp($table, "EvaluationJoueurs")==0)
{$critere = stripslashes(str_replace("'","",$_POST['critere']));}
else {
	if(strcmp($table, "RapportMatch")==0||strcmp($table, "TableEvenement0")==0)
	{
		$critere = $_POST['critere'];
		
	}else {
	if(strcmp($table, "TableMatch")==0)
	{
		$critere = $_POST['critere'];
		
	}
	else {
		$critere = str_replace("'","",$_POST['critere']);
	}

	}
}
//$critere = stripslashes($_POST['critere']);

//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

//////////////////////////////////
//
//	Les queries
//

if (strcmp($mode, 'creer') == 0) {

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
			$query_set .= "'" . mysqli_real_escape_string($conn,$value) . "',";
	}
	$query_set = substr($query_set, 0, -1);
	$query_set .= ")";


	$retour = mysqli_query($conn,$query_set) or die("Erreur: " . $query_set . mysqli_error($conn) . json_encode($valeur) . $_POST['valeur']);
}

if (strcmp($mode, 'modif') == 0) {

	$query_set = "UPDATE $table SET ";

	foreach ($valeur as $key => $value) {
		if (strcmp($value, 'NULL') == 0)
			$query_set .= $key . "=NULL,";
		else
			$query_set .= $key . "='" . mysqli_real_escape_string($conn,$value) . "',";
	}
	$query_set = substr($query_set, 0, -1);
	$query_set .= " WHERE ";
	$query_set .= $critere;

	echo mysqli_query($conn,$query_set) or die("Erreur: " . $query_set . mysqli_error($conn) . " , valeur: " . json_encode($valeur) . " , critère: " . json_encode($critere));
	echo $query_set;
}

if (strcmp($mode, 'efface') == 0) {

	$query_set = "DELETE FROM $table ";

	$query_set .= " WHERE ";
	$query_set .= $critere;

	echo mysqli_query($conn,$query_set) or die("Erreur Delete: " . $query_set . mysqli_error($conn) . " , critère: " . json_encode($critere));
	echo $query_set;
}



if (strcmp($mode, 'ecrase') == 0) {

	$query_set = "UPDATE $table SET ";

	foreach ($valeur as $key => $value) {
		if (strcmp($value, 'NULL') == 0)
			$query_set .= $key . "=NULL,";
		else
			$query_set .= $key . "='" . mysqli_real_escape_string($conn,$value) . "',";
	}
	$query_set = substr($query_set, 0, -1);
	$query_set .= " WHERE ";
	$query_set .= $critere;
	$isMAJ =mysqli_query($conn,$query_set) or die("Erreur ecrase: " . $query_set . mysqli_error($conn) . " , valeur: " . json_encode($valeur) . " , critère: " . json_encode($critere));
	echo $isMAJ;
	if (mysqli_affected_rows($conn)<1)
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
				$query_set .= "'" . mysqli_real_escape_string($conn,$value) . "',";
		}
		$query_set = substr($query_set, 0, -1);
		$query_set .= ")";

		$retour = mysqli_query($conn,$query_set) or die("Erreur ecrase: " . $query_set . mysqli_error($conn) . json_encode($valeur) . $_POST['valeur']);

	}

}
//mysqli_close($conn);
?>
