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

function trouveNomJoueurParID($ID){ 

$resultJoueur = mysql_query("SELECT * FROM TableJoueur WHERE joueur_id = '{$ID}'")
or die(mysql_error());  
if($rangeeJoueur=mysql_fetch_array($resultJoueur))
		  return ($rangeeJoueur['NomJoueur']); 
else { return ("Anonyme"); }
} 

function ismidv4()//is MatchId version 4 variables
{$i1=stripos($ID,'_');
$i2=stripos($ID,'_',$i1+1);
$i3=stripos($ID,'_',$i2+1);
if($i3==false)
{return false;}
else 
	{return true;}
}

/////////////////////////////////////////////////////////////
//
//

function parseMatchID($ID){
	 
//$monMatch['date'] = str_replace('/', '-', substr($ID,0,stripos($ID,'_')));
$i1=stripos($ID,'_');
$i2=stripos($ID,'_',$i1+1);
$i3=stripos($ID,'_',$i2+1);
$monMatch['date'] = substr($ID,0,$i1);
$i1=stripos($ID,'_');

$longueur = strlen($monMatch['date']);
$monMatch['dom'] = substr($ID,$i1+1,$i2-$i1-1);
if($i3!=false)
	$monMatch['vis'] = substr($ID,$i2+1,$i3-$i2-1);
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





/////////////////////////////////////////////////////////////
// 
//

function trouveSaisonActiveDeLigueId($ID){ 
$rfSaison = mysql_query("SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ID}' and saisonActive=1")
or die(mysql_error());  
 return (mysql_result($rfSaison, 0)); 
} 


///////////////////////////////////
	//
	//	 Lister les équipes de la ligue
	//
	///////////////////////////////////
	
	$monGet = $_POST["ligueId"];	
	$saisonId="null";
	
	if(!isset($deSyncMatch))
		{$ligueId = $monGet;}
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

							$message = "Exécution stats2/listeMatchs2JSON.";
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/logTest.txt', $log, FILE_APPEND);	


	$nomEq = array();
$resultEquipe = mysql_query("SELECT * FROM TableEquipe WHERE ligue_equipe_ref = '{$ligueId}'")
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
								WHERE ligueRef = '{$ligueId}' 
									AND statut='F'")
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
								GROUP BY match_event_id")
or die(mysql_error());  
$Ieq =0;

while($rangeeVE=mysql_fetch_array($sqlVerifEvent))				// Vérification: Est-ce que tout les évènements font partis d'un match enregistré.
	{
	$Ienr =0;
	$boule = 0;
	while($Ienr<count($listeMatchEnr))  // Tous les matchs au statut 'F' de la ligue sélectionnée.
		{
		if(!strcmp($listeMatchEnr[$Ienr], $rangeeVE['match_event_id']))// Si l'équipe voulue a participée au match
			{$boule=1;}													// On a trouvé, aucune action a prendre.
		$Ienr++;
		}
	if($boule==0)														// Si on n'a pas trouvé
		{
//			array_push($aEnr,$rangeeVE['match_event_id'])
			$cAEnr=count($aEnr);
			$aEnr[$cAEnr]=$rangeeVE['match_event_id'];					// On met en file pour enregistrer.
		}	
	$listeMatchEnr[$Ienr]=$rangeeVE['match_event_id'];
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
	$matchAEnr = parseMatchID($aEnr[$Iae]);
//	$eDom = trouveIDParNomEquipe($matchAEnr['dom']);
//	$eVis = trouveIDParNomEquipe($matchAEnr['vis']);
	$eDom = trouveIDParNomEqEtLigue($matchAEnr['dom'],$ligueId);
	$eVis = trouveIDParNomEqEtLigue($matchAEnr['vis'],$ligueId);
//echo "  ".json_encode($matchAEnr)." / ".$matchID."  /  "." D ".$eDom." V ".$eVis;


	//////////  Section pour compatibilité avec quipe par parseMatchId.	

			$eqFake=array();
		$rEqFake = mysql_query("SELECT equipe_event_id, chrono 
									FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' AND code=10 AND souscode=0;");
								$eqFake=mysql_fetch_row($rEqFake);
						

	$aDate = date('Y-m-d H:i:s', $eqFake[1]/1000);
								
	if($eDom==0)
		{$eDom=floor($eqFake[0]/10000);}
	if($eVis==0)
	{
			$eqFake=array();
		$rEqFake = mysql_query("SELECT equipe_event_id 
									FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' AND code=10 AND souscode=0;");
								$eqFake=mysql_fetch_row($rEqFake);
								$eVis=$eqFake[0]%10000;
								
	}
//////////////////// Fin de la section.


	//$aDate = $matchAEnr['date'];
	unset($rEnr);			// unset n'avait pas réglé le problème lorsqu'implanté.
	unset($rPeriode);
	unset($matchFini);
	unset($compteDom);
	unset($compteVis);

	// Vérification s'il y a une inscription dans tablematch
	$rEnr = mysql_query("SELECT * 
									FROM TableMatch 
								WHERE matchIdRef = '{$aEnr[$Iae]}'")
		or die(mysql_error());  
	$isEnr = mysql_num_rows($rEnr);		

	// Obtention du code de période pour tableevenement0
	$rPeriode = mysql_query("SELECT MAX(souscode) 
								FROM TableEvenement0 
								WHERE match_event_id = '{$aEnr[$Iae]}' 
								AND code=11")
		or die(mysql_error());  
		$statutAr=mysql_fetch_row($Periode);
		if($statutAr['souscode']<10)
		{$statut=$statutAr['souscode'];}
		else {
			$statut=$statutAr['souscode']%10;
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
				
							$message = "Création match dans stats2/listeMatchs2JSON, 1er appel.";
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/logTest.txt', $log, FILE_APPEND);	
				}
			else
				{$retour = mysql_query("UPDATE TableMatch
											SET score_dom='{$cDom}', score_vis='{$cVis}' ,statut='F'
											WHERE matchIdRef='{$aEnr[$Iae]}'");
				}
				
		}
	else{
			if($isEnr==0)
				{$retour = mysql_query("INSERT 
								INTO TableMatch 
									(eq_dom, score_dom, eq_vis, score_vis, matchIdRef, ligueRef, date,statut) 
								VALUES 
									('{$eDom}', '{$cDom}', '{$eVis}', '{$cVis}','{$aEnr[$Iae]}','{$ligueId}','{$aDate}','{$statut}')")or die(mysql_error()."INSERT 	INTO TableMatch");	
				
							$message = "Création match dans stats2/listeMatchs2JSON, 2e appel.";
							$log  = $message.' - '.date("F j, Y, g:i:s a").PHP_EOL.
	        				"-------------------------".PHP_EOL;
							file_put_contents('../test/logTest.txt', $log, FILE_APPEND);	
			}
			else
				{$retour = mysql_query("UPDATE TableMatch
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
	}
	
	
////////////////////////////////////////
//
//  Bâtir JSON	
//	
///////////////////////////////////////

$JSONstring = "{";
//$JSONstring .=$aEnr[0];
$JSONstring .="\"matchs\": [";
//$JSONstring .=$premierMatch;
//$JSONstring .=$dernierMatch;

$liste=array();
$Ine=0;
	
unset($resultEvent);	
unset($rangeeEv);	
$resultEvent = mysql_query("SELECT TableEquipe.*, Ligue.*, TableMatch.*,TableEvenement0.* 
								FROM TableMatch 
								JOIN Ligue
									ON TableMatch.ligueRef=Ligue.ID_Ligue
								JOIN TableEvenement0
									ON TableMatch.matchIdRef=TableEvenement0.match_event_id
								LEFT JOIN TableEquipe
									ON TableMatch.eq_dom=TableEquipe.equipe_id
								WHERE TableEvenement0.code='10' 
									AND TableEvenement0.souscode='0'
									AND TableMatch.ligueRef='{$ligueId}'
									")

or die(mysql_error());  
$Ieq =0;
$mesFic= array();
$IF=0;
/*$JSONstring .= $ligueId;
$JSONstring .= $premierMatch;
$JSONstring .= $dernierMatch;
*/
 while($rangeeEv=mysql_fetch_array($resultEvent))
{
	$trouveDom=0;
	$trouveVis=0;
	for($a=0;$a<$IF;$a++)
	{
			if($mesFic[$a]['eqId']==$rangeeEv['eq_dom'])
				{
					$trouveDom=$mesFic[$a]['ficId'];
				}
			if($mesFic[$a]['eqId']==$rangeeEv['eq_vis'])
				{
					$trouveVis=$mesFic[$a]['ficId'];
				}
	}
	
			if($trouveDom==0)
		{
					$mesFic[$IF]=array();
					$mesFic[$IF]['eqId']=$rangeeEv['eq_dom'];
					$rFic = mysql_query("SELECT ficId
													FROM TableEquipe
													WHERE equipe_id= '{$rangeeEv['eq_dom']}'")or die(mysql_error());  
					$tmpFic=mysql_fetch_row($rFic);
					$mesFic[$IF]['ficId']=$tmpFic[0];
					$trouveDom=$tmpFic[0];
		}
			if($trouveVis==0)
		{
					$mesFic[$IF]=array();
					$mesFic[$IF]['eqId']=$rangeeEv['eq_vis'];
					$rFic = mysql_query("SELECT ficId
													FROM TableEquipe
													WHERE equipe_id= '{$rangeeEv['eq_vis']}'")or die(mysql_error());  
					$tmpFic=mysql_fetch_row($rFic);
					$mesFic[$IF]['ficId']=$tmpFic[0];
					$trouveVis=$tmpFic[0];	
		}		
		
		
	
	unset($matchID);
	$matchID = $rangeeEv['match_event_id'];

	$leMatch = parseMatchID($matchID);
	$dateMatch = strtotime($leMatch['date']);
	//$pm = strtotime($premierMatch);
	//$dm = strtotime($dernierMatch);
	
//	if(($dateMatch>=$pm)&&($dateMatch<=$dm))
//	{
	$JSONstring .= "{\"matchID\": \"".$matchID."\",";
	$JSONstring .="\"date\": \"".$rangeeEv['date']."\",";
	$JSONstring .="\"eqDom\": \"".trouveNomParIDEquipe($rangeeEv['eq_dom'])."\","; 
	$JSONstring .="\"eqDomId\": \"".$rangeeEv['eq_dom']."\","; 
	$JSONstring .="\"ficIdDom\": \"".$trouveDom."\","; 
	//	$JSONstring .="\"eqDom\": \"".$leMatch['dom']."\",";
	$JSONstring .="\"equipeScoreDom\": \"".$rangeeEv['score_dom']."\",";
	$JSONstring .="\"equipeScoreVis\": \"".$rangeeEv['score_vis']."\",";
	$JSONstring .="\"statut\": \"".$rangeeEv['statut']."\",";
	//$JSONstring .="\"eqVis\": \"".$leMatch['vis']."\"},";
		$JSONstring .="\"eqVisId\": \"".$rangeeEv['eq_vis']."\","; 
	$JSONstring .="\"ficIdVis\": \"".$trouveVis."\","; 
	$JSONstring .="\"eqVis\": \"".trouveNomParIDEquipe($rangeeEv['eq_vis'])."\"},"; 
	array_push($liste,$matchID);
//	}
}

$Ine++;


if(!strcmp(",", substr($JSONstring,-1)))// Pour �viter les vides;
							{$JSONstring = substr($JSONstring, 0,-1);}
							$JSONstring .= "]}";
								
//echo json_encode($Sommaire);
if($deSyncMatch!=1)
{
echo $JSONstring;
}
//	echo json_encode($matchEnCours);
//	echo json_encode($liste);


?>

