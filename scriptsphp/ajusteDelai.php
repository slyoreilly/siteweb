
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



$fic=$_POST["fic"];
$cam=$_POST["cam"];
$delai=$_POST["delai"];



	$retour=array();

$qSel="SELECT * FROM StatutCam 
		 WHERE camId='{$cam}'  LIMIT 1";
$resCam=mysqli_query($conn,$qSel) or die(mysqli_error($conn).$qSel.' damn');
while($rangCam=mysqli_fetch_array($resCam)){


	$qCon="SELECT * FROM Controle 
	WHERE telId='{$rangCam['telId']}' AND arg0='videos' AND arg1='recut' AND arg2= '{$fic}' and etatSync='3' ";

	$resCon=mysqli_query($conn,$qCon) or die(mysqli_error($conn).$qCon.' damn');
	if(mysqli_num_rows($resCon)>0){
		while($rangCon=mysqli_fetch_array($resCon)){
			$queryMod = "UPDATE Controle SET valeur = '{$delai}'
							WHERE  telId='{$rangCam['telId']}' AND arg0='videos' AND arg1='recut' AND arg2= '{$fic}' and etatSync=3";
			mysqli_query($conn,$queryMod) or die("Erreur: ".$queryMod."\n".mysqli_error($conn));
		}
	}else{
		$qAjoute="INSERT INTO Controle (`telId`, `arg0`, `arg1`, `arg2`, `valeur`, `cleValeur`, `etatSync`) 
		VALUES ('{$rangCam['telId']}','videos','recut','{$fic}','{$delai}',NULL, 3)";
		$ajouteBut =mysqli_query($conn,$qAjoute)or die("Erreur: ".$qAjoute."\n".mysqli_error($conn));

	}


}

//mysqli_close($conn);
?>
