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
// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");



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

$rFic = mysqli_query($conn, $reqFic)
or die(mysqli_error($conn));  
$leVid=array();
$leVid = mysqli_fetch_assoc($rFic);
if(strcmp(json_encode($leVid),"false")==0||strcmp(json_encode($leVid),"null")==0){
	
	$reqFic = "SELECT * 
			FROM Video
			JOIN TableMatch
				ON (Video.nomMatch = TableMatch.match_id)
			LEFT JOIN TableJoueur
				ON (Video.tagPrincipal = TableJoueur.joueur_id)	
			WHERE nomFichier='{$videofile}'";

$rFic = mysqli_query($conn,$reqFic)
or die(mysqli_error($conn));  
$leVid=array();
$leVid = mysqli_fetch_assoc($rFic);
	
}else{

}


$reqAltAngles = "SELECT * 
			FROM Video
			WHERE nomMatch='{$leVid['nomMatch']}'";
		
		

echo json_encode($leVid);

mysqli_close($conn);	


?>
