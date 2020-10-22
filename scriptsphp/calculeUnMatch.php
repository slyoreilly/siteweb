<?php

/////////////////////////////////////////////////////////////
//
//  Définitions des variables
//
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';

////////////////////////////////////////////////////////////
//
// 	Connections é la base de données
//
////////////////////////////////////////////////////////////

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");


if( isset($_POST['noMatchId']) )
{
	$noMatchId = $_POST['noMatchId'];
}


//////////
///  $noMatchId doit être défini dans le script appellant

// Vérification s'il y a une inscription dans tablematch
$rEnr = mysqli_query($conn, "SELECT matchIdRef,eq_dom,eq_vis
									FROM TableMatch 
								WHERE match_id = '{$noMatchId}'") or die(mysqli_error($conn));
$isEnr = mysqli_num_rows($rEnr);

if ($isEnr > 0) {


$vecMatch = mysqli_fetch_row($rEnr);
$matchIdRef = $vecMatch[0];
$eDom = $vecMatch[1];
$eVis = $vecMatch[2];
// Obtention du code de période pour tableevenement0
$rPeriode = mysqli_query($conn, "SELECT MAX(souscode) as sc
								FROM TableEvenement0 
								WHERE match_event_id = '{$matchIdRef}' 
								AND code=11") or die(mysqli_error($conn));

if (mysqli_num_rows($rPeriode) > 0) {
	$statutAr = mysqli_fetch_row($rPeriode);
	if ($statutAr[0] < 10) {
		if(is_null($statutAr[0])){
		$statut = 0;
		}else{
			$statut = $statutAr[0];

		}
	} else {
		$statut = $statutAr[0] % 10;
		$statut = $statut . 'P';
	}
} else {
	$statut = 0;
}

//Vérifivation si le match est complet pour enregistrement définitif.
$matchFini = mysqli_query($conn, "SELECT * 
								FROM TableEvenement0 
								WHERE match_event_id = '{$matchIdRef}' 
								AND code=10 
								AND souscode=10") or die(mysqli_error($conn));
$fini = mysqli_num_rows($matchFini);

///Compte le score
$compteDom = mysqli_query($conn, "SELECT * 
								FROM TableEvenement0 
								WHERE match_event_id = '{$matchIdRef}' 
									AND code=0 
									AND equipe_event_id =  '{$eDom}'") or die(mysqli_error($conn));

$compteVis = mysqli_query($conn, "SELECT * 
								FROM TableEvenement0 
								WHERE match_event_id = '{$matchIdRef}' 
									AND code=0 
									AND equipe_event_id = '{$eVis}'") or die(mysqli_error($conn));
$cDom = mysqli_num_rows($compteDom);
$cVis = mysqli_num_rows($compteVis);

if ($fini > 0) {

	if ($cDom == $cVis) {
		$cFD = 0;
		$cFV = 0;

		$resFus = mysqli_query($conn, "SELECT  * FROM TableEvenement0 
										WHERE match_event_id = '{$matchIdRef}' AND code=2 AND souscode=1") or die(mysqli_error($conn));

		while ($rangFus = mysqli_fetch_array($resFus)) {
			if ($rangFus['equipe_event_id'] == $eDom)
				$cFD++;
			if ($rangFus['equipe_event_id'] == $eVis)
				$cFV++;
		}
		if ($cFD > $cFV)
			$cDom++;
		if ($cFV > $cFD)
			$cVis++;
	}

	if ($isEnr == 0) {
				$qNiou="INSERT 
								INTO TableMatch 
									(eq_dom, score_dom, eq_vis, score_vis, matchIdRef, ligueRef, date,statut) 
								VALUES 
									('{$eDom}', '{$cDom}', '{$eVis}', '{$cVis}','{$noMatchId}','{$ligueId}','{$aDate}','F')";
			
		$retour = mysqli_query($conn, $qNiou) or die(mysqli_error($conn) . "INSERT 	INTO TableMatch");

	} else {
			$qNiou="UPDATE TableMatch
											SET score_dom='{$cDom}', score_vis='{$cVis}' ,statut='F'
											WHERE match_id='{$noMatchId}'";
		$retour = mysqli_query($conn, $qNiou);
	}

} else {
	if ($isEnr == 0) {
			$qNiou = "INSERT 
								INTO TableMatch 
									(eq_dom, score_dom, eq_vis, score_vis, matchIdRef, ligueRef, date,statut) 
								VALUES 
									('{$eDom}', '{$cDom}', '{$eVis}', '{$cVis}','{$noMatchId}','{$ligueId}','{$aDate}','{$statut}')";
		$retour = mysqli_query($conn, $qNiou) or die(mysqli_error($conn) . "INSERT 	INTO TableMatch");

	} else {
$qNiou ="UPDATE TableMatch
											SET score_dom='{$cDom}', score_vis='{$cVis}' ,statut='{$statut}'
											WHERE match_id='{$noMatchId}'	";
		$retour = mysqli_query($conn, $qNiou);
	}
}
echo $qNiou;

}
mysqli_close($conn);

?>

