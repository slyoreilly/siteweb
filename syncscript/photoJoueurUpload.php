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

$joueur = json_decode(stripslashes($_POST['params']), true);


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

mysql_query($query) or die("Erreur: "+$query+"\n"+mysql_error());



$querySel = "SELECT ficId FROM TableFichier WHERE 1 ORDER BY ficId DESC ";
$retSel = mysql_query($querySel) or die("Erreur: "+$querySel+"\n"+mysql_error());

$are = mysql_fetch_row($retSel);

$query = "UPDATE TableJoueur SET dernierMAJ = now(), ficIdPortrait = '{$are[0]}'
			WHERE joueur_id='{$joueur['joueurId']}'";
	

mysql_query($query) or die("Erreur: "+$query+"\n"+mysql_error());




echo $are[0];

}
else echo 0;

//////////////////////////////////
//
//	Les queries
//


//include 'library/closedb.php';
	
?>
