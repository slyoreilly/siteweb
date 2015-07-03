<?php
$totalJSON=json_decode(stripslashes($transPJ), true);

for($a=0;$a<count($totalJSON);$a++)
{		
		$lesParams = $totalJSON[$a];




	$intEquipe = $lesParams['equipeId'];
	$intLigue = $lesParams['ligueId'];
	$intJoueur =  mysql_real_escape_string($lesParams['nomJoueur']);
	$intNo = $lesParams['noJoueur'];

	$joueurId = $lesParams['joueurId'];
	$position = $lesParams['position'];


	
	$resultNouveau = mysql_query("UPDATE {$tableJoueur} SET NomJoueur='{$intJoueur}', NumeroJoueur='{$lesParams['noJoueur']}',position='{$lesParams['position']}',dernierMAJ=NOW() WHERE joueur_id='{$joueurId}' ")
				or die(mysql_error()."update bug");  
	
	
	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	
$retour = mysql_query("SELECT abonJouLig 
						FROM abonJoueurLigue 
						WHERE joueurId={$joueurId}
						AND ligueId = {$lesParams['ligueId']}
						AND finAbon>NOW()")or die(mysql_error()."select ligue"."SELECT abonJouLig FROM abonJoueurLigue WHERE joueurId={$joueurId} AND ligueId = {$lesParams['ligueId']} AND finAbon>NOW()");  	

		if(mysql_num_rows($retour)>0)
		{
			/*			$mr = mysql_fetch_row($retour);
						mysql_query("UPDATE abonJoueurLigue SET finAbon=NOW() WHERE abonJouLig='{$mr[0]}' ")
				or die(mysql_error()."update bug");  */
		}
		else
		{$retour = mysql_query("INSERT INTO abonJoueurLigue (joueurId, ligueId, permission, debutAbon, finAbon) 
		VALUES ('{$joueurId}', '{$intLigue}',30, NOW(),'2030-01-01')")or die(mysql_error()."insert Ligue");  
		}
		///////////////////////////////////
		/// Section Equipe
		//////////////////////////////////
		
		
		$retour = mysql_query("SELECT abonJouEq 
						FROM abonJoueurEquipe 
						JOIN abonEquipeLigue
							ON (abonJoueurEquipe.equipeId=abonEquipeLigue.equipeId)
						WHERE joueurId={$joueurId}
						AND ligueId = {$lesParams['ligueId']}
						AND abonJoueurEquipe.equipeId <> {$lesParams['equipeId']}
						AND abonJoueurEquipe.finAbon>NOW()")or die(mysql_error()."select bug EQ");  	

		if(mysql_num_rows($retour)>0)
		{
						$mr = mysql_fetch_row($retour);
						mysql_query("UPDATE abonJoueurEquipe SET finAbon=NOW() WHERE abonJouEq='{$mr[0]}' ")
				or die(mysql_error()."update bug EQ");  
		}
 if($intEquipe!=0)
{
	$retour = mysql_query("INSERT INTO abonJoueurEquipe (joueurId, equipeId, permission, debutAbon, finAbon) 
		VALUES ('{$joueurId}', '{$intEquipe}',30, NOW(),'2030-01-01')")or die(mysql_error()."insert bug EQ");  	
}
}
?>
