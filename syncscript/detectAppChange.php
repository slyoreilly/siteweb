
<?php
require '../scriptsphp/defenvvar.php';


    
$app = $_POST['app'];
$channel =$_POST['channel'];
$versionCode =$_POST['version'];



$qGet ="SELECT systemconfigId 
        FROM SystemConfig 
        WHERE app='{$app}'
            AND channel='{$channel}'
            AND isActive=1
            AND versionCode>'{$versionCode}'
            ORDER BY versionCode DESC LIMIT 1
             " ;
    $res = mysqli_query($conn,$qGet) or die("Erreur dans le get de set AppVersion "."\n".mysqli_error($conn));

    if(mysqli_num_rows($res)>0){
        if(!strcmp($channel,"stable")){
            echo "https://syncstats.ca/android/".$app."/".$channel."/".$app.".apk";
        }
        else{
            echo "http://fileserver.sm.syncstats.ca/android/".$app."/".$channel."/".$app.".apk";
        }
    }else{
        echo " ";
    }

?>

