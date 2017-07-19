<?php


/////////////////////////////////////////////////////////////
//
//  Définitions des variables
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
// 	Connections é la base de données
//
////////////////////////////////////////////////////////////

$erreur = file_put_contents('../test/logTest.txt', "test", FILE_APPEND);	
error_log($erreur,3,"error_log");

	if(!isset($deSyncMatch))
{

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");
}

/////////////////////////////////////////////////////////////
//
//



/////////////////////////////////////////////////////////////
//
//

function parseMatchID_AM($ID){
	 
//$monMatch['date'] = str_replace('/', '-', substr($ID,0,stripos($ID,'_')));
$i1=stripos($ID,'_');
$i2=stripos($ID,'_',$i1+1);
$i3=stripos($ID,'_',$i2+1);
$monMatch['date'] = substr($ID,0,$i1);
$i1=stripos($ID,'_');

$longueur = strlen($monMatch['date']);
$monMatch['dom'] = substr($ID,$i1+1,$i2-$i1-1);
if($i3!=false)
	{$monMatch['vis'] = substr($ID,$i2+1,$i3-$i2-1);
	$monMatch['ligueId'] = substr($ID,$i3+1,$i3-$i2-1);}
else{$monMatch['vis']= substr($ID,$i2+1);}
	return $monMatch;
} 

/////////////////////////////////////////////////////
	//
//   Trouve ID de l'equipe é partir du nom.
//
////////////////////////////////////////////////////

function trouveIDParNomEquipe($nomEq)
{
$resultEquipe = mysql_query("SELECT * FROM TableEquipe")
or die(mysql_error());  
while($rangeeEquipe=mysql_fetch_array($resultEquipe))
{
		if(!strcmp($rangeeEquipe['nom_equipe'],$nomEq))
	{$equipeID =$rangeeEquipe['equipe_id'];// Ce sont de INT
	}
}
return $equipeID;
}

