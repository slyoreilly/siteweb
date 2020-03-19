<?php
require '../scriptsphp/defenvvar.php';

$table = $_POST['table'];
if(strcmp($table, "EvaluationJoueurs")==0)
{$ou = stripslashes(str_replace("'","",$_POST['ou']));}
else
{$ou = str_replace("'","",$_POST['ou']);}
//$ligueId = $_POST['ligueId'];


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
	$qSelOu ="SELECT * FROM $table WHERE $ou"; 
	$retour = mysqli_query($conn,$qSelOu)or die(mysqli_error($conn)."   /  ".$qSelOu);
}

$vecMatch = array();
while($r = mysqli_fetch_assoc($retour)) {
    $vecMatch[] = $r;
	}
$tmp=json_encode($vecMatch);
$adomper=$tmp;

if(strcmp($table, "EvaluationJoueurs")==0)
{$adomper= stripslashes($tmp);}


//
$adomper =str_replace( '"[' ,'[',$adomper );
$adomper =str_replace( ']"' ,']',$adomper );

echo $tmp;
//echo $adomper;
//echo json_encode($vecMatch)

	//		header("HTTP/1.1 200 OK");
?>

