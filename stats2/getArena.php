<?php $db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
$mavId = $_POST['arenaId'];
$ligueId = $_POST['ligueId'];
$username = $_POST['username'];

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, "utf8");

function utf8ize($mixed) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } else if (is_string ($mixed)) {
        return utf8_encode($mixed);
    }
    return $mixed;
}
//$jDom = json_decode($jDomJSON, true);
//$jVis = json_decode($jVisJSON, true);
$strRetour .= $mavId;
//echo ($username != 0)."Blip". ($username != undefined);
if (($username !== 0) && ($username != undefined) && ($username != null)) {


	$retour = mysqli_query($conn, "SELECT *	
						FROM TableUser
						LEFT JOIN AbonnementLigue
							ON (TableUser.noCompte=AbonnementLigue.userid)
						LEFT JOIN abonLigueArena
							ON (AbonnementLigue.ligueid=abonLigueArena.ligueId)
						LEFT JOIN TableArena
							ON (abonLigueArena.arenaId=TableArena.arenaId)
						WHERE username='{$username}' group by TableArena.arenaId
						 	") or die(mysqli_error());
							
	$strRetour .= mysqli_num_rows($retour);

	$vecMatch = array();
	while ($r = mysqli_fetch_assoc($retour)) {
		$vecMatch[] = $r;
	}
	$adomper = json_encode($vecMatch);
	$adomper = str_replace('"[', '[', $adomper);
	$adomper = str_replace(']"', ']', $adomper);
	echo utf8_encode($adomper);
							
							
							
							
} else {

	if ($ligueId != 0 && $ligueId != undefined) {
		
		$retour = mysqli_query($conn,"SELECT TableArena.*	
						FROM TableArena
						LEFT JOIN abonLigueArena
							ON (TableArena.arenaId=abonLigueArena.arenaId)
						 WHERE abonLigueArena.ligueId='{$ligueId}'
						 	") or die(mysqli_error());
	} else {$retour = mysqli_query($conn,"SELECT TableArena.*	
						FROM TableArena WHERE 1
						 	") or die(mysqli_error());

	}
	$strRetour .= mysqli_num_rows($retour);

	$vecMatch = array();
	while ($r = mysqli_fetch_assoc($retour)) {
		$vecMatch[] = $r;
	}
	$vecMatch2 = utf8ize($vecMatch);
	$adomper = json_encode($vecMatch2);
	//echo json_last_error();
	//$adomper = str_replace('"[', '[', $adomper);
	//$adomper = str_replace(']"', ']', $adomper);
	echo utf8_encode($adomper);
}
mysqli_close($conn);
		//header("HTTP/1.1 200 OK");
?>

