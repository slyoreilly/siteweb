
<?php
require '../scriptsphp/defenvvar.php';
http_response_code(500);
header('http/1.0 404 not found');
echo 'a';
$OK = true;
$versionName="";
$channel ="alpha";
$isActive = false;

// Takes raw data from the request
$json = file_get_contents('php://input');

// Converts it into a PHP object
$data = json_decode($json);

$app =$data->app;
$channel =$data->channel;
$versionCode=$data->versionCode;
$versionName=$data->versionName;
$isActive =$data->isActive;



/*
if(isset($_POST['app'])){
    $app = $_POST['app'];
}else{
    $OK = false;
}

if(isset($_POST['channel'])){
    $channel = $_POST['channel'];
}
if(isset($_POST['versionCode'])){
    $versionCode = $_POST['versionCode'];
}else{
    $OK = false;
}
if(isset($_POST['versionName'])){
    $versionName = $_POST['versionName'];
}
if(isset($_POST['isActive'])){
    $isActive = $_POST['isActive'];
}*/
echo 'b';

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

    echo "qGet: ".$qGet." num_rows: ".mysqli_num_rows($res);
    if(mysqli_num_rows($res)==0){
        echo 'd';
        $qSet = "INSERT INTO SystemConfig (app, channel, versionCode, versionName, isActive, lastUpdate) 
                VALUES ('{$app}','{$channel}','{$versionCode}','{$versionName}','{$isActive}',NOW())";
                echo 'e';
    }else{

        $qSet = "UPDATE SystemConfig 
        SET app='{$app}', channel='{$channel}', versionCode='{$versionCode}', versionName='{$versionName}', isActive='{$isActive}'
        WHERE  app='{$app}'
            AND channel='{$channel}'
            AND versionCode='{$versionCode}'
            AND versionName='{$versionName}'";

    }
    echo 'a';
    $res =false;

    $res = mysqli_query($conn,$qSet) or die("Erreur dans le set de set AppVersion "."\n".mysqli_error($conn));
error_log($qSet);
if($res=false){
    http_response_code(409);
}else{http_response_code(200);}
    
echo 'a';


mysqli_close($conn);
}

else{
    echo 'c';

    http_response_code(412);
}

http_response_code(500);
header('http/1.0 404 not found');

?>

