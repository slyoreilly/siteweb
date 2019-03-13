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
where reference =0 and type=0 ORDER By videoId ASC LIMIT 0,10
") 
//WHERE (equipe_event_id = 5 or equipe_event_id =6 or equipe_event_id =10) and tempChrono>10000")
or die(mysqli_error($conn));
 while($video=mysqli_fetch_array($rVideo))
{
       $rEvent = mysqli_query($conn, "
           SELECT * FROM TableEvenement0
                where match_event_id='{$Video['nomMatch']}' AND reference =0 and type=0 AND chrono<'{$Video['chrono']}' ORDER BY chrono DESC Limit 0,1
        ") ;



    while($event=mysqli_fetch_array($rEvent))
    {

        if(abs($event['chrono']-$Video['chrono'])<20000){
                mysqli_query($conn, "
                UPDATE `Video` SET `reference`='{$event['event_id']} where videoId='{$Video['videoId']}"
                );

        }

	    echo $Video['videoId']."</br>";
    }

}

$rVideo = mysqli_query($conn, "SELECT * FROM Video
where reference =0 and type=0 ORDER By videoId ASC LIMIT 0,10
") 
//WHERE (equipe_event_id = 5 or equipe_event_id =6 or equipe_event_id =10) and tempChrono>10000")
or die(mysqli_error($conn));
 while($video=mysqli_fetch_array($rVideo))
{
       $rEvent = mysqli_query($conn, "
           SELECT * FROM TableEvenement0
           INNER JOIN TableMatch
           on (TableEvenement0.match_event_id=TableMatch.matchIdRef)
                where match_id='{$Video['nomMatch']}' AND reference =0 and type=0 AND chrono<'{$Video['chrono']}' ORDER BY chrono DESC Limit 0,1
        ") ;



    while($event=mysqli_fetch_array($rEvent))
    {

        if(abs($event['chrono']-$Video['chrono'])<20000){
                mysqli_query($conn, "
                UPDATE `Video` SET `reference`='{$event['event_id']} where videoId='{$Video['videoId']}"
                );

        }

	    echo $Video['videoId']."</br>";
    }

}

?>


	
	YO
	</body>

</html>	
