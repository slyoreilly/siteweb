
<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$app = $_POST['app'];
$channel =$_POST['channel'];
$version =$_POST['version'];
error_log("test0: ".$channel." ".$app." ".$version);

$mVersionArray= array("synccam"=>array("stable"=>"80","beta"=>"80","alpha"=>"88"),"syncstats"=>array("stable"=>"64","beta"=>"65","alpha"=>"65"));
error_log("test-1: ".json_encode($mVersionArray));

error_log("test: ".$mVersionArray[$app][$channel]);

    if($mVersionArray[$app][$channel]>$version)
    {
        echo "http://syncstats.com/android/".$app."/".$channel."/".$app.".apk";
        error_log("test2: "."http://syncstats.com/android/".$app."/".$channel."/".$app.".apk");
    }
    else echo "";

?>

