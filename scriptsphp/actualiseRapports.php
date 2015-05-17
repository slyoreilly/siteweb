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


$qSelect = "SELECT * 
			FROM TableMatch
			JOIN RapportMatch
				ON RapportMatch.matchId=TableMatch.matchIdRef
			WHERE 1 GROUP BY matchId ORDER BY rapportId";

$retour = mysql_query($qSelect) or die("Erreur: " . mysql_error() . $qSelect);
while ($rInsc = mysql_fetch_array($retour)) {
	array_push($matchInscrits, stripslashes($rInsc['matchIdRef']));
}
echo json_encode($matchInscrits);




$qTout = "SELECT * 
			FROM TableMatch
			WHERE 1";

$retTout = mysql_query($qTout) or die("Erreur: " . mysql_error() . $qTout);

$IT=0;
while ($rTout = mysql_fetch_array($retTout)) {
//	echo "=";
	//array_push($matchTotaux, $rTout);
	
	if(in_array($rTout['matchIdRef'], $matchInscrits))
	{
//		echo "=";
			$qqchose = false;
	$vecCV = (array) json_decode($rTout['cleValeur']);
	//echo json_encode($vecCV);
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
	$qUp = "UPDATE RapportMatch SET " . $strArb . $strTB . " WHERE matchId='{$rTout['matchIdRef']}'";
	echo $qUp . "  -   " . $qqchose . "\n";
	if ($qqchose) {$retour = mysql_query($qUp) or die("Erreur: " . $qUp . mysql_error());
	}
		
	}
	else {
		echo "CRISSE!!!!";
	$mesOpt=json_decode( $rTout['cleValeur'],true);
		echo json_encode($mesOpt);
	
			if (array_key_exists('arbitreId',$mesOpt)) {
		echo "CRISSE IF!!!!";
				
		$strArb1 = "arbitreId,";
		$strArb2 = "{$mesOpt['arbitreId']}, ";
	} else {
			echo "CRISSE ELSE!!!!";
	
		$strArb1 = "";
		$strArb2 = "";
	}
		echo "CRISSE!!!!";
		
	if (array_key_exists('tempsButs', $mesOpt)) {
		$strTB1 = "tempsButs,";
		$mJ=json_encode($mesOpt['tempsButs']);
		$strTB2 = "'{$mJ}', ";
	} else {
		$strTB1 = "";
		$strTB2 = "";
	}
			echo "CRISSE!!!!";
	//echo json_encode($matchNouveaux[$IMN])."\n";
	$qInsMatch = "INSERT INTO RapportMatch (" . $strArb1 . "" . $strTB1 . "matchId,date) Values (" . $strArb2 . "" . $strTB2 . "'{$rTout['matchIdRef']}','{$rTout['date']}');";
	echo $qInsMatch;
	$retInsMatch = mysql_query($qInsMatch) or die("Erreur: " . $qInsMatch . mysql_error());

	
	
		
	}
}





?>
