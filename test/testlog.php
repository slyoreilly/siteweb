
<?php
$message = "ceci est un test";

$log  = $message.' - '.date("F j, Y, g:i a").PHP_EOL.
        "-------------------------".PHP_EOL;
//Save string to log, use FILE_APPEND to append.
file_put_contents('./logTest.txt', $log, FILE_APPEND);

?>
