<?php
require '../scriptsphp/defenvvar.php';


$time = $_POST['time'];
$location = $_POST['location'];
$game = $_POST['game'];
$cam = $_POST['cam'];
$fileName = $_POST['fileName'];


// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");


/*

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


*/

	$qSel="SELECT * FROM Video 
		WHERE nomMatch='{$game}' AND camId='{$cam}' AND nomFichier='{$fileName}'";	
	$retSel=mysqli_query($conn,$qSel) or die("Erreur: "+$qSel+"\n"+mysqli_error($conn));
		if(mysqli_num_rows($retSel)==0){
    $query = "INSERT INTO Video (nomFichier,nomMatch,chrono,camId,type,reference,emplacement) ".
    "VALUES ('{$fileName}','{$game}','{$time}','{$cam}',10000,null,'{$location}')";
    mysqli_query($conn,$query) or die("Erreur: "+$query+"\n"+mysqli_error($conn));
 
            
				
        }
        
/*
        
        $options = array('ftp' => array('overwrite' => true));
        $stream = stream_context_create($options);
        
        //for($a=0;$a<count($fichiers);$a++){
            file_put_contents('..'.DIRECTORY_SEPARATOR."vidsintegraux".DIRECTORY_SEPARATOR.$fileName, $content, 0, $stream);
        //}
        
}     

echo var_dump($_FILES);
*/
	mysqli_close($conn);
?>
