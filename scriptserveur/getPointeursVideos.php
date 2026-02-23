<?php
header("Access-Control-Allow-Origin: https://syncstats.com");
header("Access-Control-Allow-Origin: http://syncstats.com");
header("Access-Control-Allow-Origin: https://vieuxsite.sm.syncstats.ca");
header("Access-Control-Allow-Origin: http://vieuxsite.sm.syncstats.ca");
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

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";
$database = 'syncsta1_900';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");



$fichiers=json_decode($_POST['fichiers']);
$mesFics= array();
for($a=0;$a<count($fichiers);$a++){
    $rFichiers = mysqli_query($conn,"SELECT videoId, nomFichier,emplacement FROM Video WHERE nomFichier = '{$fichiers[$a]}'  LIMIT 0,1")
    or die(mysqli_error($conn)." Select saisonId"); 
    
    while($rangFichier=mysqli_fetch_assoc($rFichiers))
    {
        $monFic=array();
        $monFic['videoId']=$rangFichier['videoId'];
        $monFic['emplacement']=$rangFichier['emplacement'];
        $monFic['nomFichier']=$rangFichier['nomFichier'];
       // $monFic['lienOK']= file_exists("http://"+$rangFichier['emplacement']+"/lookatthis/"+$rangFichier['nomFichier']);
       $monFic['lienOK']=true;
        array_push($mesFics,$monFic);
        
    }
    


}
echo json_encode($mesFics);

 
 

	
?>
