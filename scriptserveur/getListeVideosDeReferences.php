


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


    $fichiers =  isset($_POST['fichiers'])? $_POST['fichiers']:null;
    $retArray = array();
    if($fichiers!=null){

        foreach ($fichiers as $fichier)
        {


            $files = glob("../lookatthis/".$fichier['nomFichier']."*");
            // Process through each file in the list
            // and output its extension
            if (count($files) > 0)
                foreach ($files as $file) // Devrait en avoir que 1.
                    {
                        $info = pathinfo($file);
                        $filesize = filesize("../lookatthis/".$info['basename']); // bytes
        
                        $fichier['trouve']= true;
                        $fichier['taille']= round($filesize / 1024 / 1024, 1);
                        $fichier['basename']= $info['basename'];

                    }
            else{
                $fichier['trouve']= false;
                $fichier['taille']= "0";
                $fichier['basename']= "null";
            }
            $retArray[] =  $fichier;



        }



    }



$listeJSON = json_encode($retArray);

echo $listeJSON;
	

?>


