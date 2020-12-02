<?php

$channel ="rpi01"; 
Shell_exec("(php-cgi -f /var/www/html/scriptserveur/stream.php channel=".$channel." | ffmpeg -i - -r 12 -s 1280x720 -vb 1000k -f ogg - | oggfwd -p -n 'Mon premier fax' 192.168.1.99 8000 syncstats /".$channel.")")





?>
