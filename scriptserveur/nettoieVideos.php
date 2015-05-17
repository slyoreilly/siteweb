<HTML> 
<HEAD> 
	<title>Nettoie vidéos</title>
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

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}



mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");


/////////////////////////////////////////////////////////////
// 
//


$mesFics = scandir( '../lookatthis/' , SCANDIR_SORT_ASCENDING);
$ficOrphelins = array();

foreach ($mesFics as $unFichier) {
	$qFic = "SELECT nomFichier, nomMatch 
				FROM Video 
				WHERE nomFichier = '{$unFichier}'";
	$resFic = mysql_query($qFic)or die(mysql_error().'{$unFichier}' );  
	$ligneFic = mysql_fetch_row($resFic);
	if($ligneFic==False)
	{
		array_push($ficOrphelins,$unFichier);
		
	}
}

foreach($ficOrphelins as $fO){
	echo("<p>".$fO."</p>");
	@unlink(realpath(__DIR__.'/../lookatthis/'.$fO));
}
	

	

?>


	</body>

</html>	
