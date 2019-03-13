<HTML> 
<HEAD> 
	<title>Normalise reference videos</title>
<link rel="stylesheet" href="style/general.css" type="text/css">
<script src="/scripts/fonctions.js" type="text/javascript"></script>

</HEAD>
<body>


<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';


////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////



// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}
 
mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");




$rVideo = mysqli_query($conn, "SELECT * FROM Video
where reference =0 and type=0 ORDER By chrono ASC LIMIT 0,10000
") 
//WHERE (equipe_event_id = 5 or equipe_event_id =6 or equipe_event_id =10) and tempChrono>10000")
or die(mysqli_error($conn));
 while($Video=mysqli_fetch_array($rVideo))
{
       $rEvent = mysqli_query($conn, "
           SELECT * FROM TableEvenement0
                where (match_event_id='{$Video['nomMatch']}')  AND chrono<('{$Video['chrono']}'+20000) ORDER BY chrono DESC Limit 0,1
        ") or die(mysqli_error($conn)) ;

        echo mysqli_num_rows( $rEvent)." dans ".$Video['nomMatch']."</br>";
if(mysqli_num_rows( $rEvent)==0){
    echo (abs($event['chrono']-$Video['chrono']))." BAD : noevent at first</br>";
}
    else{

    while($event=mysqli_fetch_array($rEvent))
    {
        echo 'processing...'."</br>";
        if(abs($event['chrono']-$Video['chrono'])<20000){
                mysqli_query($conn, "
                UPDATE Video SET reference='{$event['event_id']}' where videoId='{$Video['videoId']}'"
                ) or die(mysqli_error($conn));
                echo (abs($event['chrono']-$Video['chrono']))." GOOD</br>";
        
            }
            else{
                mysqli_query($conn, "
                UPDATE `Video` SET `reference`=null where videoId='{$Video['videoId']}'"
                );
    
                
                echo (abs($event['chrono']-$Video['chrono']))." BAD</br>";
            }

	    echo $Video['videoId']."</br>";
    }
    }

}

$rVideo = mysqli_query($conn, "SELECT * FROM Video
where reference =0 and type=0 ORDER By chrono ASC LIMIT 0,10000
") 
//WHERE (equipe_event_id = 5 or equipe_event_id =6 or equipe_event_id =10) and tempChrono>10000")
or die(mysqli_error($conn));
 while($Video=mysqli_fetch_array($rVideo))
{
       $rEvent = mysqli_query($conn, "
           SELECT * FROM TableEvenement0
           INNER JOIN TableMatch
           on (TableEvenement0.match_event_id=TableMatch.matchIdRef)
                where match_id='{$Video['nomMatch']}' AND chrono<('{$Video['chrono']}'+20000) ORDER BY chrono DESC Limit 0,1
        ") ;

        if(mysqli_num_rows( $rEvent)==0){
            mysqli_query($conn, "
            UPDATE `Video` SET `reference`=null where videoId='{$Video['videoId']}'"
            );
            echo (abs($event['chrono']-$Video['chrono']))." BAD : noevent at first</br>";
        }
            else{
        

    while($event=mysqli_fetch_array($rEvent))
    {
        echo 'processing.^.^.'."{$event['match_event_id']}"."</br>";

        if(abs($event['chrono']-$Video['chrono'])<20000){
                mysqli_query($conn, "
                UPDATE `Video` SET `reference`='{$event['event_id']}' where videoId='{$Video['videoId']}'"
                );
                echo (abs($event['chrono']-$Video['chrono']))." GOOD</br>";
        }else{
            mysqli_query($conn, "
            UPDATE `Video` SET `reference`=null where videoId='{$Video['videoId']}'"
            );
            echo (abs($event['chrono']-$Video['chrono']))." BAD</br>";
        }

	    echo $Video['videoId']."</br>";
    }
}

}
echo 'YOYO!!'; 

$rVideo = mysqli_query($conn, "SELECT * FROM Video
where reference  is NULL  or (reference<1 and type=5) ORDER By chrono ASC LIMIT 0,10000
") 
//WHERE (equipe_event_id = 5 or equipe_event_id =6 or equipe_event_id =10) and tempChrono>10000")
or die(mysqli_error($conn));
 while($Video=mysqli_fetch_array($rVideo))
{
       $rEvent = mysqli_query($conn, "
           SELECT * FROM Clips
           INNER JOIN TableMatch
           on (Clips.matchId=TableMatch.matchIdRef)
                where match_id='{$Video['nomMatch']}' AND chrono<('{$Video['chrono']}'+20000) ORDER BY chrono DESC Limit 0,1
        ") ;

        if(mysqli_num_rows( $rEvent)==0){
            mysqli_query($conn, "
            UPDATE `Video` SET `reference`=-1 where videoId='{$Video['videoId']}'"
            );
            echo (abs($event['chrono']-$Video['chrono']))." BAD : noevent at first</br>";
        }
            else{
        

    while($event=mysqli_fetch_array($rEvent))
    {
        echo 'processing.^.^.'."{$event['match_event_id']}"."</br>";

        if(abs($event['chrono']-$Video['chrono'])<20000){
                mysqli_query($conn, "
                UPDATE `Video` SET `reference`='{$event['clipId']}', type=5 where videoId='{$Video['videoId']}'"
                );
                echo (abs($event['chrono']-$Video['chrono']))." GOOD</br>";
        }else{
            mysqli_query($conn, "
            UPDATE `Video` SET `reference`=-1 where videoId='{$Video['videoId']}'"
            );
            echo (abs($event['chrono']-$Video['chrono']))." BAD</br>";
        }

	    echo $Video['videoId']."</br>";
    }
}

}



?>


	
	YO
	</body>

</html>	
