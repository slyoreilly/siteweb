

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
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

/////////////////////////////////////////////////////////////
// 
//


$premierVideo =  isset($_POST['premierVideo'])? $_POST['premierVideo']:null;
$dernierVideo =  isset($_POST['dernierVideo'])? $_POST['dernierVideo']:null;
$fichiers =  isset($_POST['fichiers'])? $_POST['fichiers']:null;

//$mesFics = scandir( '../lookatthis/' , SCANDIR_SORT_ASCENDING);
$ficOrphelins = array();
$info= array();

if($premierVideo!=null && $dernierVideo!=null){
	$qFic = "SELECT nomFichier, nomMatch, emplacement 
				FROM Video 
				WHERE chrono>{$premierVideo} and chrono<{$dernierVideo}";
	$resFic = mysqli_query($conn, $qFic)or die(mysqli_error($conn) );
foreach ($fichiers as $unFichier) {
	$trouve=false;
	while($ligneFic=mysqli_fetch_assoc($resFic))
    	{
			$parts = explode('?',$ligneFic['nomFichier']);
			if(strcmp($parts[0],$unFichier)==0)
			{
				$trouve=true;	
				array_push($info,$parts[0]);	
			}
		}
	if($trouve==false){
		array_push($ficOrphelins,$unFichier);
	}
	mysqli_data_seek($resFic,0);
	
}
}
echo "ficOrphelin ".json_encode($ficOrphelins);
echo "info ".json_encode($info);


?>

