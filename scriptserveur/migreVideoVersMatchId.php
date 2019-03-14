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
           INNER JOIN TableMatch
           on (Video.nomMatch=TableMatch.matchIdRef)
where nomMatch LIKE '201%' group by TableMatch.matchIdRef LIMIT 0,1000
") 
//WHERE (equipe_event_id = 5 or equipe_event_id =6 or equipe_event_id =10) and tempChrono>10000")
or die(mysqli_error($conn));
 while($Video=mysqli_fetch_array($rVideo))
{
       $rEvent = mysqli_query($conn, "
       UPDATE Video SET nomMatch='{$Video['match_id']}' where nomMatch='{$Video['matchIdRef']}'
        ") or die(mysqli_error($conn)) ;

    

}



?>


	
	YO
	</body>

</html>	