function trouveIDParNomEqEtLigue($nomEq,$ligueId)
{
$resultEquipe = mysql_query("SELECT * FROM TableEquipe
										JOIN abonEquipeLigue
											ON (TableEquipe.equipe_id =abonEquipeLigue.equipeId) 
										WHERE 
											abonEquipeLigue.ligueId='{$ligueId}'
										AND
											TableEquipe.nom_equipe='{$nomEq}'")
or die(mysql_error());  
while($rangeeEquipe=mysql_fetch_array($resultEquipe))
{
	{return $rangeeEquipe['equipe_id'];// Ce sont de INT
	}
}
$resultEquipe = mysql_query("SELECT * FROM TableEquipe
										WHERE 
											ligue_equipe_ref='{$ligueId}'
										AND
											nom_equipe='{$nomEq}'")
or die(mysql_error());  
	while($rangeeEquipe=mysql_fetch_array($resultEquipe))
	{
		{return $rangeeEquipe['equipe_id'];// Ce sont de INT
		}
	}


}


/////////////////////////////////////////////////////
	//
//   Trouve ID de la ligue é partir du nom.
//
////////////////////////////////////////////////////

function trouveIDParNomLigue($nomLi)
{
$resultLigue = mysql_query("SELECT * FROM Ligue")
or die(mysql_error());  
while($rangeeLigue=mysql_fetch_array($resultLigue))
{
		if(!strcmp($rangeeLigue['Nom_Ligue'],$nomLi))
	{$LigueID =$rangeeLigue['ID_Ligue'];// Ce sont de INT
	}
}
return $LigueID;
}

/////////////////////////////////////////////////////
	//
//   Trouve Nom de l'equipe é partir du ID.
//
////////////////////////////////////////////////////
function trouveNomParIDEquipe($IEq)
{
$resultEquipe2 = mysql_query("SELECT * FROM TableEquipe WHERE equipe_id='{$IEq}'")
or die(mysql_error());  
while($rangeeEquipe2=mysql_fetch_array($resultEquipe2))
{
			
		if($rangeeEquipe2['equipe_id']==$IEq)
	{
	$NomEquipe =$rangeeEquipe2['nom_equipe'];// Ce sont de INT
	}
}
return $NomEquipe;
}



////////////////////////////////////
//		LOG

							$message = "Éexcution de scriptsphp/actualiseMatch.";
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/logTest.txt', $log, FILE_APPEND);	

/////////////////////////////////////////////////////////////
// 
//

function trouveSaisonActiveDeLigueId($ID){ 
$rfSaison = mysql_query("SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ID}' and saisonActive=1")
or die(mysql_error());  
 return (mysql_result($rfSaison, 0)); 
} 


function devineLigueId($dom,$vis)
{
$rDom = mysql_query("SELECT ligue_equipe_ref FROM TableEquipe WHERE nom_equipe='$dom' ORDER BY ligue_equipe_ref ASC")
or die(mysql_error());  
$rVis= mysql_query("SELECT ligue_equipe_ref FROM TableEquipe WHERE nom_equipe='$vis' ORDER BY ligue_equipe_ref ASC")
or die(mysql_error());  
$nbDom=mysql_num_rows($rDom);
$nbVis=mysql_num_rows($rVis);
if($nbDom==1&&$nbVis<=1)
{$arEq = mysql_fetch_row($rDom);
	return $arEq[0];}
if($nbDom==0&&$nbVis==1)
{$arEq = mysql_fetch_row($rDom);
return $arEq[0];}
if($nbDom>0)
{
$arEq = mysql_fetch_row($rDom);
return $arEq[0];}
if($nbVis>0)
{
$arEq = mysql_fetch_row($rVis);
return $arEq[0];}
return 0;

	
	
}

	$nomEq = array();
$resultEquipe = mysql_query("SELECT * FROM TableEquipe WHERE 1")
or die(mysql_error());  
		$Ine=0;

		
while($rangeeEq=mysql_fetch_array($resultEquipe))
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
	
$sqlVerifMatch = mysql_query("SELECT * 
									FROM TableMatch 
								WHERE statut='F'")
or die(mysql_error());  
$listeMatchEnr = array();	
$IV=0;	
while($rangeeVM=mysql_fetch_array($sqlVerifMatch))
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

$sqlVerifEvent = mysql_query("SELECT * 
								FROM TableEvenement0 
								WHERE equipe_event_id = '{$nomEq[$Ine]}'
								AND code=3 
								GROUP BY match_event_id")
or die(mysql_error());  
$Ieq =0;

while($rangeeVE=mysql_fetch_array($sqlVerifEvent))				// Vérification: Est-ce que tout les évènements font partis d'un match enregistré.
	{
	$Ienr =0;
	$boule = 0;
//			array_push($aEnr,$rangeeVE['match_event_id']);
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
	$matchAEnr = parseMatchID_AM($aEnr[$Iae]);
	if(isset($matchAEnr['ligueId']))
		{$ligueId=$matchAEnr['ligueId'];}
	else {
		$ligueId=devineLigueId($matchAEnr['dom'],$matchAEnr['vis']);
	}
//	$eDom = trouveIDParNomEquipe($matchAEnr['dom']);
//	$eVis = trouveIDParNomEquipe($matchAEnr['vis']);
	$eDom = trouveIDParNomEqEtLigue($matchAEnr['dom'],$ligueId);
	$eVis = trouveIDParNomEqEtLigue($matchAEnr['vis'],$ligueId);

	//echo "  ".json_encode($matchAEnr)." / ".$matchID."  /  "." D ".$eDom." V ".$eVis;


	//////////  Section pour compatibilité avec quipe par parseMatchId.	

			$eqFake=array();
		$rEqFake = mysql_query("SELECT equipe_event_id, chrono, event_id ,joueur_event_ref
									FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' AND code=10 AND souscode=0");
						
	if(mysql_num_rows($rEqFake)>0)
	{
		$eqFake=mysql_fetch_row($rEqFake);
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


	//$aDate = $matchAEnr['date'];
	unset($rEnr);			// unset n'avait pas réglé le problème lorsqu'implanté.
	unset($rPeriode);
	unset($matchFini);
	unset($compteDom);
	unset($compteVis);

	// Vérification s'il y a une inscription dans tablematch

	// Obtention du code de période pour tableevenement0
	$rPeriode = mysql_query("SELECT MAX(souscode) 
								FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' 
								AND (code=11 OR code =10)")
		or die(mysql_error());  
		$statutAr=mysql_fetch_row($rPeriode);
		if($statutAr[0]<10)
		{$statut=$statutAr[0];}
		else {
			$statut=$statutAr[0]%10;
			$statut=$statut.'P';
		}
		
		

	//Vérifivation si le match est complet pour enregistrement définitif.
	$matchFini = mysql_query("SELECT * 
								FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' 
								AND code=10 
								AND souscode=10")
		or die(mysql_error());  
	$fini = mysql_num_rows($matchFini);		
	
	
	///Compte le score
	$compteDom = mysql_query("SELECT * 
								FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' 
									AND code=0 
									AND equipe_event_id =  '{$eDom}'")
		or die(mysql_error());  

	$compteVis = mysql_query("SELECT * 
								FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' 
									AND code=0 
									AND equipe_event_id = '{$eVis}'")
		or die(mysql_error());  
	$cDom = mysql_num_rows($compteDom);
	$cVis = mysql_num_rows($compteVis);		
								


				$rEnr = mysql_query("SELECT * 
									FROM TableMatch 
								WHERE matchIdRef = '{$aEnr[$Iae]}'")
		or die(mysql_error());  
	$isEnr = mysql_num_rows($rEnr);		
			
	

	if($fini>0)
		{
			
			if($cDom==$cVis)
			{
					$cFD=0;
					$cFV=0;
					
					$resFus= mysql_query("SELECT  * FROM TableEvenement0 
										WHERE match_event_id = '{$aEnr[$Iae]}' AND code=2 AND souscode=1") or die(mysql_error()); 
									 
					while($rangFus=mysql_fetch_array($resFus))
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
				{$retour = mysql_query("INSERT 
								INTO TableMatch 
									(eq_dom, score_dom, eq_vis, score_vis, matchIdRef, ligueRef, date,statut) 
								VALUES 
									('{$eDom}', '{$cDom}', '{$eVis}', '{$cVis}','{$aEnr[$Iae]}','{$ligueId}','{$aDate}','F')")or die(mysql_error()."INSERT 	INTO TableMatch");	
							$message = "Création match dans scriptsphp/actualiseMatch, 1er appel.";
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/logTest.txt', $log, FILE_APPEND);	
			}
			else
				{$q = "UPDATE TableMatch
											SET score_dom='{$cDom}', score_vis='{$cVis}' ,statut='F'
											WHERE matchIdRef='{$aEnr[$Iae]}'";
						
					$retour = mysql_query($q) or die(mysql_error()."    - ".$q);
//					echo $retour;
				}
				
		}
	else{
			if($isEnr==0)
				{$retour = mysql_query("INSERT 
								INTO TableMatch 
									(eq_dom, score_dom, eq_vis, score_vis, matchIdRef, ligueRef, date,statut) 
								VALUES 
									('{$eDom}', '{$cDom}', '{$eVis}', '{$cVis}','{$aEnr[$Iae]}','{$ligueId}','{$aDate}','{$statut}')")or die(mysql_error()."INSERT 	INTO TableMatch");	

							$message = "Création match dans scriptsphp/actualiseMatch, 2e appel.";
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/logTest.txt', $log, FILE_APPEND);	
				}
			else
				{
						$q ="UPDATE TableMatch
											SET score_dom='{$cDom}', score_vis='{$cVis}' ,statut='{$statut}'
											WHERE matchIdRef='{$aEnr[$Iae]}'";
					$retour = mysql_query($q) or die(mysql_error()."    - ".$q);
//					echo $retour;
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
		//echo $ligueId." / ".$matchAEnr['date']." / ".$matchAEnr['dom']." - ".$eDom." / ".$matchAEnr['vis']." - ".$eVis." </br>";
	
	}
	
	


?>

