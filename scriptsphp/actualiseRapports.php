<?php
require '../scriptsphp/defenvvar.php';

$valeur = json_decode(stripslashes($_POST['valeur']));
$table = $_POST['table'];
$mode = $_POST['mode'];
$critere = str_replace("'", "", $_POST['critere']);

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

$retour = mysqli_query($conn, $qSelect) or die("Erreur: " . mysqli_error($conn) . $qSelect);
while ($rInsc = mysql_fetch_array($retour)) {
	array_push($matchInscrits, stripslashes($rInsc['matchIdRef']));
}
echo json_encode($matchInscrits);




$qTout = "SELECT * 
			FROM TableMatch
			WHERE 1";

$retTout = mysqli_query($conn,$qTout) or die("Erreur: " . mysqli_error($conn) . $qTout);

$IT=0;
while ($rTout = mysqli_fetch_array($retTout)) {
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
	if ($qqchose) {$retour = mysqli_query($conn,$qUp) or die("Erreur: " . $qUp . mysqli_error($conn));
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
	$retInsMatch = mysqli_query($conn,$qInsMatch) or die("Erreur: " . $qInsMatch . mysqli_error($conn));

	
	
		
	}
}





?>
