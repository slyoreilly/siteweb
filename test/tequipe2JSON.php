<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';
$tableSaison = 'TableSaison';

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

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////




$getEquipe = $_GET["equipeId"];


$rLiEq = mysql_query("SELECT * FROM {$tableEquipe} WHERE equipe_id = '{$getEquipe}'")
or die(mysql_error());  

$rangeeLiEq=mysql_fetch_array($rLiEq);
$Ligue = $rangeeLiEq['ligue_equipe_ref'];


$resultSaison = mysql_query("SELECT * FROM TableSaison WHERE ligueRef = '{$Ligue}' AND saisonActive=1")
or die(mysql_error());  
while($rangeeSaison=mysql_fetch_array($resultSaison))
{
	$premierMatch = $rangeeSaison['premierMatch'];
	$dernierMatch = $rangeeSaison['dernierMatch'];
	$typeSaison = $rangeeSaison['typeSaison'];
}

//  
$lesMatchs = array();
$I2=0;
$resultMatch = mysql_query("SELECT * FROM TableMatch WHERE (eq_vis = '{$getEquipe}' OR eq_dom = '{$getEquipe}')")
or die(mysql_error());  

while($rangeeMatch=mysql_fetch_array($resultMatch))
{
	if($rangeeMatch['date']>=$premierMatch&&$rangeeMatch['date']<=$dernierMatch)
	{
			$lesMatchs[$I2]=$rangeeMatch['matchIdRef'];
			$I2++;
	}
}


$Im = 0;
$JoueurSommeEvenement = array();
$I0=0;
while($Im<count($lesMatchs))
{
	// Retrieve all the data from la table
unset($resultEvent);
unset($rangeeEv);
//$resultEvent = mysql_query("SELECT * FROM TableEvenement0 WHERE equipe_event_id = '{$getEquipe}' AND match_event_id = '{$lesMatchs[$Im]}'")

$resultEvent = mysql_query("SELECT TableEvenement0.*, TableJoueur.NomJoueur, TableJoueur.NumeroJoueur  FROM TableEvenement0 JOIN TableJoueur ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id) WHERE equipe_event_id = '{$getEquipe}' AND match_event_id = '{$lesMatchs[$Im]}'")
or die(mysql_error());  

while($rangeeEv=mysql_fetch_array($resultEvent))
	{
				$JoueurSommeEvenement[$I0]['event_id']= $rangeeEv['event_id'];
				$JoueurSommeEvenement[$I0]['joueur_event_ref']= $rangeeEv['joueur_event_ref'];
				$JoueurSommeEvenement[$I0]['code']= $rangeeEv['code'];
				$JoueurSommeEvenement[$I0]['NumeroJoueur']= $rangeeEv['NumeroJoueur'];
				$JoueurSommeEvenement[$I0]['nom']= $rangeeEv['NomJoueur'];
				$I0++;
	}
	$Im++;
}	

///////////////////////////////////////////////////////////	
//
// 	Construit la liste de joueur, Initialise les stats.

	$rangeeStats=array();
	$Inom = 0;
	$Ibuts = 0;
	$Ipasses = 0;
	$NbEntre=count($JoueurSommeEvenement);
	unset($joueursEntres);
	$joueursEntres = array(); 
	
	$Ievent = 0;
	
	while($Ievent<$NbEntre)
	{
		$ligneEvent0 = $JoueurSommeEvenement[$Ievent]['event_id'];
		$ligneEvent1 = $JoueurSommeEvenement[$Ievent]['joueur_event_ref'];
		$ligneEvent2 = $JoueurSommeEvenement[$Ievent]['code'];
		$Itrouve=0;
		$boule =0;
		while($Itrouve<count($joueursEntres))
		{
		if(!strcmp($joueursEntres[$Itrouve],$ligneEvent1))
			{$boule=1;}
		$Itrouve++;
		}
		if($boule==0)//joueur pas dans la liste
		{$joueursEntres[$Itrouve]= $ligneEvent1;

///////////////////////////
//////  tests...

		
		$rangeeStats[$Itrouve][0]=$JoueurSommeEvenement[$Ievent]['nom'];
		$rangeeStats[$Itrouve][1]=0;
		$rangeeStats[$Itrouve][2]=0;
		$rangeeStats[$Itrouve][3]=0;
		$rangeeStats[$Itrouve][4]=$JoueurSommeEvenement[$Ievent]['NumeroJoueur'];
		}
		
		$Ievent++;
		
	}
	
	///////////////////////////////////////////////////////
	//
	// 	Construit la matrice de stats
	
		
	$NbRangeeStats=count($rangeeStats);
	$Ievent=0;
	while($Ievent<$NbEntre)
	{
		$ligneEvent0 = $JoueurSommeEvenement[$Ievent]['event_id'];
		$ligneEvent1 = $JoueurSommeEvenement[$Ievent]['joueur_event_ref'];
		$ligneEvent3 = $JoueurSommeEvenement[$Ievent]['nom'];
		
		$ligneEvent2 = $JoueurSommeEvenement[$Ievent]['code'];
		switch ($ligneEvent2) {
    	case 0:
			$indexJoueur=0;
			while($indexJoueur<$NbRangeeStats)
			{if(!strcmp($rangeeStats[$indexJoueur][0],$JoueurSommeEvenement[$Ievent]['nom']))
        	{$rangeeStats[$indexJoueur][1]++;}
			$indexJoueur++;
			}
        	break;
    	case 1:
			$indexJoueur=0;
			while($indexJoueur<$NbRangeeStats)
			{if(!strcmp($rangeeStats[$indexJoueur][0],$JoueurSommeEvenement[$Ievent]['nom']))
        	{$rangeeStats[$indexJoueur][2]++;}
			$indexJoueur++;
			}
    	    break;
    	case 2:
    	    break;
		case 3:
			$indexJoueur=0;
			while($indexJoueur<$NbRangeeStats)
			{if(!strcmp($rangeeStats[$indexJoueur][0],$JoueurSommeEvenement[$Ievent]['nom']))
        	{$rangeeStats[$indexJoueur][3]++;}
			$indexJoueur++;
			}
    	  
			
			
    	    break;
					}
		$Ievent++;
	}
	
	//////////////////////////////////////////////////
	//
	// 	Affichage des stats
	$Ievent=0;
$stats=array();
	$JSONstring = "{\"joueurs\": [";

	while($Ievent<$NbRangeeStats)
		{
$stats[$Ievent]['nom'] = $rangeeStats[$Ievent][0];
$stats[$Ievent]['nbButs'] = $rangeeStats[$Ievent][1];
$stats[$Ievent]['nbPasses'] = $rangeeStats[$Ievent][2];
$stats[$Ievent]['pj'] = $rangeeStats[$Ievent][3];
$stats[$Ievent]['no'] = $rangeeStats[$Ievent][4];

				$JSONstring .= json_encode($stats[$Ievent]).",";

		$Ievent++;
		}  
			$JSONstring = substr($JSONstring, 0,-1);
	$JSONstring .= "]}";
	
echo $JSONstring;
		


?>
