<?php
require '../scriptsphp/defenvvar.php';

	
	//////////////////////////////////////////////////////////////////////
//
//	Partie upload file
//
/////////////////////////////////////////////////////////////////////
$paramsJSON = $_POST['params'];
//$heure = $_POST['heure'];
$params = json_decode($paramsJSON,true);
$mydate=getdate(date("U"));
//echo "$mydate[weekday], $mydate[month] $mydate[mday], $mydate[year]";
$dossier=$params['telId'].'/'. $mydate['year'].'_'.$mydate['mon'].'_'.$mydate['mday'];
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
//$contentEsc = mysqli_real_escape_string($content);
fclose($fp);


 $options = array('ftp' => array('overwrite' => true));
 $stream = stream_context_create($options);
 
 if (!is_dir('../monitoring/'.$dossier)) {
    mkdir('../monitoring/'.$dossier,0777,true);         
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


?>
