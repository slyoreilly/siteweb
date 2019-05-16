<?php

$source=$_POST['source'];
$fichiers=json_decode($_POST['fichiers']);

$options = array('ftp' => array('overwrite' => true));
$stream = stream_context_create($options);

for($a=0;$a<count($fichiers);$a++){
    file_put_contents($_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."lookatthis".DIRECTORY_SEPARATOR.$fichiers[$a], fopen($source.DIRECTORY_SEPARATOR."lookatthis".DIRECTORY_SEPARATOR.$fichiers[$a], 'r'), 0, $stream);
}

 
 

	
?>
