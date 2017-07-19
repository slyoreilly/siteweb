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



//////////////////////////////////////////////////////
//
//  	Section "VideoFiles"
//
//////////////////////////////////////////////////////
	
//$receveur = $_POST["receveur"];
$videofile1 = $_POST['videofile'];
$videofile = $videofile1.".mp4";

$reqFic = "SELECT * 
			FROM Video
			JOIN TableMatch
				ON (Video.nomMatch = TableMatch.matchIdRef)
			LEFT JOIN TableJoueur
				ON (Video.tagPrincipal = TableJoueur.joueur_id)	
			WHERE nomFichier='{$videofile}'";

$rFic = mysql_query($reqFic)
or die(mysql_error());  
$leVid=array();
$leVid = mysql_fetch_assoc($rFic);
if(strcmp(json_encode($leVid),"false")==0){
	
	$reqFic = "SELECT * 
			FROM Video
			JOIN TableMatch
				ON (Video.nomMatch = TableMatch.match_id)
			LEFT JOIN TableJoueur
				ON (Video.tagPrincipal = TableJoueur.joueur_id)	
			WHERE nomFichier='{$videofile}'";

$rFic = mysql_query($reqFic)
or die(mysql_error());  
$leVid=array();
$leVid = mysql_fetch_assoc($rFic);
	
}else{

}


$reqAltAngles = "SELECT * 
			FROM Video
			WHERE nomMatch='{$leVid['nomMatch']}'";
		
		

echo json_encode($leVid);

	


?>
