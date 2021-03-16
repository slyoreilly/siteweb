
<?php
    $db_host="192.168.1.99";
    $db_user="syncsta1_u01";
    $db_pwd="test";
    $database="syncsta1_901";


$OK = true;
$versionName="";
$channel ="alpha";
$isActive = false;

$opts = getopt('',['app:','channel','versionCode:','versionName:','isActive']);


//////////////////////////////////////////////



if(isset($opts['app'])){
    $app = $opts['app'];
}else{
    $OK = false;
}

if(isset($opts['channel'])){
    $channel = $opts['channel'];
}
if(isset($opts['versionCode'])){
    $versionCode = $opts['versionCode'];
}else{
    $OK = false;
}
if(isset($opts['versionName'])){
    $versionName = $opts['versionName'];
}
if(isset($opts['isActive'])){
    $isActive = $opts['isActive'];
}


if($OK){


    // Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'"); 

$qGet ="SELECT systemconfigId 
        FROM SystemConfig 
        WHERE app='{$app}'
            AND channel='{$channel}'
            AND versionCode='{$versionCode}'
            AND versionName='{$versionName}'
             " ;
    $res = mysqli_query($conn,$qGet) or die("Erreur dans le get de set AppVersion "."\n".mysqli_error($conn));

    if(mysqli_num_rows($res)==0){
        $qSet = "INSERT INTO SystemConfig (app, channel, versionCode, versionName, isActive, lastUpdate) 
                VALUES ('{$app}','{$channel}','{$versionCode}','{$versionName}','{$isActive}',NOW())";
    }else{

        $qSet = "UPDATE SystemConfig 
        SET app='{$app}', channel='{$channel}', versionCode='{$versionCode}', versionName='{$versionName}', isActive='{$isActive}'
        WHERE  app='{$app}'
            AND channel='{$channel}'
            AND versionCode='{$versionCode}'
            AND versionName='{$versionName}'";

    }

    $res = mysqli_query($conn,$qSet) or die("Erreur dans le set de set AppVersion "."\n".mysqli_error($conn));
    echo '200';


mysqli_close($conn);
}

else{

  echo '412';
}



?>

