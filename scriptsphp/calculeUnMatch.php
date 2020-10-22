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

$connC1M = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$connC1M) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($connC1M, "SET NAMES 'utf8'");
mysqli_query($connC1M, "SET CHARACTER SET 'utf8'");


if( isset($_POST['noMatchId']) )
{
	$noMatchId = $_POST['noMatchId'];
}


//////////
///  $noMatchId doit être défini dans le script appellant

// Vérification s'il y a une inscription dans tablematch
$rEnr = mysqli_query($connC1M, "SELECT matchIdRef,eq_dom,eq_vis
									FROM TableMatch 
								WHERE match_id = '{$noMatchId}'") or die(mysqli_error($connC1M));
$isEnr = mysqli_num_rows($rEnr);

if ($isEnr > 0) {


$vecMatch = mysqli_data_seek($rEnr,0);
$matchIdRef = $vecMatch[0];
$eDom = $vecMatch[1];
$eVis = $vecMatch[2];
// Obtention du code de période pour tableevenement0
$rPeriode = mysqli_query($connC1M, "SELECT MAX(souscode) as sc
								FROM TableEvenement0 
								WHERE match_event_id = '{$matchIdRef}' 
								AND code=11") or die(mysqli_error($connC1M));

if (mysqli_num_rows($rPeriode) > 0) {
	$statutAr = mysqli_data_seek($rPeriode,0);
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
$matchFini = mysqli_query($connC1M, "SELECT * 
								FROM TableEvenement0 
								WHERE match_event_id = '{$matchIdRef}' 
								AND code=10 
								AND souscode=10") or die(mysqli_error($connC1M));
$fini = mysqli_num_rows($matchFini);

///Compte le score
$compteDom = mysqli_query($connC1M, "SELECT * 
								FROM TableEvenement0 
								WHERE match_event_id = '{$matchIdRef}' 
									AND code=0 
									AND equipe_event_id =  '{$eDom}'") or die(mysqli_error($connC1M));

$compteVis = mysqli_query($connC1M, "SELECT * 
								FROM TableEvenement0 
								WHERE match_event_id = '{$matchIdRef}' 
									AND code=0 
									AND equipe_event_id = '{$eVis}'") or die(mysqli_error($connC1M));
$cDom = mysqli_num_rows($compteDom);
$cVis = mysqli_num_rows($compteVis);

if ($fini > 0) {

	if ($cDom == $cVis) {
		$cFD = 0;
		$cFV = 0;

		$resFus = mysqli_query($connC1M, "SELECT  * FROM TableEvenement0 
										WHERE match_event_id = '{$matchIdRef}' AND code=2 AND souscode=1") or die(mysqli_error($connC1M));

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
			
		$retour = mysqli_query($connC1M, $qNiou) or die(mysqli_error($connC1M) . "INSERT 	INTO TableMatch");

	} else {
			$qNiou="UPDATE TableMatch
											SET score_dom='{$cDom}', score_vis='{$cVis}' ,statut='F'
											WHERE match_id='{$noMatchId}'";
		$retour = mysqli_query($connC1M, $qNiou);
	}

} else {
	if ($isEnr == 0) {
			$qNiou = "INSERT 
								INTO TableMatch 
									(eq_dom, score_dom, eq_vis, score_vis, matchIdRef, ligueRef, date,statut) 
								VALUES 
									('{$eDom}', '{$cDom}', '{$eVis}', '{$cVis}','{$noMatchId}','{$ligueId}','{$aDate}','{$statut}')";
		$retour = mysqli_query($connC1M, $qNiou) or die(mysqli_error($connC1M) . "INSERT 	INTO TableMatch");

	} else {
$qNiou ="UPDATE TableMatch
											SET score_dom='{$cDom}', score_vis='{$cVis}' ,statut='{$statut}'
											WHERE match_id='{$noMatchId}'	";
		$retour = mysqli_query($connC1M, $qNiou);
	}
}
echo $qNiou;

}
mysqli_close($connC1M);

?>

