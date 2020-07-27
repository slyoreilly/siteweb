<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$contexte = $_POST['contexte'];
// Create connection
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

if($_FILES['userfile']['size'] > 0)
{
$fileName = $_FILES['userfile']['name'];
$tmpName  = $_FILES['userfile']['tmp_name'];
$fileSize = $_FILES['userfile']['size'];
$fileType = $_FILES['userfile']['type'];

$fp      = fopen($tmpName, 'r');
$content = fread($fp, filesize($tmpName));
$content = addslashes($content);
fclose($fp);

if(!get_magic_quotes_gpc())
{
    $fileName = addslashes($fileName);
}

$query = "INSERT INTO TableFichier (contexte, idRef , name, size, type, content ) ".
"VALUES ('equipe', '0','{$fileName}', '{$fileSize}', '{$fileType}', '{$content}')";

mysqli_query($conn,$query) or die("Erreur: "+$query+"\n"+mysqli_error($conn));

$querySel = "SELECT ficId FROM TableFichier WHERE 1 ORDER BY ficId DESC ";
$retSel = mysqli_query($conn,$querySel) or die("Erreur: "+$querySel+"\n"+mysql_error());
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
