<?php
$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';

$valeur = json_decode(stripslashes($_POST['valeur']));
$table = $_POST['table'];
$mode = $_POST['mode'];
$critere = str_replace("'", "", $_POST['critere']);

if (!mysql_connect($db_host, $db_user, $db_pwd))
	die("Can't connect to database");

if (!mysql_select_db($database)) {
	echo "<h1>Database: {$database}</h1>";
	echo "<h1>Table: {$table}</h1>";
	die("Can't select database");
}

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");
//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

//////////////////////////////////
//
//	On calcule tout les matchs. On update les rapports existant
//	Puis on trouve les matchs sans rapports. On crée alors ces rapports.
//
//

$matchInscrits = array();
$matchTotaux = array();

$qTout = "SELECT * 
			FROM TableMatch
			WHERE 1";

$retTout = mysql_query($qTout) or die("Erreur: " . mysql_error() . $qTout);

while ($rTout = mysql_fetch_array($retTout)) {
	array_push($matchTotaux, $rTout);
}

$qSelect = "SELECT * 
			FROM TableMatch
			JOIN RapportMatch
				ON RapportMatch.matchId=TableMatch.matchIdRef
			WHERE 1 GROUP BY matchId ORDER BY rapportId";

$retour = mysql_query($qSelect) or die("Erreur: " . mysql_error() . $qSelect);

while ($rSel = mysql_fetch_array($retour)) {
	$qqchose = false;
	$vecCV = (array) json_decode($rSel['cleValeur']);
	echo json_encode($vecCV);
	//echo $rSel['cleValeur'];
	//	echo $rSel['matchId']."\n";
	if ($vecCV != NULL && $vecCV != false) {


				if (array_key_exists('arbitreId', $vecCV)) {

			$strArb = "arbitreId=";
			$strArb .= "'{$vecCV['arbitreId']}'";
			$qqchose = true;
		
			
		} else {
			$strArb = "";
		}

		if (array_key_exists('tempsButs', $vecCV)) {
			if ($qqchose) {$strArb .= ", ";
			}

						$strTB = "tempsButs=";
						$strTemp =json_encode($vecCV['tempsButs']);
			//echo $strTemp;
			$strTB .= "'{$strTemp}'";
			$qqchose = true;
			
		} else {
			$strTB = "";
		}
		


	}

			
	//echo $rSel;
	//$vecCV = json_decode($rSel['cleValeur']);
	$qUp = "UPDATE RapportMatch SET " . $strArb . $strTB . " WHERE matchId='{$rSel['matchId']}'";
	echo $qUp . "  -   " . $qqchose . "\n";
	if ($qqchose) {$retour = mysql_query($qUp) or die("Erreur: " . $qUp . mysql_error());
	}

	array_push($matchInscrits, $rSel['matchId']);
	//echo json_encode($matchInscrits);
}


$matchNouveaux = $matchTotaux;

// On retire les matchs avec le rapport existant
$IMI = 0;

while ($IMI < count($matchInscrits)) {
	//echo $matchInscrits[$IMI];
	//echo stripslashes(str_replace('"', "",$matchInscrits[$IMI]))."\n";
	//echo stripslashes(str_replace('"', "",$matchNouveaux[$IMI]['matchIdRef']))."\n";
	//echo $matchNouveaux[$IMI]['matchIdRef']."\n";
	$IMN = 0;
	$trouve=false;
	while ($IMN < count($matchNouveaux)&&!$trouve) {
		//echo json_encode($matchNouveaux[$IMN]);
		if (strcmp(stripslashes(str_replace('"', "",$matchInscrits[$IMI])),stripslashes(str_replace('"', "",$matchNouveaux[$IMN]['matchIdRef'])))==0)// Si pareil, on enlève
			{
				/*echo*/ json_encode(array_splice($matchNouveaux,$IMN,1));
				$trouve=true;
			}
		else
			$IMN++;

	}
	$IMI++;
}

// On insère les nouveaux
$IMN = 0;
//echo count($matchNouveaux);

while ($IMN < count($matchNouveaux)) {
		if (array_key_exists('arbitreId', $matchNouveaux[$IMN])) {
		$strArb1 = "arbitreId,";
		$strArb2 = "{$matchNouveaux[$IMN]['arbitreId']}, ";
	} else {
		$strArb1 = "";
		$strArb2 = "";
	}
	if (array_key_exists('tempsButs', $matchNouveaux[$IMN])) {
		$strTB1 = "tempsButs,";
		$strTB2 = "{$matchNouveaux[$IMN]['tempsButs']}, ";
	} else {
		$strTB1 = "";
		$strTB2 = "";
	}
	//echo json_encode($matchNouveaux[$IMN])."\n";
	$qInsMatch = "INSERT INTO RapportMatch (" . $strArb1 . "" . $strTB1 . "matchId,date) Values (" . $strArb2 . "" . $strTB2 . "'{$matchNouveaux[$IMN]['matchIdRef']}','{$matchNouveaux[$IMN]['date']}');";
	//echo $qInsMatch;
	$retInsMatch = mysql_query($qInsMatch) or die("Erreur: " . $qInsMatch . mysql_error());

	$IMN++;
}
?>
