<?php

/////////////////////////////////////////////////////////////
//
//  Définitions des variables
//
////////////////////////////////////////////////////////////

$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';

$ligueId = $_POST['ligueId'];

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

////////////////////////////////////////////////////////////
//
// 	Connections à la base de données
//
////////////////////////////////////////////////////////////

if (!mysql_connect($db_host, $db_user, $db_pwd))
	die("Can't connect to database");

if (!mysql_select_db($database)) {
	echo "<h1>Database: {$database}</h1>";
	die("Can't select database");

}

	mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

///////////////////////////////////////////////////////////
//
//	Début du corps
//
///////////////////////////////////////////////////////////

$equipeIds = array();
$rLigue = mysql_query("SELECT Ligue.*
								FROM  Ligue
								WHERE Ligue.ID_Ligue='$ligueId'") or die(mysql_error());
$aLigue = mysql_fetch_assoc($rLigue);

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
$rSaison = mysql_query("SELECT TableSaison.*, abonEquipeLigue.*
								FROM TableSaison
								LEFT JOIN abonEquipeLigue
									ON TableSaison.ligueRef=abonEquipeLigue.ligueId
								WHERE ligueRef='$ligueId'
								AND
								finAbon>=premierMatch
								AND debutAbon<=dernierMatch
								ORDER BY dernierMatch DESC
								") or die(mysql_error());
$mSai = 0;
$eqIdsLoc=array();
$jsonSai=array();		
			$Is=0;
$JSONEquipes = "\"equipes\": [";
while ($aSaison = mysql_fetch_assoc($rSaison)) {
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
		$rEquipe = mysql_query("SELECT *
								FROM TableEquipe
								WHERE equipe_id='{$aSaison['equipeId']}'
								") or die(mysql_error());
		while ($aEquipe = mysql_fetch_assoc($rEquipe)) {
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
		


$rSaisonSeul = mysql_query("SELECT TableSaison.*
								FROM TableSaison
								WHERE ligueRef='$ligueId'
								ORDER BY dernierMatch DESC
								") or die(mysql_error());

while ($aSaisonSeul = mysql_fetch_assoc($rSaisonSeul)) 
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
		$jsonSai[$Is]['nom']=$aSaison['nom'];
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
$rArena = mysql_query("SELECT abonLigueArena.*
								FROM abonLigueArena
								WHERE ligueId='$ligueId'
								AND finAbon>=NOW()
								AND debutAbon<=NOW()
								") or die(mysql_error());

while ($aArena = mysql_fetch_assoc($rArena)) 
{
	$Ia=0;

	$arenas[$Ia]=$aArena['arenaId'];
	$Ia++;
}			

echo ", \"Arenas\":".json_encode($arenas);

echo "}";
mysql_close();

?>
	