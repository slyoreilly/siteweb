<?php
require '../scriptsphp/defenvvar.php';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
$evalueId = $_POST['evalueId'];
$evaluateurId = $_POST['evaluateurId'];
$ligueId = $_POST['ligueId'];

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");


	
if($ou==NULL)
{	
$retour = mysqli_query($conn,"SELECT *
						FROM $table
						 WHERE 1")or die(mysqli_error($conn));	
}

else {
	$retour = mysqli_query($conn, "SELECT *
						FROM $table
						 WHERE $ou")or die(mysqli_error($conn));
}

$vecMatch = array();
while($r = mysqli_fetch_assoc($retour)) {
    $vecMatch[] = $r;
	}
$tmp=json_encode($vecMatch);
$adomper=$tmp;
//$adomper= stripslashes($tmp);
$adomper =str_replace( '"[' ,'[',$adomper );
$adomper =str_replace( ']"' ,']',$adomper );

echo $tmp;
//echo $adomper;
//echo json_encode($vecMatch)

	//		header("HTTP/1.1 200 OK");

	mysqli_close($conn)
?>

