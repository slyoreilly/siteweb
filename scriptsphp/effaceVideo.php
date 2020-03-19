
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

$lien1=mysql_connect($db_host, $db_user, $db_pwd);
if (!$lien1)
    die("Can't connect to database");
mysql_set_charset('utf8',$lien1);

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}

$numDelFiles=0;
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

$fic=$_POST["fic"];






	if(@unlink(realpath(__DIR__.'/../lookatthis/'.$fic))){$numDelFiles++;}
else{echo __DIR__.'/../lookatthis/'.$fic;
}

		

$qDel="DELETE FROM Video
		 WHERE nomFichier='{$fic}'  LIMIT 1";
$resCam=mysql_query($qDel) or die(mysql_error().' damn');

echo "Number of files deleted: ".$numDelFiles;

?>
