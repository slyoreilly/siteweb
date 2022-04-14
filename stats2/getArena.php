<?php 
require '../scriptsphp/defenvvar.php';

$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
$arenaId =null; 
$ligueId = null;
$username = null;

if(isset($_POST['arenaId'])){
	$arenaId =$_POST['arenaId'];
}
if(isset($_POST['ligueId'])){
	$ligueId =$_POST['ligueId'];
}
if(isset($_POST['username'])){
	$username =$_POST['username'];
}

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, "utf8");
mysqli_query($conn, "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY','')");

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
$strRetour = $arenaId;
//echo ($username != 0)."Blip". ($username != undefined);
if (($username !== 0) && ($username != null)) {


	$retour = mysqli_query($conn, "SELECT /*AbonnementLigue.type,
	AbonnementLigue.ligueId,
	 	
		 abonLigueArena.gabaritId,*/
		 TableArena.arenaId,
		 TableArena.nomArena,
		 TableArena.nomGlace/*,
		 Gabarits.nomGabarit	*/	 
						FROM TableUser
						LEFT JOIN AbonnementLigue
							ON (TableUser.noCompte=AbonnementLigue.userid)
						INNER JOIN abonLigueArena
							ON (AbonnementLigue.ligueid=abonLigueArena.ligueId)
						LEFT JOIN Gabarits
							ON (abonLigueArena.gabaritId= Gabarits.gabaritId)
						LEFT JOIN TableArena
							ON (abonLigueArena.arenaId=TableArena.arenaId)
						WHERE username='{$username}' 
							AND abonLigueArena.finAbon>Now() 
							AND abonLigueArena.debutAbon<Now()  
						group by TableArena.arenaId
						 	") or die(mysqli_error($conn));
							
	$strRetour .= mysqli_num_rows($retour);

	$vecMatch = array();
	$IA=0;
	while ($r = mysqli_fetch_assoc($retour)) {
		//$vecMatch[$IA]['type'] = $r['type'];
		//$vecMatch[$IA]['ligueId'] = $r['ligueId'];
		$vecMatch[$IA]['arenaId'] = $r['arenaId'];
		//$vecMatch[$IA]['gabaritId'] = $r['gabaritId'];
		//$vecMatch[$IA]['nomGabarit'] = $r['nomGabarit'];
		$vecMatch[$IA]['nomArena'] = $r['nomArena'];
		$vecMatch[$IA]['nomGlace'] = $r['nomGlace'];
		/*if($vecMatch[$IA]['gabaritId']!=null){
			$qGab = "SELECT  positionGabarits.posX, positionGabarits.posY, positionGabarits.posGabId,  positionGabarits.role
			FROM positionGabarits
				WHERE gabaritId='{$vecMatch[$IA]['gabaritId']}'	";
			$retGab =mysqli_query($conn, $qGab) or die (mysqli_error($conn));
			$IPG=0;
			$vecMatch[$IA]['gabaritId']=array();
			while ($rangGab = mysqli_fetch_assoc($retGab)) {
				$vecMatch[$IA]['gabaritId'][$IPG]['posGabId'] = $rangGab['posGabId'];
				$vecMatch[$IA]['gabaritId'][$IPG]['posX'] = $rangGab['posX'];
				$vecMatch[$IA]['gabaritId'][$IPG]['posY'] = $rangGab['posY'];
				$vecMatch[$IA]['gabaritId'][$IPG]['role'] = $rangGab['role'];
				$IPG++;
			}

		}*/
		$IA++;
	}
	$vecMatch2 = utf8ize($vecMatch);
	$adomper = json_encode($vecMatch2);
	$adomper = str_replace('"[', '[', $adomper);
	$adomper = str_replace(']"', ']', $adomper);
	echo utf8_encode($adomper);
							
							
							
							
} else {

	if ($ligueId != 0 && $ligueId != null) {
		
		$retour = mysqli_query($conn,"SELECT TableArena.*	
						FROM TableArena
						LEFT JOIN abonLigueArena
							ON (TableArena.arenaId=abonLigueArena.arenaId)
						 WHERE abonLigueArena.ligueId='{$ligueId}'
						 	") or die(mysqli_error($conn));
	} else {$retour = mysqli_query($conn,"SELECT TableArena.*	
						FROM TableArena WHERE 1
						 	") or die(mysqli_error($conn));

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

