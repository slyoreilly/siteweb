<?php

/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
//
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';



////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

if (!mysql_connect($db_host, $db_user, $db_pwd))
	die("Can't connect to database");

if (!mysql_select_db($database)) {
	echo "<h1>Database: {$database}</h1>";
	die("Can't select database");

}

function trouveIDParNomLigue($ligue) {
	$fResultLigue = mysql_query("SELECT * FROM Ligue") or die(mysql_error());
	while ($fRangeeLigue = mysql_fetch_array($fResultLigue)) {
		if (!strcmp($fRangeeLigue['Nom_Ligue'], $ligue)) {$equipeID = $fRangeeLigue['ID_Ligue'];
			// Ce sont de INT
		}
	}
	return $equipeID;
}

$ligueIdInter = $_POST['ligueId'];
$userId = $_POST['userId'];

$ligueId = $ligueIdInter;

//if(is_numeric($ligueIdInter)&&!is_null($ligueIdInter))
//{$ligueId = trouveIDParNomLigue($ligueIdInter);}
//if(!is_numeric($ligueIdInter)&&!is_null($ligueIdInter))
//{$ligueId = $ligueIdInter;}

if (is_numeric($ligueId)) {
	$resultEquipe = mysql_query("SELECT * FROM AbonnementLigue WHERE ligueid='{$ligueId}' AND contexte='ligue'") or die(mysql_error());
	$vecUtilisateurs=Array();
	$boule = 0;
	while ($rangee = mysql_fetch_array($resultEquipe)) {
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
		$resultEquipe = mysql_query("SELECT * FROM AbonnementLigue
					JOIN TableUser
						ON(TableUser.noCompte=AbonnementLigue.userid)
					 WHERE username='{$userId}' AND contexte='ligue'") or die(mysql_error());
		$abon=array();
		$IA=0;
		while ($rangee = mysql_fetch_array($resultEquipe)) {
			$abon[$IA]['ligueId']=$rangee['ligueid'];
			$abon[$IA]['type']=$rangee['type'];
		}
		$JSONstring=json_encode($abon);
	}

}

echo $JSONstring;
?>

