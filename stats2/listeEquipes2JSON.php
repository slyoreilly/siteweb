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


$equipeId = $_POST["equipeId"];
$ligueId = $_POST["ligueId"];
	

	if($equipeId==null||$equipeId==undefined)
	{
		
		$qEq ="SELECT * FROM TableEquipe
								INNER JOIN abonEquipeLigue
									ON (equipeId=equipe_id) 
									WHERE ligueId = '{$ligueId}'";
$resultEquipe = mysql_query($qEq)
or die(mysql_error());  
		
		$equipe=array();
while($rangeeEquipe=mysql_fetch_assoc($resultEquipe))
{
	$equipe[]=$rangeeEquipe;
}
		
		
		
	}else{
				$qEq ="SELECT * FROM {$tableEquipe} WHERE equipe_id = '{$equipeId}'";
		
$resultEquipe = mysql_query($qEq)
or die(mysql_error());  	

while($rangeeEquipe=mysql_fetch_assoc($resultEquipe))
{
	$equipe[]=$rangeeEquipe;
}


		
	}
	
//	echo $qEq;
echo json_encode($equipe);
	


?>
