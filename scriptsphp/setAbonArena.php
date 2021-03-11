<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";
$database = 'syncsta1_900';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);

$dateDeb = $_POST['dateDeb'];
$dateFin = $_POST['dateFin'];
$ligueId = $_POST['ligueId'];
$arenaId = $_POST['arenaId'];
$abonId = $_POST['abonId'];

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

	if($dateDeb=='')
	{$dateDeb='2011-01-01';}
	if($dateFin=='')
	{$dateFin='2050-01-01';}

if($abonId!='undefined'&&$abonId!=0)
{
$retour = mysqli_query($conn, "SELECT * 
						FROM abonLigueArena 
						WHERE abonLiArId='{$abonId}'")or die(mysqli_error($conn));	

if(mysqli_num_rows($retour)>0)
{
	$retour = mysqli_query($conn, "UPDATE MatchAVenir SET arenaId={$arenaId},ligueId={$ligueId} ,debutAbon='{$dateDeb}',finAbon='{$dateFin}',ligueId='{$ligueId}' WHERE abonLiArId='{$abonId}'")or die(mysql_error()." UPDATE ");	
$retour=$abonId;
}
}


else {
	$retour = mysqli_query($conn,"INSERT INTO abonLigueArena (debutAbon, finAbon, ligueId, arenaId, permission) 
VALUES ('{$dateDeb}','{$dateFin}','{$ligueId}','{$arenaId}',30)")or die(mysqli_error($conn)." INSERT INTO");

$ret = mysqli_query($conn,"SELECT *
						FROM abonLigueArena 
						WHERE 1 
						ORDER BY abonLiArId DESC")or die(mysqli_error($conn));	
$tmp= mysqli_fetch_row($ret);
$retour=$tmp[0];	
}
mysqli_close($conn);
echo $retour;
?>
