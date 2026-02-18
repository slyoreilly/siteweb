<?php require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

	mysqli_query($conn,"UPDATE TableEvenement0 SET equipe_event_id='{$eqId}' WHERE match_event_id='{$matchId}' AND 
														equipe_event_id='{$ancienEqId}' ");


	mysqli_query($conn,"UPDATE TableMatch SET eq_vis='{$eqId}' WHERE matchIdRef='{$matchId}' AND 
														eq_vis='{$ancienEqId}'"); 
	mysqli_query($conn,"UPDATE TableMatch SET eq_dom='{$eqId}' WHERE matchIdRef='{$matchId}' AND 
														eq_dom='{$ancienEqId}'");

//mysqli_close($conn);
//echo $tabButs[$butMAJ->noSeq]->chrono;
?>

