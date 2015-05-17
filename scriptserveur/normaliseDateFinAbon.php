<HTML> 
<HEAD> 
	<title>Statistiques du joueur</title>

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

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}


	$result2=mysql_query("UPDATE abonJoueurLigue SET finAbon='2030-01-01' WHERE finAbon>'2030-01-01' ")
or die(mysql_error());















?>


	
	YO, <?php echo $vrai." bons, ".$faux." pas bons." ?>
 	</body>

</html>	
