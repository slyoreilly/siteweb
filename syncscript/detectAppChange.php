
<?php
require '../scriptsphp/defenvvar.php';

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

$app = $_POST['app'];
$channel =$_POST['channel'];
$versionCode =$_POST['version'];



$qGet ="SELECT systemconfigId 
        FROM SystemConfig 
        WHERE app='{$app}'
            AND channel='{$channel}'
            AND versionCode>'{$versionCode}'
             " ;
    $res = mysqli_query($conn,$qGet) or die("Erreur dans le get de set AppVersion "."\n".mysqli_error($conn));

    if(mysqli_num_rows($res)>0){
        if(!strcmp($channel,"stable")){
            echo "http://syncstats.com/android/".$app."/".$channel."/".$app.".apk";
        }
        else{
            echo "http://vieuxsite.sm.syncstats.ca/android/".$app."/".$channel."/".$app.".apk";
        }
    }else{
        echo "";
    }

?>

