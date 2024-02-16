<?php
////////////////////////////////////////////////////////////
//
//	getMatchsAVenir.php
//	Est appellé dans SyncAdapter.kt
//
//
////////////////////////////////////////////////////////////


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
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");
	
$username = $_POST['username'];

$qString = "SELECT TableMatch.*, Presences.*
            FROM TableMatch 
            JOIN abonEquipeLigue 
                ON abonEquipeLigue.ligueId = TableMatch.ligueRef 
            JOIN AbonnementLigue 
                ON abonEquipeLigue.ligueId = AbonnementLigue.ligueid 
            JOIN TableUser 
                ON AbonnementLigue.userId = TableUser.noCompte 
            LEFT JOIN Presences
                ON TableMatch.match_id = Presences.matchId
            WHERE TableUser.username = '{$username}'  
                AND abonEquipeLigue.finAbon > NOW() 
                AND TableMatch.date > (NOW() - INTERVAL 1 DAY) 
                AND TableMatch.date < (NOW() + INTERVAL 7 DAY) 
                AND (TableMatch.eq_dom = abonEquipeLigue.equipeId 
                    OR TableMatch.eq_vis = abonEquipeLigue.equipeId) 
            GROUP BY TableMatch.match_id, Presences.presenceId";

$retour = mysqli_query($conn,$qString) or die(mysqli_error($conn));    

$vecMatch = array();
$vecMatchCompose = array();
$matchIds = array();
while($r = mysqli_fetch_array($retour,MYSQLI_ASSOC)) {
    $matchId = $r['match_id'];
    if (!in_array($matchId, $matchIds)) {
        $unMatchCompose= array();
        $unMatch= array();
        $unMatch['GameComId']=$r['match_id'];
        $unMatch['matchLongId']=$r['matchIdRef'];
        $unMatch['eqDom']=$r['eq_dom'];
        $unMatch['eqVis']=$r['eq_vis'];
        $unMatch['date']=strtotime($r['date'])*1000;
        $unMatch['ligueId']=$r['ligueRef'];
        $unMatch['arenaId']=$r['arenaId'];
        $unMatch['scoreDom']=$r['score_dom'];
        $unMatch['scoreVis']=$r['score_vis'];
        $unMatch['cleValeur']=$r['cleValeur'];
        if($r['statut'] ==null){
            $unMatch['etat'] = 20;
        } elseif(is_numeric($r['statut'])){
            $unMatch['etat'] = 10;
        } elseif('F'==$r['statut']){
            $unMatch['etat'] = 30;
        } else{
            $unMatch['etat'] = 40;
        }

    // requête pour récupérer les présences de l'équipe domicile
    $query = "SELECT * FROM Presences WHERE matchId = '{$r['match_id']}' AND domVis = 1";
    $result = mysqli_query($conn, $query);
    $alignementDom = array();
    while ($presence = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $alignementDom[] = $presence;
    }
    
    // requête pour récupérer les présences de l'équipe visiteur
    $query = "SELECT * FROM Presences WHERE matchId = '{$r['match_id']}' AND domVis = 2";
    $result = mysqli_query($conn, $query);
    $alignementVis = array();
    while ($presence = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $alignementVis[] = $presence;
    }
    
    // créer l'objet $unMatchCompose contenant les informations du match et les présences
    $unMatchCompose = array(
        'match' => $unMatch,
        'alignementDom' => $alignementDom,
        'alignementVis' => $alignementVis
    );
    
    // ajouter l'objet $unMatchCompose au tableau $vecMatchCompose
    array_push($vecMatchCompose, $unMatchCompose);
    array_push($matchIds, $matchId);
        }
}

mysqli_close($conn);

echo json_encode($vecMatchCompose);
	


?>
