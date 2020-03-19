<?php

require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$connMJ = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$connMJ) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($connMJ, "SET NAMES 'utf8'");
mysqli_query($connMJ, "SET CHARACTER SET 'utf8'");
	




$totalJSON=json_decode(stripslashes($transPJ), true);

for($a=0;$a<count($totalJSON);$a++)
{		
		$lesParams = $totalJSON[$a];




	$intEquipe = $lesParams['equipeId'];
	$intLigue = $lesParams['ligueId'];
	$intJoueur =  mysqli_real_escape_string($connMJ,$lesParams['nomJoueur']);
	$intNo = $lesParams['noJoueur'];

	$joueurId = $lesParams['joueurId'];
	$position = $lesParams['position'];


	
	$resultNouveau = mysqli_query($connMJ,"UPDATE {$tableJoueur} SET NomJoueur='{$intJoueur}', NumeroJoueur='{$lesParams['noJoueur']}',position='{$lesParams['position']}',dernierMAJ=NOW() WHERE joueur_id='{$joueurId}' ")
				or die(mysqli_error($connMJ)."update bug");  
	
	
	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	
$retour = mysqli_query($connMJ,"SELECT abonJouLig 
						FROM abonJoueurLigue 
						WHERE joueurId={$joueurId}
						AND ligueId = {$lesParams['ligueId']}
						AND finAbon>NOW()")or die(mysqli_error($connMJ)."select ligue"."SELECT abonJouLig FROM abonJoueurLigue WHERE joueurId={$joueurId} AND ligueId = {$lesParams['ligueId']} AND finAbon>NOW()");  	

		if(mysqli_num_rows($retour)>0)
		{
			/*			$mr = mysql_fetch_row($retour);
						mysql_query("UPDATE abonJoueurLigue SET finAbon=NOW() WHERE abonJouLig='{$mr[0]}' ")
				or die(mysql_error()."update bug");  */
		}
		else
		{$retour = mysqli_query($connMJ,"INSERT INTO abonJoueurLigue (joueurId, ligueId, permission, debutAbon, finAbon) 
		VALUES ('{$joueurId}', '{$intLigue}',30, NOW(),'2030-01-01')")or die(mysqli_error($connMJ)."insert Ligue");  
		}
		///////////////////////////////////
		/// Section Equipe
		//////////////////////////////////
		
		$qAbonEq = "SELECT abonJouEq ,abonJoueurEquipe.equipeId
						FROM abonJoueurEquipe 
						JOIN abonEquipeLigue
							ON (abonJoueurEquipe.equipeId=abonEquipeLigue.equipeId)
						WHERE joueurId={$joueurId}
						AND ligueId = {$lesParams['ligueId']}
						AND abonJoueurEquipe.finAbon>NOW()";
		
		
		$resultAbonEq = mysqli_query($connMJ,$qAbonEq)
		or die(mysqli_error($connMJ).$qAbonEq);  
		

		$abonOk=false;
		//On passe dans les abonnements actifs
		while($rangeeAbonEq=mysqli_fetch_array($resultAbonEq)){
			
			if($lesParams['equipeId']!=$rangeeAbonEq['equipeId']){
				mysqli_query($connMJ,"UPDATE abonJoueurEquipe SET finAbon=NOW() WHERE abonJouEq='{$rangeeAbonEq[0]}' ")
				or die(mysqli_error($connMJ)."update bug EQ");
			}
			else	{
					
				$abonOk=true;
			}	  

		}

		if($intEquipe!=0&&!$abonOk)
		{
			$retour = mysqli_query($connMJ,"INSERT INTO abonJoueurEquipe (joueurId, equipeId, permission, debutAbon, finAbon) 
			VALUES ('{$joueurId}', '{$intEquipe}',30, NOW(),'2030-01-01')")or die(mysqli_error($connMJ)."insert bug EQ");  	
		}		
		


}
mysqli_close($connMJ);

?>
