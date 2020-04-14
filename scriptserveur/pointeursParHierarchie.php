<?php
header("Access-Control-Allow-Origin: https://syncstats.com");
header("Access-Control-Allow-Origin: http://syncstats.com");
header("Access-Control-Allow-Origin: https://syncstats.ddns.net");
header("Access-Control-Allow-Origin: http://syncstats.ddns.net");
header("Access-Control-Allow-Origin: https://syncstats.ca");
header("Access-Control-Allow-Origin: http://syncstats.ca");
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }

    function UR_exists($url){
        $headers=get_headers($url);
        return stripos($headers[0],"200 OK")?true:false;
     }

    require '../scriptsphp/defenvvar.php';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection/
if (!$conn) {
   die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

$hierarchie = $_POST['hierarchie'];
$plusVieux = $_POST['plusVieux'];
$plusNeuf = $_POST['plusNeuf'];
$maxTraite = $_POST['maxTraite'];

$retour ="What....";
$dernierOK = null;
$rFichiers = mysqli_query($conn,"SELECT videoId, nomFichier,emplacement,chrono FROM Video WHERE chrono > '{$plusVieux}'  AND chrono < '{$plusNeuf}' ORDER BY chrono ASC limit 0,{$maxTraite}")
or die(mysqli_error($conn)." Select saisonId"); 
$nbTraite=0;
while($rangFichier=mysqli_fetch_assoc($rFichiers))
{
    $nbTraite++;
    $trouve=false;
    $dernierOK=$rangFichier['chrono'];
    for($a=0;$a<count($hierarchie)&&$trouve==false;$a++){
        $retour.= "Traite "."http://".$hierarchie[$a]."/lookatthis/".$rangFichier['nomFichier']."\r\n";
        if(UR_exists("http://".$hierarchie[$a]."/lookatthis/".$rangFichier['nomFichier'])){
            $trouve=true;
            if(strcmp($rangFichier['emplacement'],$hierarchie[$a])){
               
                $retour.= "Aurait mis a jour: ".$rangFichier['videoId']." à ".$hierarchie[$a]."\r\n";
               /* mysqli_query($conn,"UPDATE Video SET emplacement ='{$hierarchie[$a]}' WHERE videoId=$rangFichier['videoId']")
                or die(mysqli_error($conn)." Update Emplacement");*/
            }
        }
         else
         {
            if(UR_exists("https://".$hierarchie[$a]."/lookatthis/".$rangFichier['nomFichier'])){
                $trouve=true;
                if(strcmp($rangFichier['emplacement'], $hierarchie[$a])){
                    $retour.= "Aurait mis a jour: ".$rangFichier['videoId']." à ".$hierarchie[$a]."\r\n";
/*                    mysqli_query($conn,"UPDATE Video SET emplacement ='{$hierarchie[$a]}' WHERE videoId=$rangFichier['videoId']")
                    or die(mysqli_error($conn)." Update Emplacement"); */
                }
            }
            else{
                
            }
         }
    }

    
}
$rFichiers = mysqli_query($conn,"SELECT chrono FROM Video WHERE chrono >= $dernierOK ORDER BY chrono ASC limit 0,2" )
or die(mysqli_error($conn)." Select saisonId"); 
$rangee = mysqli_data_seek($rFichiers ,1);
$nextOK = $rangee[0]; 
$jsonRetour = Array();
$jsonRetour['nextOK']=$nextOK;
$jsonRetour['retour']=$retour;
if($nbTtraite<$maxTraite){
    $jsonRetour['continue']=false;
}else{
    $jsonRetour['continue']=true;
}


echo json_encode($jsonRetour);
?>


