
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


$numDelFiles=0;


$fic=$_POST["fic"];






	if(@unlink(realpath(__DIR__.'/../lookatthis/'.$fic))){$numDelFiles++;}
else{echo __DIR__.'/../lookatthis/'.$fic;
}

		

$qDel="DELETE FROM Video
		 WHERE nomFichier='{$fic}'  LIMIT 1";
$resCam=mysqli_query($conn,$qDel) or die(mysqli_error($conn).' damn');

echo "Number of files deleted: ".$numDelFiles;
//mysqli_close($conn);

?>
