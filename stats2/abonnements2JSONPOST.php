<?php

/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
//
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';



$ligueIdInter = $_POST['ligueId'];
$userId = $_POST['userId'];

$ligueId = $ligueIdInter;

//if(is_numeric($ligueIdInter)&&!is_null($ligueIdInter))
//{$ligueId = trouveIDParNomLigue($ligueIdInter);}
//if(!is_numeric($ligueIdInter)&&!is_null($ligueIdInter))
//{$ligueId = $ligueIdInter;}

if (is_numeric($ligueId)) {
	$resultEquipe = mysqli_query($conn,"SELECT * FROM AbonnementLigue WHERE ligueid='{$ligueId}' AND contexte='ligue'") or die(mysqli_error($conn));
	$vecUtilisateurs=Array();
	$boule = 0;
	while ($rangee = mysqli_fetch_array($resultEquipe)) {
		$tmpRang=Array();
		$tmpRang['userId']=$rangee['userid'];
		$tmpRang['type']=$rangee['type'];
		$tmpRang['ligueId']=$rangee['ligueId'];
		$boule = 1;
		array_push($vecUtilisateurs,$tmpRang);
	}
	if ($boule == 0) {
		$tmpRang['userId']=null;
		$tmpRang['type']=30;
		$tmpRang['ligueId']=null;

	}
	$JSONstring=json_encode($vecUtilisateurs);
} else {
	if (isset($userId)) {
		$resultEquipe = mysqli_query($conn,"SELECT * FROM AbonnementLigue
					JOIN TableUser
						ON(TableUser.noCompte=AbonnementLigue.userid)
					 WHERE username='{$userId}' AND contexte='ligue'") or die(mysqli_error($conn));
		$abon=array();
		$IA=0;
		while ($rangee = mysqli_fetch_array($resultEquipe)) {
			$abon[$IA]['ligueId']=$rangee['ligueid'];
			$abon[$IA]['type']=$rangee['type'];
		}
		$JSONstring=json_encode($abon);
	}

}

echo $JSONstring;
?>

