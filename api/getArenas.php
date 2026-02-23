<?php 
require '../scriptsphp/defenvvar.php';

$username = null;

if(isset($_POST['username'])){
	$username =$_POST['username'];
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, "utf8");
mysqli_query($conn, "
SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')
");

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

if (($username !== 0) && ($username != null)) {


	$retour = mysqli_query($conn, "SELECT 
		 TableArena.arenaId,
		 TableArena.nomArena,
		 TableArena.nomGlace,
		 TableArena.tailleGlace
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
						GROUP BY TableArena.arenaId, TableArena.nomArena, TableArena.nomGlace, TableArena.tailleGlace
						 	") or die(mysqli_error($conn));
							

	$lesArenas = array();


	while ($r = mysqli_fetch_assoc($retour)) {
		$unArena=array();
		$unArena['typeSurface'] = $r['tailleGlace'];
		$unArena['plateauId'] = $r['arenaId'];
		$unArena['nomEmplacement'] = $r['nomArena'];
		$unArena['nomSurface'] = $r['nomGlace'];
		array_push($lesArenas,$unArena);
	}
	
	echo json_encode($lesArenas);
							
							
							
							
} 
//mysqli_close($conn);
		//header("HTTP/1.1 200 OK");
?>

