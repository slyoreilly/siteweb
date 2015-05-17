<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_910';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';


$videos = $_POST['videos'];

$syncOK=array();
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
	mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

$IV=0;
//while ($IV < count($videos)) {


		
		switch($videos['video']['es']) {

			case 12 :
				$qInsM = "INSERT INTO Videos (chrono,nomfic,video) VALUES ('{$videos['video']['chrono']}','{$videos['video']['nomfic']}',2)";

				mysql_query($qInsM) or die(mysql_error() . $qInsM);
				array_push($syncOK, $videos['video']['chrono']);
				break;

	}
	$IV++;
//}
	
echo json_encode($syncOK);
	

?>
