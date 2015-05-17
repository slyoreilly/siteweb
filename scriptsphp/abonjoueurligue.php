<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$pm = $_POST['premierMatch'];
$dm = $_POST['dernierMatch'];
$joueurId = $_POST['joueurId'];
$ligueId = $_POST['ligueId'];
$code = $_POST['code'];




if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}


//////////////////////////////////
//
//	Les queries
//


if($premierMatch==undefined||$premierMatch=="")
{$pm='NOW()';}
else
{$pm="'".$pm."'";}
	

if($code==60)
{
	$query_delete = "UPDATE abonJoueurLigue SET finAbon=NOW()-INTERVAL 1 DAY WHERE ligueId=$ligueId AND joueurId=$joueurId";
$retour = mysql_query($query_delete)or die('Error, query failed: '.$query_delete1);


$retour1 = mysql_query("SELECT * FROM abonJoueurEquipe 
							JOIN abonEquipeLigue 
							 ON (abonJoueurEquipe.equipeId=abonEquipeLigue.equipeId) 
							WHERE  joueurId='{$joueurId}' 
								AND abonEquipeLigue.ligueId=$ligueId
								AND abonEquipeLigue.finAbon>DATE(NOW())
								AND abonEquipeLigue.debutAbon<=DATE(NOW())
								AND abonJoueurEquipe.finAbon>DATE(NOW())
								AND abonJoueurEquipe.debutAbon<=DATE(NOW())");
		if(mysql_num_rows($retour1)>0)			//S'il y avait déjà un abonnement, mettre fin à celui-ci.
		{while($rangee = mysql_fetch_assoc($retour1))
			//{$equipe=$rangee['equipeId'];}
			
//			$retour.=mysql_query("UPDATE abonJoueurEquipe SET finAbon=NOW() WHERE joueurId='{$lesJoueurs[$Ij]['joueurId']}' AND equipeId=$equipe");
			$retour.=mysql_query("UPDATE abonJoueurEquipe SET finAbon=NOW() WHERE abonJouEq = {$rangee['abonJouEq']}");

		}




}
else
{
	
	
	
	$query_equipe = "INSERT INTO abonJoueurLigue (joueurId, ligueId, permission, debutAbon, finAbon) ".
"VALUES ($joueurId, $ligueId, 30, ".$pm.",'$dm' )";
		
$retour = mysql_query($query_equipe)or die('Error, query failed: '.$query_equipe);
}	

	echo $retour ;
?>
