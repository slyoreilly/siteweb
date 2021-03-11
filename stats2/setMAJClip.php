<?php require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
$strMAJ = $_POST['strMAJ'];


$clipMAJ = json_decode(stripslashes($strMAJ));
//echo stripslashes(json_encode($butMAJ));
//echo $butMAJ->matchId;


if (($clipMAJ -> nouveauClip)==true) {
  //  $resultBut = mysqli_query($conn,"SELECT * 
  //  FROM Clips
  //      WHERE matchId='{$clipMAJ->matchId}'
  //          AND code='0' ORDER BY chrono");
//	echo $_POST['strMAJ'];/
//	$cRow = 0;/
//	while ($row = mysqli_fetch_assoc($resultBut)) {/
//		$cRow++;/
//	}
//	$clipMAJ -> noSeq = $cRow;
//	$ajouteBut = mysqli_query($conn,"INSERT INTO Clips (`matchId`, `chrono`) 
//	VALUES ('{$clipMAJ->matchId}','{$clipMAJ->chrono}'");
	



} else {


    if (($clipMAJ -> deleteClip)==true) {


        mysqli_query($conn,"DELETE FROM Clips WHERE matchId='{$clipMAJ->matchId}'
        AND chrono='{$clipMAJ->chrono}'
        ");





    }

    else{


   //     echo stripslashes(json_encode($tabButs));

//        mysqli_query($conn,"UPDATE TableEvenement0 SET joueur_event_ref='{$butMAJ->marqueurId}' ,souscode ='{$sc}' WHERE match_event_id='{$butMAJ->matchId}'
 //                                                           AND code=0 AND chrono='{$tabButs['chrono']}'");
    
    }


	//echo "cpas: " . $cPas . "  sPas: " . $sPas . "  " . $butMAJ -> passeur1Id . "  " . $butMAJ -> passeur2Id;
	//echo " / rep insert Passeurs ".mysql_num_rows($retour);
}
echo 1;
mysqli_close($conn);
//echo $tabButs[$butMAJ->noSeq]->chrono;
?>

