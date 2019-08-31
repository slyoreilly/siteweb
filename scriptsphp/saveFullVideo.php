<?php
$file = $_FILES['userfile'];

    $fileName = $file['name'];
    $tmpName  = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];
    
    $fp      = fopen($tmpName, 'r');
    $content = fread($fp, filesize($tmpName));
    //$contentEsc = mysql_real_escape_string($content);
    fclose($fp);
    
    if(!get_magic_quotes_gpc())
    {
        $fileName = addslashes($fileName);
    }
    
    //error_log("preStream ".$_FILES['fichier']['name'], 0);
     $options = array('ftp' => array('overwrite' => true));
     $stream = stream_context_create($options);
    // error_log("postStream ".$_FILES['fichier']['name'], 0);
     $retByte =file_put_contents($_SERVER["DOCUMENT_ROOT"]."/vidsintegraux/".$fileName, $content, 0, $stream); 





	
?>
