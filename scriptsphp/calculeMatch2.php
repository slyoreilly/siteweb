<?php

////////////////////////////
//	LOG

//							$message = "Execution de scriptsphp/calculeMatch.";
//							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
//	        				"-------------------------".PHP_EOL;
//							file_put_contents('../test/logTest.txt', $log, FILE_APPEND);
/////////////////////////////////////////////////////////////
//
//  Définitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';
$connCM = mysqli_connect($db_host, $db_user, $db_pwd, $database, $db_port);
if (!$connCM) {
	die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($connCM, "SET NAMES 'utf8'");
mysqli_query($connCM, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($connCM, "utf8");
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';


function trouveIDParNomEqEtLigue($nomEq,$ligueId)
{
	global $connCM;
$resultEquipe = mysqli_query($connCM, "SELECT * FROM TableEquipe
										JOIN abonEquipeLigue
											ON (TableEquipe.equipe_id =abonEquipeLigue.equipeId) 
										WHERE 
											abonEquipeLigue.ligueId='{$ligueId}'
										AND
											TableEquipe.nom_equipe='{$nomEq}'")
or die(mysqli_error($connCM));  
	while($rangeeEquipe=mysqli_fetch_array($resultEquipe))
	{
		{return $rangeeEquipe['equipe_id'];// Ce sont de INT
		}
	}
$resultEquipe = mysqli_query($connCM,"SELECT * FROM TableEquipe
										WHERE 
											ligue_equipe_ref='{$ligueId}'
										AND
											nom_equipe='{$nomEq}'")
or die(mysqli_error($connCM));  
	while($rangeeEquipe=mysqli_fetch_array($resultEquipe))
	{
		{return $rangeeEquipe['equipe_id'];// Ce sont de INT
		}
	}




}

function devineLigueId($dom,$vis)
{
	global $connCM;
$rDom = mysqli_query($connCM,"SELECT ligue_equipe_ref FROM TableEquipe WHERE nom_equipe='$dom' ORDER BY ligue_equipe_ref ASC")
or die(mysqli_error($connCM));  
$rVis= mysqli_query($connCM,"SELECT ligue_equipe_ref FROM TableEquipe WHERE nom_equipe='$vis' ORDER BY ligue_equipe_ref ASC")
or die(mysqli_error($connCM));  
$nbDom=mysqli_num_rows($rDom);
$nbVis=mysqli_num_rows($rVis);
if($nbDom==1&&$nbVis<=1)
{$arEq = mysqli_fetch_row($rDom);
	return $arEq[0];}
if($nbDom==0&&$nbVis==1)
{$arEq = mysqli_fetch_row($rDom);
return $arEq[0];}
if($nbDom>0)
{
$arEq = mysqli_fetch_row($rDom);
return $arEq[0];}
if($nbVis>0)
{
$arEq = mysqli_fetch_row($rVis);
return $arEq[0];}
return 0;
}
///////////////////////////////////
	//
	//	 Lister les équipes de la ligue
	//
	///////////////////////////////////
	function CalculeMatch($ligueId){
	
	global $connCM;
	$saisonId="null";
		$DMX=4;
	//if(!isset($deSyncMatch))
	//	{$ligueId = $monGet;}
//	if(!isset($ligueId))
//	$ligueId=3;
//	$ligueId = trouveIDParNomLigue($monGet);

/////////////////////////////////////////
//
//	Ajout 4 mai 2012
//	Retourner que les matchs de la saison active.
//
///////////////////////////////////////////
/*
if(!strcmp($saisonId,"null")&&strcmp($ligueId,"null"))// Spécifie par la ligue
{
	$saisonId = trouveSaisonActiveDeLigueId($ligueId);
//	$saisonId =2;
	$prSaison = mysql_query("SELECT premierMatch FROM TableSaison where saisonId =$saisonId")
or die(mysql_error());  
$premierMatch=mysql_result($prSaison, 0);
	$drSaison = mysql_query("SELECT dernierMatch FROM TableSaison where saisonId =$saisonId")
or die(mysql_error());  
$dernierMatch=mysql_result($drSaison, 0);
}




*/




///////////////////////

	$nomEq = array();
	$qSelEq="SELECT * FROM TableEquipe WHERE ligue_equipe_ref = '{$ligueId}'";
//	echo $qSelEq;
$resultEquipe = mysqli_query($connCM,$qSelEq)
or die(mysqli_error($connCM));  
		$Ine=0;

		
while($rangeeEq=mysqli_fetch_array($resultEquipe))
{
	//$nomEq[$Ine] = trouveNomParIDEquipe($rangeeEq['equipe_id']);
		$nomEq[$Ine] = $rangeeEq['equipe_id'];
	$Ine++;
}	
	$eqDomID = 1;
$eqVisID = 2;
	// Retrieve all the data from la table


	
	//////////////////////////////////
	//
	//	Vérification de l'inscription
	// des matchs dans TableMatch
	//////////////////////////////////
											//	AND statut='F'
$sqlVerifMatch = mysqli_query($connCM, "SELECT * 
									FROM TableMatch 
								WHERE ligueRef = '{$ligueId}' 
								
									")
or die(mysqli_error($connCM));  
$listeMatchEnr = array();	
$IV=0;	
while($rangeeVM=mysqli_fetch_array($sqlVerifMatch))
{
	//$nomEq[$Ine] = trouveNomParIDEquipe($rangeeEq['equipe_id']);
	$listeMatchEnr[$IV]=$rangeeVM['matchIdRef'];
	$IV++;
}	
	
$aEnr = array();
$matchEnCours = array();
$Ine=0;
while($Ine<count($nomEq))// Nombre d'équipe dans la ligue.
{

$sqlVerifEvent = mysqli_query($connCM, "SELECT * 
								FROM TableEvenement0 
								WHERE equipe_event_id = '{$nomEq[$Ine]}' 
								GROUP BY match_event_id")
or die(mysqli_error($connCM));  
$Ieq =0;

while($rangeeVE=mysqli_fetch_array($sqlVerifEvent))				// Vérification: Est-ce que tout les évènements font partis d'un match enregistré.
	{
	$Ienr =0;
	$boule = 0;
	if(!in_array($rangeeVE['match_event_id'],$listeMatchEnr))														// Si on n'a pas trouvé
		{
			array_push($aEnr,$rangeeVE['match_event_id']);
//			$cAEnr=count($aEnr);								Simplification du code.
//			$aEnr[$cAEnr]=$rangeeVE['match_event_id'];					// On met en file pour enregistrer.
		}
	else
		{array_push($listeMatchEnr,$rangeeVE['match_event_id']);}	
//	$listeMatchEnr[$Ienr]=$rangeeVE['match_event_id'];
	}

	$Ine++;
}
	
	$Iae =0;
	$Inon10 =0;
	
//////////////////////////////////////////////
//
///	// Inscription si nécessaire.
	//
	///////////////////////////////////
	
	
//	echo count($aEnr);
while($Iae<count($aEnr)) // Tous les matchs a être recalculés pour enregistrement.
	{
	/*$matchAEnr = parseMatchID_CM($aEnr[$Iae]);
	if(isset($matchAEnr['ligueId']))
		{$ligueId=$matchAEnr['ligueId'];}
	else {
		$ligueId=devineLigueId($matchAEnr['dom'],$matchAEnr['vis']);
	}*/
//	$eDom = trouveIDParNomEquipe($matchAEnr['dom']);
//	$eVis = trouveIDParNomEquipe($matchAEnr['vis']);
	$eDom = trouveIDParNomEqEtLigue($matchAEnr['dom'],$ligueId);
	$eVis = trouveIDParNomEqEtLigue($matchAEnr['vis'],$ligueId);
//echo "  ".json_encode($matchAEnr)." / ".$matchID."  /  "." D ".$eDom." V ".$eVis;
	//////////  Section pour compatibilité avec quipe par parseMatchId.
	

			$eqFake=array();
		$rEqFake = mysqli_query($connCM,"SELECT equipe_event_id, chrono, event_id ,joueur_event_ref
									FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' AND code=10 AND souscode=0");
						
	if(mysqli_num_rows($rEqFake)>0)
	{
		$eqFake=mysqli_fetch_row($rEqFake);
		$aDate = date('Y-m-d H:i:s', $eqFake[1]/1000);
								
	if($eDom==0||$eqFake[2]>20000)  // On force la nouvelle m�thode � partir d'une date donn�e.
		{$eDom=floor($eqFake[0]/10000);
		$ligueId=$eqFake[3];}
	 
	if($eVis==0||$eqFake[2]>20000)
	{
		/*	$eqFake=array();
		$rEqFake = mysql_query("SELECT equipe_event_id 
									FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' AND code=10 AND souscode=0");
		$eqFake=mysql_fetch_row($rEqFake);*/
		$eVis=$eqFake[0]%10000;
								
	}
	 }
	 
//////////////////// Fin de la section.


	$aDate = $matchAEnr['date'];
	unset($rEnr);			// unset n'avait pas réglé le problème lorsqu'implanté.
	unset($rPeriode);
	unset($matchFini);
	unset($compteDom);
	unset($compteVis);

	// Vérification s'il y a une inscription dans tablematch
	$rEnr = mysqli_query($connCM,"SELECT * 
									FROM TableMatch 
								WHERE matchIdRef = '{$aEnr[$Iae]}'")
		or die(mysqli_error($connCM));  
	$isEnr = mysqli_num_rows($rEnr);		

	// Obtention du code de période pour tableevenement0
	$rPeriode = mysqli_query($connCM,"SELECT MAX(souscode) 
								FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' 
								AND code=11")
		or die(mysqli_error($connCM));  
		
		if(mysqli_num_rows($rPeriode)>0){
		$statutAr=mysqli_fetch_row($rPeriode);
		if($statutAr['souscode']<10)
		{$statut=$statutAr['souscode'];}
		else {
			$statut=$statutAr['souscode']%10;
			$statut=$statut.'P';
		}}
		else{
			$statut=0;
		}
		
		

	//Vérifivation si le match est complet pour enregistrement définitif.
	$matchFini = mysqli_query($connCM,"SELECT * 
								FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' 
								AND code=10 
								AND souscode=10")
		or die(mysqli_error($connCM));  
	$fini = mysqli_num_rows($matchFini);		
	
	
	///Compte le score
	$compteDom = mysqli_query($connCM,"SELECT * 
								FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' 
									AND code=0 
									AND equipe_event_id =  '{$eDom}'")
		or die(mysqli_error($connCM));  

	$compteVis = mysqli_query($connCM,"SELECT * 
								FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' 
									AND code=0 
									AND equipe_event_id = '{$eVis}'")
		or die(mysqli_error($connCM));  
	$cDom = mysqli_num_rows($compteDom);
	$cVis = mysqli_num_rows($compteVis);		
								

	

	if($fini>0)
		{
			
			if($cDom==$cVis)
			{
					$cFD=0;
					$cFV=0;
					
					$resFus= mysqli_query($connCM,"SELECT  * FROM TableEvenement0 
										WHERE match_event_id = '{$aEnr[$Iae]}' AND code=2 AND souscode=1") or die(mysqli_error($connCM)); 
									 
					while($rangFus=mysqli_fetch_array($resFus))
					{
							if($rangFus['equipe_event_id']==$eDom)
								$cFD++;
							if($rangFus['equipe_event_id']==$eVis)
								$cFV++;														
					}				
					if($cFD>$cFV)
						$cDom++;
					if($cFV>$cFD)
						$cVis++;
			}
			
			
			if($isEnr==0)
				{$retour = mysqli_query($connCM,"INSERT 
								INTO TableMatch 
									(eq_dom, score_dom, eq_vis, score_vis, matchIdRef, ligueRef, date,statut) 
								VALUES 
									('{$eDom}', '{$cDom}', '{$eVis}', '{$cVis}','{$aEnr[$Iae]}','{$ligueId}','{$aDate}','F')")or die(mysqli_error($connCM)."INSERT 	INTO TableMatch");	
				
							$message = "Création match dans scriptsphp/calculeMatch, 1er appel.";
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/logTest.txt', $log, FILE_APPEND);	
				}
			else
				{$retour = mysqli_query($connCM,"UPDATE TableMatch
											SET score_dom='{$cDom}', score_vis='{$cVis}' ,statut='F'
											WHERE matchIdRef='{$aEnr[$Iae]}'");
				}
				
		}
	else{
			if($isEnr==0)
				{$retour = mysqli_query($connCM,"INSERT 
								INTO TableMatch 
									(eq_dom, score_dom, eq_vis, score_vis, matchIdRef, ligueRef, date,statut) 
								VALUES 
									('{$eDom}', '{$cDom}', '{$eVis}', '{$cVis}','{$aEnr[$Iae]}','{$ligueId}','{$aDate}','{$statut}')")or die(mysqli_error($connCM)."INSERT 	INTO TableMatch");	
							$message = "Création match dans scriptsphp/calculeMatch, 2e appel.";
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/logTest.txt', $log, FILE_APPEND);		
			
			}
			else
				{$retour = mysqli_query($connCM,"UPDATE TableMatch
											SET score_dom='{$cDom}', score_vis='{$cVis}' ,statut='{$statut}'
											WHERE matchIdRef='{$aEnr[$Iae]}'	");
				}
/*	
		$matchEnCours[$Inon10]['matchID']=$aEnr[$Iae];
		$matchEnCours[$Inon10]['date']=$aDate;
		$matchEnCours[$Inon10]['eqDom']=$matchAEnr['dom'];
		$matchEnCours[$Inon10]['eqVis']=$matchAEnr['vis'];
		$matchEnCours[$Inon10]['equipeScoreDom']=$cDom;
		$matchEnCours[$Inon10]['equipeScoreVis']=$cVis;
		$Inon10++;*/
		}
	$Iae++;
	//echo $matchAEnr['date']." / ".$matchAEnr['dom']." / ".$matchAEnr['vis']." </br>";
	}
	}
	

mysqli_close($connCM);
?>

