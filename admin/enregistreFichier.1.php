<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$refId = $_POST['refId'];
$contexte = $_POST['contexte'];

if (!isset($_POST['refId'])){
    $refId =0;
}
if (!isset($_POST['contexte'])){
    $contexte ='equipe';
}


if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
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

$fp      = fopen($tmpName, 'r');
$content = fread($fp, filesize($tmpName));
$content = addslashes($content);
fclose($fp);

if(!get_magic_quotes_gpc())
{
    $fileName = addslashes($fileName);
}

$query = "INSERT INTO TableFichier (contexte, idRef , name, size, type, content ) ".
"VALUES ('{$contexte}','{$refId}','{$fileName}', '{$fileSize}', '{$fileType}', '{$content}')";

mysql_query($query) or die("Erreur: "+$query+"\n"+mysql_error());

$querySel = "SELECT ficId FROM TableFichier WHERE 1 ORDER BY ficId DESC ";
$retSel = mysql_query($querySel) or die("Erreur: "+$querySel+"\n"+mysql_error());
$are = mysql_fetch_row($retSel);
echo $are[0];

}
else echo 0;

//////////////////////////////////
//
//	Les queries
//


//include 'library/closedb.php';
	
?>
