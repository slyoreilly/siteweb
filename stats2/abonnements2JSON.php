<?php

require '../scriptsphp/defenvvar.php';



////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

$ligueIdInter =null;
if(isset($_GET['ligueId'])){
$ligueIdInter = $_GET['ligueId'];}
$userId = $_GET['userId'];

$ligueId = $ligueIdInter;

//if(is_numeric($ligueIdInter)&&!is_null($ligueIdInter))
//{$ligueId = trouveIDParNomLigue($ligueIdInter);}
//if(!is_numeric($ligueIdInter)&&!is_null($ligueIdInter))
//{$ligueId = $ligueIdInter;}

if (is_numeric($ligueId)) {
	$resultEquipe = mysqli_query($conn,"SELECT * FROM AbonnementLigue WHERE ligueid='{$ligueId}' AND contexte='ligue'") or die(mysqli_error($conn));

	$boule = 0;
	while ($rangee = mysqli_fetch_array($resultEquipe)) {
		$boule = 1;
		$JSONstring = "{\"userId\": \"" . $rangee['userid'] . "\",";
		$JSONstring .= "\"type\": \"" . $rangee['type'] . "\",";
		$JSONstring .= "\"ligueId\": \"" . $rangee['ligueid'] . "\"}";
	}
	if ($boule == 0) {
		$JSONstring = "{\"userId\": \"null\",";
		$JSONstring .= "\"type\": \"30\",";
		$JSONstring .= "\"ligueId\": \"null\"}";

	}
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
mysqli_close($conn);
?>

