
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

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

$numDelFiles=0;


$fic=$_POST["fic"];






	if(@unlink(realpath(__DIR__.'/../lookatthis/'.$fic))){$numDelFiles++;}
else{echo __DIR__.'/../lookatthis/'.$fic;
}

		

$qDel="DELETE FROM Video
		 WHERE nomFichier='{$fic}'  LIMIT 1";
$resCam=mysqli_query($conn,$qDel) or die(mysqli_error($conn).' damn');

echo "Number of files deleted: ".$numDelFiles;
mysqli_close($conn);

?>
