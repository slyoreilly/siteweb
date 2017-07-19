<HTML> 
<HEAD> 
	<title>Statistiques du joueur</title>
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

$ligueId = $_GET['ligueId'];

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}



/////////////////////////////////////////////////////
	//
//   Rétro associe le vidéo
//
////////////////////////////////////////////////////

$qM = "Select matchIdRef
FROM TableMatch
Where ligueRef = '{$ligueId}' ORDER BY date DESC";

$resMatch = mysql_query($qM)
or die(mysql_error().$qM);  
$strMatch="";
while($rangMatch=mysql_fetch_array($resMatch))
{
	$qV = "Select chrono, videoId
	From Video 
	WHERE nomMatch = '{$rangMatch['matchIdRef']}'" ;
	$strMatch=$strMatch." <br> ".$qV;//$rangMatch['matchIdRef'];
	
	$resVid = mysql_query($qV)
	or die(mysql_error().$qV);

	while($rangVid=mysql_fetch_array($resVid))
	{
		$get1temps ="SELECT chrono,event_id
		FROM TableEvenement0
		ORDER BY ABS(chrono - '{$rangVid['chrono']}')
		LIMIT 1";
			$strMatch=$strMatch." <br>     ".$rangVid['chrono'];
		$resT = mysql_query($get1temps)
		or die(mysql_error().$get1temps);

		$arr= mysql_fetch_row($resT);
		
		$qU="UPDATE Video 
		SET type=0, reference = '{$arr[1]}'
		WHERE videoId = '{$rangVid['videoId']}'";
			$resU = mysql_query($qU)
		or die(mysql_error().$qU);
			
		
	}
	

	
	
}




?>


	
	YO
	
<?php
echo $strMatch;
?>
	</body>

</html>	
