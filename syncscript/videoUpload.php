<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';


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
$contentEsc = mysql_real_escape_string($content);
fclose($fp);

if(!get_magic_quotes_gpc())
{
    $fileName = addslashes($fileName);
}


 $options = array('ftp' => array('overwrite' => true));
 $stream = stream_context_create($options);
 
 file_put_contents('../lookatthis/'.$fileName, $content, 0, $stream); 

}
else echo "fichier grosseur nulle?";

//////////////////////////////////
//
//	Les queries
//


//include 'library/closedb.php';
	
?>
