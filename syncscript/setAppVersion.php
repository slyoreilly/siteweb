
<?php
require '../scriptsphp/defenvvar.php';


$OK = true;
$versionName="";
$channel ="alpha";
$isActive = 0;

// Takes raw data from the request
$json = file_get_contents('php://input');

// Converts it into a PHP object
$data = json_decode($json);

$app =$data->app;
$channel =$data->channel;
$versionCode=$data->versionCode;
$versionName=$data->versionName;
$isActive =$data->isActive;


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

    $res =false;

    $res = mysqli_query($conn,$qSet) or die("Erreur dans le set de set AppVersion "."\n".mysqli_error($conn));

if($res=false){
    http_response_code(409);
}else{http_response_code(200);}
    



mysqli_close($conn);
}

else{
    http_response_code(412);
}



?>

