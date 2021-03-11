<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$joueur = json_decode(stripslashes($_POST['params']), true);


$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
	
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////

if($_FILES['fichier']['size'] > 0)
{
$fileName = $_FILES['fichier']['name'];
$tmpName  = $_FILES['fichier']['tmp_name'];
$fileSize = $_FILES['fichier']['size'];
$fileType = $_FILES['fichier']['type'];

$fp      = fopen($tmpName, 'r');
$content = fread($fp, filesize($tmpName));
$content = addslashes($content);
fclose($fp);

if(!get_magic_quotes_gpc())
{
    $fileName = addslashes($fileName);
}

$query = "INSERT INTO TableFichier (contexte, idRef , name, size, type, content ) ".
"VALUES ('joueur', '0','{$fileName}', '{$fileSize}', '{$fileType}', '{$content}')";

mysqli_query($conn,$query) or die("Erreur: "+$query+"\n"+mysqli_error($conn));



$querySel = "SELECT ficId FROM TableFichier WHERE 1 ORDER BY ficId DESC ";
$retSel = mysqli_query($conn,$querySel) or die("Erreur: "+$querySel+"\n"+mysqli_error($conn));

$are = mysqli_fetch_row($retSel);

$query = "UPDATE TableJoueur SET dernierMAJ = now(), ficIdPortrait = '{$are[0]}'
			WHERE joueur_id='{$joueur['joueurId']}'";
	

mysqli_query($conn,$query) or die("Erreur: "+$query+"\n"+mysqli_error($conn));




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
