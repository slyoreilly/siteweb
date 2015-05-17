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

$username = $_POST['username'];
$password = $_POST['password'];
$ligueId = $_POST['ligueId'];

$joueursmaj = stripslashes($_POST['joueursmaj']);

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	
	
//$json=json_decode("'".$matchjson."'");
$lesJoueurs = json_decode($joueursmaj, true);   // Liste des joueurs étant mis à jours.
/*
$Ij=0;
while($Ij<count($lesJoueurs))
{
$retour = mysql_query("UPDATE {$tableJoueur} SET equipe_id_ref='{$lesJoueurs[$Ij]['equipeId']}',dernierMAJ=NOW() WHERE joueur_id='{$lesJoueurs[$Ij]['joueurId']}'");	
$Ij++;
}*/
$Ij=0;
while($Ij<count($lesJoueurs))
{	// Parmis la liste de joueurs qu'on a, sortir ceux qui font partis d'une équipe inscrite à la ligue.
	$retour1 = mysql_query("SELECT * FROM abonJoueurEquipe 
							JOIN abonEquipeLigue 
							 ON (abonJoueurEquipe.equipeId=abonEquipeLigue.equipeId) 
							WHERE  joueurId='{$lesJoueurs[$Ij]['joueurId']}' 
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
//		else {
			
			if($lesJoueurs[$Ij]['equipeId']!=0)// S'il y a une nouvelle equipe (qu'il ne devient pas agent libre)			
			{
					$sql = "INSERT INTO abonJoueurEquipe 
									(joueurId,equipeId,permission,debutAbon,finAbon) 
									VALUES 
									('{$lesJoueurs[$Ij]['joueurId']}','{$lesJoueurs[$Ij]['equipeId']}','30',NOW(),'2020-01-01')";
				$res=mysql_query($sql) or die("Et mon erreur: ".mysql_error());

			}
			
//		}
		$retour = mysql_query("UPDATE {$tableJoueur} SET equipe_id_ref=null,dernierMAJ=NOW() WHERE joueur_id='{$lesJoueurs[$Ij]['joueurId']}'");	
		$Ij++;
}

echo(mysql_error());


echo "Message: ".$joueursmaj;
//	header("HTTP/1.1 200 OK");
?>
<?php  ?>
