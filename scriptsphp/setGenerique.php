<?php
$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';

if (!mysql_connect($db_host, $db_user, $db_pwd))
	die("Can't connect to database");

if (!mysql_select_db($database)) {
	echo "<h1>Database: {$database}</h1>";
	echo "<h1>Table: {$table}</h1>";
	die("Can't select database");
}

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

$valeur = json_decode(stripslashes($_POST['valeur']));
$table = $_POST['table'];
$mode = $_POST['mode'];
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

if(strcmp($table, "EvaluationJoueurs")==0)
{$critere = stripslashes(str_replace("'","",$_POST['critere']));}
else {
	if(strcmp($table, "RapportMatch")==0)
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
			$query_set .= "'" . mysql_real_escape_string($value) . "',";
	}
	$query_set = substr($query_set, 0, -1);
	$query_set .= ")";


	$retour = mysql_query($query_set) or die("Erreur: " . $query_set . mysql_error() . json_encode($valeur) . $_POST['valeur']);
}

if (strcmp($mode, 'modif') == 0) {

	$query_set = "UPDATE $table SET ";

	foreach ($valeur as $key => $value) {
		if (strcmp($value, 'NULL') == 0)
			$query_set .= $key . "=NULL,";
		else
			$query_set .= $key . "='" . mysql_real_escape_string($value) . "',";
	}
	$query_set = substr($query_set, 0, -1);
	$query_set .= " WHERE ";
	$query_set .= $critere;

	echo mysql_query($query_set) or die("Erreur: " . $query_set . mysql_error() . " , valeur: " . json_encode($valeur) . " , critère: " . json_encode($critere));
	echo $query_set;
}

if (strcmp($mode, 'efface') == 0) {

	$query_set = "DELETE FROM $table ";

	$query_set .= " WHERE ";
	$query_set .= $critere;

	echo mysql_query($query_set) or die("Erreur Delete: " . $query_set . mysql_error() . " , critère: " . json_encode($critere));
	echo $query_set;
}



if (strcmp($mode, 'ecrase') == 0) {

	$query_set = "UPDATE $table SET ";

	foreach ($valeur as $key => $value) {
		if (strcmp($value, 'NULL') == 0)
			$query_set .= $key . "=NULL,";
		else
			$query_set .= $key . "='" . mysql_real_escape_string($value) . "',";
	}
	$query_set = substr($query_set, 0, -1);
	$query_set .= " WHERE ";
	$query_set .= $critere;
	$isMAJ =mysql_query($query_set) or die("Erreur ecrase: " . $query_set . mysql_error() . " , valeur: " . json_encode($valeur) . " , critère: " . json_encode($critere));
	echo $isMAJ;
	if (mysql_affected_rows($isMAJ)<1)
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
				$query_set .= "'" . mysql_real_escape_string($value) . "',";
		}
		$query_set = substr($query_set, 0, -1);
		$query_set .= ")";

		$retour = mysql_query($query_set) or die("Erreur ecrase: " . $query_set . mysql_error() . json_encode($valeur) . $_POST['valeur']);

	}

}
?>
