<?php
require '../scriptsphp/defenvvar.php';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
$heure = $_POST['date'];
$usager = $_POST['userId'];
$fakeId = $_POST['fakeId'];
$ligueId = $_POST['ligueId'];
$location = $_POST['location'];
$referrer = $_POST['referrer'];


$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");



if($usager!=""){
$resultUser = mysqli_query($conn,"SELECT noCompte FROM TableUser where username='$usager'")
or die(mysqli_error($conn));  
$rangUser=mysqli_num_rows($resultUser);
if($rangUser>0)
{$tmpUsr=mysqli_fetch_row($resultUser);
$userId=$tmpUsr[0];}
else {
	
$erreurExist = 0;
}

}
else{
$userId=0;	
	
}
if($fakeId==""){
	$qMaxFake = "SELECT MAX(fakeId) FROM Visites"; 
	 
$resMaxFake = mysqli_query($conn, $qMaxFake) or die(mysqli_error($conn));
$tmpMax=mysqli_fetch_row($resMaxFake);
$fakeId=$tmpMax[0]+1;
}
	
	
		$query = "INSERT INTO Visites (userId,fakeId,ligueId,location,referrer,date) ".
		"VALUES ('{$userId}','{$fakeId}','{$ligueId}','{$location}','{$referrer}','{$heure}')";
		mysqli_query($conn, $query) or die("Erreur: ".$query."\n".mysqli_error($conn));
		
		echo $fakeId;
	mysqli_close($conn);
?>
