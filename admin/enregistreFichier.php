<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';
$contexte = 'equipe';


if (isset($_POST['contexte'])){
    $contexte =$_POST['contexte'];
}
$contexte = 'equipe';

$refId = 0;
if (isset($_POST['refId'])){
    $refId =$_POST['refId'];
}

	
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

if($_FILES['userfile']['size'] > 0)
{
$fileName = $_FILES['userfile']['name'];
$tmpName  = $_FILES['userfile']['tmp_name'];
$fileSize = $_FILES['userfile']['size'];
$fileType = $_FILES['userfile']['type'];

//$fp      = fopen($tmpName, 'r');
$content = addslashes(file_get_contents($tmpName));
//$content = fread($fp, filesize($tmpName));
//fclose($fp);


$query = "INSERT INTO TableFichier (contexte, idRef , name, size, type, content ) ".
"VALUES ('{$contexte}', '{$refId}','{$fileName}', '{$fileSize}', '{$fileType}', '{$content}')";

mysqli_query($conn,$query) or die("Erreur: ".$query."\n".mysqli_error($conn));

$querySel = "SELECT ficId FROM TableFichier WHERE 1 ORDER BY ficId DESC ";
$retSel = mysqli_query($conn,$querySel) or die("Erreur: ".$querySel."\n".mysqli_error($conn));
$are = mysqli_fetch_row($retSel);
echo $are[0];

}
else echo 0;

//////////////////////////////////
//
//	Les queries
//


//include 'library/closedb.php';
	
mysqli_close($conn);

?>
