
<?php
require '../scriptsphp/defenvvar.php';
http_response_code(500);
header('http/1.0 404 not found');
echo 'a';
$OK = true;
$versionName="";
$channel ="alpha";
$isActive = false;

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
    $res =false;
    if(mysqli_num_rows($res)==0){
        $qSet = "INSERT INTO SystemConfig (app, channel, versionCode, versionName, isActive, lastUpdate) 
                VALUES ('{$app}','{$channel}','{$versionCode}','{$versionName}','{$isActive}',NOW())";
    }else{

        $qSet = "UPDATE SystemConfig 
        SET app='{$app}', channel='{$channel}', versionCode='{$versionCode}', versionName='{$versionName}', isActive='{$isActive}'
        WHERE  app='{$app}'
            AND channel='{$channel}'
            AND versionCode='{$versionCode}'
            AND versionName='{$versionName}'"

    }
    echo 'a';

    $res = mysqli_query($conn,$qSet) or die("Erreur dans le set de set AppVersion "."\n".mysqli_error($conn));

if($res=false){
    http_response_code(409);
}else{http_response_code(200);}
    
echo 'a';


mysqli_close($conn);
}

else{

    http_response_code(412);
}

http_response_code(500);
header('http/1.0 404 not found');

?>

