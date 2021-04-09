<?php

/**
 * Command line script which takes files in a folder and reads them as they arrive
 * 
 * It is used essentially to assemble MPEGTS in order to pipe
 * the output in FFMPEG and then convert this input to OGG for example
 * 
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3
 * @package raspistream
 * @see http://www.tmplab.org/wiki/index.php/Streaming_Video_With_RaspberryPi
 * @author Alban Crommer
 * @copyright (c) 2015
 * @version 1.0
 */


// Defines how many digits your FFMPEG segments have
// Ex: "/tmp/capture/out-0001.ts" => 4
$digits                         = 10;
// Defines the path to your segments to merge
// Ex: "/tmp/capture/out-0001.ts" => "/tmp/capture"
$path                           = "/mnt/syncstats/";
// Defines a prefix for your segments ex "out-0001.ts"
// Ex: "/tmp/capture/out-0001.ts" => "out-"
$prefix                         = "";
// Defines the extension of your segments ex "0001.ts"
// Ex: "/tmp/capture/out-0001.ts" => "ts"
$extension                      = "ts";
// Defines a log file
$log_file                       = "/var/log/syncstats/ffmpeg.log";
// Defines a number of files to send immediately if available for buffer
$buffer_size                    = 5;


/**
 * 
 * @global int $digits
 * @global string $prefix
 * @global string $extension
 * @global string $path
 * @param type $id
 * @param string $path
 * @return type
 */
function getChunkName($id, $path = ""){

    global $digits;
    global $prefix;
    global $extension;
    global $path;
    return $path.$prefix.str_pad($id, $digits, "0", STR_PAD_LEFT).".".$extension;

}
/**
 * 
 * @global string $log_file
 * @param type $data
 */
function writeLog( $data ){
    global $log_file;
    file_put_contents( $log_file, $data."\n", FILE_APPEND);
}
/**
 * 
 * @param type $file_path
 */
function displayChunk( $file_path ){
    writeLog($file_path);
    if( is_file ( $file_path ) ){
        readfile( $file_path );
    }else{
        writeLog( "ERROR ! Invalid path ".$file_path );
    }
}

function getFileList($path){

// Attempt to read the directory content
$fileList                       = array();
$current                        = 0;
$d                              = opendir($path);
if ( ! $d) {
    die( "Failed to load source directory $path content" );
}
while (($c                      = readdir($d))!==false) {
        if (is_file($path."/".$c)) {
                $c              = intval($c);
                $fileList[]     = $c;
        }
}
closedir($d);

sort($fileList);


    return $fileList;
}


function getCurrent($fileList){

    // Attempt to parse the file list and determine the current chunk
$current                        = 0;
if( count($fileList) > 1 ){
    $current                    = $fileList[count($fileList)-1];
}



return $current;
}



function getBufferFileList($fileList,$buffer_size){

    if( count($fileList) <  $buffer_size ){
        $buffer_size                = count( $fileList);
    }
    $fileList                       = array_slice($fileList,count($fileList)-$buffer_size,$buffer_size-1);

    return $fileList;
}

function fillingBuffer($fileList){

// Read the X latest files (fill the buffer in ...)
writeLog("Filling the buffer");
foreach($fileList as $k) {
    displayChunk( getChunkName($k));
}

}

// Script start
$fileList                       = getFileList($path);
$current                        = getCurrent($fileList);
$fileList                       = getBufferFileList($fileList,$buffer_size);

fillingBuffer($fileList);


$timeout                        = 60;
$noactivity                     = 0;
$retrial                        = 2;

// Start an endless stream loop
while ( true ) {
    $file_path                  = getChunkName($current);
        if (file_exists($file_path)) {

            displayChunk( $file_path);
                // Update log record
                $current++;
                $noactivity             = 0;
        } else {
                sleep(1);
                $noactivity++;
                if($noactivity%10==1){

                    $fileList                       = getFileList($path);
                    $current                        = getCurrent($fileList);
                    $fileList                       = getBufferFileList($fileList,$buffer_size);
                    fillingBuffer($fileList);
                }
                writeLog( $file_path.":".$noactivity);
                // go home live, you're finished
                if ($noactivity >= $timeout) {
                        die();
                }
        }
}
