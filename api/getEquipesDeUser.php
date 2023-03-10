<?php
////////////////////////////////////////////////////////////
//
//	getEquipesDeUser.php
//	Est appelé dans equipeRepository.kt
//
//
////////////////////////////////////////////////////////////


require '../scriptsphp/defenvvar.php';


////////////////////////////////////////////////////////////
//
// 	Connection à la base de données
//
////////////////////////////////////////////////////////////


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

$username = $_POST['username'];

$qString = "SELECT TableEquipe.* 
	FROM TableEquipe 
	JOIN abonEquipeLigue 
		ON abonEquipeLigue.equipeId = TableEquipe.equipe_id 
	JOIN AbonnementLigue 
		ON AbonnementLigue.ligueId = abonEquipeLigue.ligueId 
	JOIN TableUser 
		ON AbonnementLigue.userId = TableUser.noCompte 
	JOIN abonJoueurEquipe 
		ON abonJoueurEquipe.equipeId = TableEquipe.equipe_id 
	WHERE TableUser.username = '{$username}' 
	AND abonEquipeLigue.debutAbon < Now()
			AND abonEquipeLigue.finAbon > Now()
	GROUP BY TableEquipe.equipe_id ";


$retour = mysqli_query($conn, $qString) or die(mysqli_error($conn));

$vecEquipes = array();

while ($r = mysqli_fetch_array($retour, MYSQLI_ASSOC)) {
    $uneEquipe = array();
    $uneEquipe['equipeId'] = $r['equipe_id'];
    $uneEquipe['ligueId'] = $r['ligue_equipe_ref'];
    $uneEquipe['nom'] = $r['nom_equipe'];
    $uneEquipe['couleur1'] = $r['couleur1'];
    if ($uneEquipe['couleur1'] === null) { // Si la valeur est nulle
        $uneEquipe['couleur1'] = 'FFFFFF'; // On affecte la valeur par défaut
    }
    $uneEquipe['dernierMAJ'] = strtotime($r['dernierMAJ']) * 1000;
    $uneEquipe['cleValeur'] = $r['cleValeur'];
    
    // Recherche des joueurs de l'équipe
    $qJoueurs = "SELECT TableJoueur.* 
	FROM TableJoueur 
	JOIN abonJoueurEquipe 
	ON abonJoueurEquipe.joueurId = TableJoueur.joueur_id 
		WHERE abonJoueurEquipe.equipeId = '{$uneEquipe['equipeId']}'
		AND abonJoueurEquipe.debutAbon < Now()
			AND abonJoueurEquipe.finAbon > Now()";
    $retourJoueurs = mysqli_query($conn, $qJoueurs) or die(mysqli_error($conn));
    $joueurs = array();
    while ($j = mysqli_fetch_array($retourJoueurs, MYSQLI_ASSOC)) {
        $joueur = array();
        $joueur['SyncKey'] = $j['joueur_id'];
        $joueur['nom'] = $j['NomJoueur'];
		if($j['position']=='g' OR $j['position']=='g'){
			$joueur['positionId'] = 5;
		}else{
			$joueur['positionId'] = 1;
		}
        $joueur['dernierMAJ'] = NOW();
        $joueur['cleValeur'] = $j['cleValeur'];
        array_push($joueurs,$joueur);
	}
    $uneEquipeCompose = array(
        'equipe' => $uneEquipe,
        'joueurs' => $joueurs
    );
	array_push($vecEquipes, $uneEquipeCompose);
}

echo json_encode($vecEquipes);

// Close connection
mysqli_close($conn);

?>