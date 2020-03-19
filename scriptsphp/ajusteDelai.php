
<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////


$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection/
if (!$conn) {
	error_log("Connection failed: " . mysqli_connect_error());
   die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");


$fic=$_POST["fic"];
$cam=$_POST["cam"];
$delai=$_POST["delai"];



	$retour=array();

$qSel="SELECT * FROM StatutCam 
		 WHERE camId='{$cam}'  LIMIT 1";
$resCam=mysqli_query($conn,$qSel) or die(mysqli_error($conn).' damn');
while($rangCam=mysqli_fetch_array($resCam)){


	$qCon="SELECT * FROM Controle 
	WHERE telId='{$rangCam['telId']}' AND arg0='videos' AND arg1='recut' AND arg2= '{$fic}' and etatSync='3' ";

	$resCon=mysqli_query($conn,$qCon) or die(mysqli_error($conn).' damn');
	if(mysqli_num_rows($resCon)>0){
		while($rangCon=mysqli_fetch_array($resCon)){
			$queryMod = "UPDATE Controle SET valeur = '{$delai}'
							WHERE  telId='{$rangCam['telId']}' AND arg0='videos' AND arg1='recut' AND arg2= '{$fic}' and etatSync=3";
			mysqli_query($conn,$queryMod) or die("Erreur: "+$queryMod+"\n"+mysqli_error($conn));
		}
	}else{

		$ajouteBut =mysqli_query($conn,"INSERT INTO Controle (`telId`, `arg0`, `arg1`, `arg2`, `valeur`, `cleValeur`, `etatSync`) 
		VALUES ('{$rangCam['telId']}','videos','recut','{$fic}','{$delai}',NULL, 3)");

	}


}

mysqli_close($conn);
?>
