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

$qLigue = "SELECT Ligue.* 
	FROM Ligue 
	JOIN AbonnementLigue 
		ON AbonnementLigue.ligueId = Ligue.ID_Ligue 
	JOIN TableUser 
		ON AbonnementLigue.userId = TableUser.noCompte 
	WHERE TableUser.username = '{$username}' 
	GROUP BY Ligue.ID_Ligue ";



$retourLigue = mysqli_query($conn, $qLigue) or die(mysqli_error($conn));


$vecLiguesCompose = array();
$vecLiguesComposeAvecEquipes = array();

while ($rL = mysqli_fetch_array($retourLigue, MYSQLI_ASSOC)) {
    $uneLigue = array();
    $joueursDansLigue = array();
    $uneLigue['ligueId'] = $rL['ID_Ligue'];
    $uneLigue['sportId'] = $rL['sportId'];
    $uneLigue['nom'] = $rL['Nom_Ligue'];
    $uneLigue['lieu'] = $rL['Lieu'];
    $uneLigue['date'] = time() * 1000;
    $uneLigue['dernierMAJ'] = strtotime($rL['dernierMAJ']) * 1000;
    $uneLigue['horaire'] = $rL['Horaire'];
    $uneLigue['cleValeur'] = $rL['cleValeur'];
    $mLigueId = $uneLigue['ligueId'];

    $qString = "SELECT TableEquipe.* 
	FROM TableEquipe 
	JOIN abonEquipeLigue 
		ON abonEquipeLigue.equipeId = TableEquipe.equipe_id 
	WHERE abonEquipeLigue.ligueId = '{$mLigueId}' 
	AND abonEquipeLigue.debutAbon < Now()
			AND abonEquipeLigue.finAbon > Now()
	GROUP BY TableEquipe.equipe_id ";

    $retour = mysqli_query($conn, $qString) or die(mysqli_error($conn));
    $vecEquipes= array();
    $vecEquipesCompose = array();
    while ($r = mysqli_fetch_array($retour, MYSQLI_ASSOC)) {
        $uneEquipe = array();
        $uneEquipe['equipeId'] = $r['equipe_id'];
        $uneEquipe['ligueId'] = $mLigueId;
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
            if ($j['position'] == 'g' or $j['position'] == 'G') {
                $joueur['positionId'] = 5;
            } else {
                $joueur['positionId'] = 1;
            }
            $joueur['dernierMAJ'] = time() * 1000;
            $joueur['cleValeur'] = $j['cleValeur'];
            array_push($joueurs, $joueur);
            array_push($joueursDansLigue, $joueur);
        }
        $uneEquipeCompose = array(
            'equipe' => $uneEquipe,
            'joueurs' => $joueurs
        );


        array_push($vecEquipes, $uneEquipe);
        array_push($vecEquipesCompose, $uneEquipeCompose);
    }


    $retourJoueurs3 = mysqli_query($conn, "SELECT * 
                            FROM abonJoueurLigue
                                JOIN TableJoueur
                                    ON (TableJoueur.joueur_id=abonJoueurLigue.joueurId)
                                JOIN AbonnementLigue 
                            		ON AbonnementLigue.ligueId = abonJoueurLigue.ligueId 
                            	JOIN TableUser 
                            		ON AbonnementLigue.userId = TableUser.noCompte 
                                WHERE TableUser.username = '{$username}' 
	                                AND abonJoueurLigue.debutAbon < Now()
			                        AND abonJoueurLigue.finAbon > Now()") or die(mysqli_error($conn));
    $joueurs = array();
    while ($j = mysqli_fetch_array($retourJoueurs3, MYSQLI_ASSOC)) {
        $joueur = array();
        $joueur['SyncKey'] = $j['joueur_id'];
        $joueur['nom'] = $j['NomJoueur'];
        if ($j['position'] == 'g' or $j['position'] == 'G') {
            $joueur['positionId'] = 5;
        } else {
            $joueur['positionId'] = 1;
        }
        $joueur['dernierMAJ'] = time() * 1000;
        if(isset($j['cleValeur'])){
            $joueur['cleValeur'] = $j['cleValeur'];
        }
        
        array_push($joueursDansLigue, $joueur);
    }


    $uneLigueCompose = array(
        'ligue'  => $uneLigue,
        'equipes' => $vecEquipes,
        'joueurs' => $joueursDansLigue
    );

    $uneLigueComposeAvecEquipes = array(
        'ligueCompose'  => $uneLigueCompose,
        'equipes' => $vecEquipesCompose
    );

    array_push($vecLiguesCompose, $uneLigueCompose);
    array_push($vecLiguesComposeAvecEquipes, $uneLigueComposeAvecEquipes);
}

echo json_encode($vecLiguesComposeAvecEquipes);

// Close connection
mysqli_close($conn);
