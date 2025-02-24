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

$ligueId = $_POST['ligueId'];


///////////////////////////////////////////////////////////
//
//	Début du corps
//
///////////////////////////////////////////////////////////

$equipeIds = array();
$rLigue = mysqli_query($conn,"SELECT Ligue.*
								FROM  Ligue
								WHERE Ligue.ID_Ligue='$ligueId'") or die(mysqli_error($conn));
$aLigue = mysqli_fetch_assoc($rLigue);

//////////////////////////////////////////////////
//
// 	écrit JSON


$JSONstring = "{\"Ligue\":{\"nom\": \"" . $aLigue['Nom_Ligue'] . "\",";
$JSONstring .= "\"ligueId\": \"" . $aLigue['ID_Ligue'] . "\",";
$JSONstring .= "\"ficId\": \"" . $aLigue['ficId'] . "\",";
$JSONstring .= "\"lieu\": \"" . $aLigue['Lieu'] . "\",";
$JSONstring .= "\"horaire\": \"" . $aLigue['Horaire'] . "\",";
$JSONstring .= "\"dernierMAJ\": \"" . $aLigue['dernierMAJ']. "\",";
$JSONstring .= "\"cleValeur\": " . $aLigue['cleValeur'] . ",";
$JSONstring .= "\"saisons\": ";

$j2=$JSONstring;
$rSaison = mysqli_query($conn,"SELECT TableSaison.*, abonEquipeLigue.*
								FROM TableSaison
								LEFT JOIN abonEquipeLigue
									ON TableSaison.ligueRef=abonEquipeLigue.ligueId
								WHERE ligueRef='$ligueId'
								AND
								finAbon>=premierMatch
								AND debutAbon<=dernierMatch
								ORDER BY dernierMatch DESC
								") or die(mysqli_error($conn));
$mSai = 0;
$eqIdsLoc=array();
$jsonSai=array();		
			$Is=0;
$JSONEquipes = "\"equipes\": [";
while ($aSaison = mysqli_fetch_assoc($rSaison)) {
	if ($aSaison['saisonId'] != $mSai) {
		if($mSai!=0)
			{$Is++;}// Cas de la 1re iteration
		$eqIdsLoc=array();
		$jsonSai[$Is]['saisonId']=$aSaison['saisonId'];
		$jsonSai[$Is]['type']=$aSaison['typeSaison'];
		$jsonSai[$Is]['saisonActive']=$aSaison['saisonActive'];
		$jsonSai[$Is]['pm']=$aSaison['premierMatch'];
		$jsonSai[$Is]['dm']=$aSaison['dernierMatch'];
		$jsonSai[$Is]['nom']=$aSaison['nom'];
//		$jsonSai[$Is]['cleValeur']=$aSaison['cleValeur'];		
		
		$mSai = $aSaison['saisonId'];
	}
		if(!isset($jsonSai[$Is]['equipes']))
			{$jsonSai[$Is]['equipes']=array();}
		array_push($jsonSai[$Is]['equipes'], $aSaison['equipeId']);
		array_push($eqIdsLoc, $aSaison['equipeId']);
	
	if (in_array($aSaison['equipeId'], $equipeIds) == false) {
		array_push($equipeIds, $aSaison['equipeId']);
		// Entrer d'Array et faire un JSON d'equipe.4
		$rEquipe = mysqli_query($conn,"SELECT *
								FROM TableEquipe
								WHERE equipe_id='{$aSaison['equipeId']}'
								") or die(mysqli_error($conn));
		while ($aEquipe = mysqli_fetch_assoc($rEquipe)) {
			$JSONEquipes .= "{\"equipeId\": \"" . $aEquipe['equipe_id'] . "\",";
			$JSONEquipes .= "\"nom\": \"" . $aEquipe['nom_equipe'] . "\",";
			$JSONEquipes .= "\"ville\": \"" . $aEquipe['ville'] . "\",";
			$JSONEquipes .= "\"logo\": \"" . $aEquipe['logo'] . "\",";
			$JSONEquipes .= "\"ficId\": \"" . $aEquipe['ficId'] . "\",";
			$JSONEquipes .= "\"couleur1\": \"" . $aEquipe['couleur1'] . "\",";
			$JSONEquipes .= "\"dernierMAJ\": \"" . $aEquipe['dernierMAJ'] . "\"},";

		}

	}
}
		


$rSaisonSeul = mysqli_query($conn,"SELECT TableSaison.*
								FROM TableSaison
								WHERE ligueRef='$ligueId'
								ORDER BY dernierMatch DESC
								") or die(mysqli_error($conn));

while ($aSaisonSeul = mysqli_fetch_assoc($rSaisonSeul)) 
{
$Is=count($jsonSai);								
	$trouve=0;
	$a=0;
		while($a<$Is) //$Is est égal à count($jsonSai);
		{
			if($jsonSai[$a]['saisonId']==$aSaisonSeul['saisonId']) 	
				$trouve=1;
		$a++;
		}
		if($trouve==0)
		{
//			$Is++;
		$jsonSai[$Is]['saisonId']=$aSaisonSeul['saisonId'];
		$jsonSai[$Is]['type']=$aSaisonSeul['typeSaison'];
		$jsonSai[$Is]['saisonActive']=$aSaisonSeul['saisonActive'];
		$jsonSai[$Is]['pm']=$aSaisonSeul['premierMatch'];
		$jsonSai[$Is]['dm']=$aSaisonSeul['dernierMatch'];
		$jsonSai[$Is]['nom']=$aSaisonSeul['nom'];
//		$jsonSai[$Is]['cleValeur']=$aSaison['cleValeur'];		
		
//		$jsonSai[$Is]['equipes']=array();						
		}
}			

	if(!strcmp(",",substr($JSONEquipes, -1)))
		$JSONEquipes = substr($JSONEquipes, 0,-1);
	$JSONEquipes .= "]";
	
//
//		echo $JSONstring;
		echo $j2.json_encode($jsonSai)."}";

echo ",".$JSONEquipes;


	$arenas= array();
$rArena = mysqli_query($conn,"SELECT abonLigueArena.*
								FROM abonLigueArena
								WHERE ligueId='$ligueId'
								AND finAbon>=NOW()
								AND debutAbon<=NOW()
								") or die(mysqli_error($conn));

while ($aArena = mysqli_fetch_assoc($rArena)) 
{
	$Ia=0;

	$arenas[$Ia]=$aArena['arenaId'];
	$Ia++;
}			

echo ", \"Arenas\":".json_encode($arenas);

echo "}";
mysqli_close($conn);

?>
	