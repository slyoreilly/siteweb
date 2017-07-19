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
$paramsJSON = $_POST['params'];
$heure = $_POST['heure'];
$params = json_decode($paramsJSON,true);
$dossier=$params['telId'];
if($dossier==null){
	$dossier = 'LostNfound';
}

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
 
 if (!is_dir($dossier)) {
    mkdir('../monitoring/'.$dossier,0777);         
}
 
 //file_put_contents('../monitoring/'.$dossier.'/'.$fileName,'+-+-+',0,$stream); 
//file_put_contents('../monitoring/'.$dossier.'/'.$fileName,'---**----'.$paramsJSON.'HHHH'.$heure.'/*/*/'); 
 file_put_contents('../monitoring/'.$dossier.'/'.$fileName, $content, 0, $stream); 

}
else echo "fichier grosseur nulle?";
echo "dossier: ".$dossier;
//////////////////////////////////
//
//	Les queries
//


//include 'library/closedb.php';
	
?>
