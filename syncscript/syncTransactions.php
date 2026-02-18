<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

header ('Content-type: text/html; charset=utf-8'); 


$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$username = $_POST['username'];
//$password = $_POST['password'];
//$matchjson = stripslashes($_POST['matchjson']);
$matchjson = stripslashes($_POST['matchjson']);


	

//$json=json_decode("'".$matchjson."'");
$leMatch = json_decode($matchjson, true);
//$leMatch = json_decode(utf8_encode($matchjson),true);
//$leMatch2 = utf8_decode($matchjson);
//$leMatch2 = json_encode(json_decode(safeJSON_chars($matchjson)), true);
//$leMatch2 = json_encode(json_decode($matchjson));
//$leMatch3 = $matchjson;
//echo json_encode(array_map( function($t){ return is_string($t) ? utf8_encode($t) : $t; }, $matchjson) );

//echo $leMatch2."\n";
//echo $leMatch3."\n";
//var_dump(json_decode($matchjson,true));


	$intEquipe = $leMatch['equipeId'];
	$intLigue = $leMatch['ligueId'];
	$intJoueur =  mysqli_real_escape_string($conn, $leMatch['nomJoueur']);
	$intNo = $leMatch['noJoueur'];
	$intNomEq = $leMatch['equipe'];
	$vieuId = $leMatch['vieuId'];

	$q1 ="INSERT INTO {$tableJoueur} (NomJoueur, NumeroJoueur, equipe_id_ref, Ligue,ficIdPortrait) 
VALUES ('{$intJoueur}', '{$intNo}', NULL, NULL,95)";
	
$retour = mysqli_query($conn,$q1)or die(mysqli_error($conn)."insert bug  ".$q1);	
//	mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono, match_event_id) 
//VALUES ( 'test	Match2', 'testMatch2', 'testMatch2', 'testMatch2','testMatch2')");	
	
	$q2="SELECT joueur_id FROM {$tableJoueur} WHERE Nomjoueur='{$intJoueur}' AND NumeroJoueur='{$intNo}' ORDER BY joueur_id DESC";
	
	$resultNouveau = mysqli_query($conn,$q2)
				or die(mysqli_error($conn)."select bug  ".$q2);  
	
	$nId = mysqli_data_seek($resultNouveau,0);
		$JSONstring = 	"{\"vieuId\": \"".$vieuId."\",";
		$JSONstring .= 	"\"nouveauId\": \"".$nId[0]."\"}";

//$retour = mysql_query("INSERT INTO abonJoueurEquipe (joueurId, equipeId, permission, debutAbon, finAbon) 
//VALUES ('{$nId[0]}', '{$intEquipe}',30, NOW(),'2050-01-01')");	
$retour = mysqli_query($conn,"INSERT INTO abonJoueurLigue (joueurId, ligueId, permission, debutAbon, finAbon) 
VALUES ('{$nId[0]}', '{$intLigue}',30, NOW(),'2030-01-01')") or die(mysqli_error($conn)."insert bug  ");  	

if($intEquipe!=0)
{
$retour = mysqli_query($conn,"INSERT INTO abonJoueurEquipe (joueurId, equipeId, permission, debutAbon, finAbon) 
VALUES ('{$nId[0]}', '{$intEquipe}',30, NOW(),'2030-01-01')") or die(mysqli_error($conn)."insert bug  2");  	
}
	
		echo $JSONstring;

		//mysqli_close($conn);
//		echo "".json_last_error();
			header("HTTP/1.1 200 OK");
?>
