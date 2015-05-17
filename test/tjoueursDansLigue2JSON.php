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
$tableSaison = 'TableSaison';

$selection = $_POST['selection'];
$ligue = $_POST['ligue'];
$equipe = $_GET['equipe'];
$matchId = $_GET['matchId'];

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

/////////////////////////////////////////////////////////////
// 
//

function trouveNomJoueurParID($ID){ 
unset($resultJoueur);
$resultJoueur = mysql_query("SELECT * FROM TableJoueur WHERE joueur_id = '{$ID}'")
or die(mysql_error());  
while($rangeeJoueur=mysql_fetch_array($resultJoueur))
	{if(strcmp($rangeeJoueur['NomJoueur'],"null"))
		  {return ($rangeeJoueur['NomJoueur']);}
	else { return ("Anonyme"); }}		   
 return ("Anonyme"); 
} 


/////////////////////////////////////////////////////////////
//
//

function parseMatchID($ID){
	 
$monMatch['date'] = substr($ID,0,stripos($ID,'_'));
$longueur = strlen($monMatch['date']);
$monMatch['dom'] = substr($ID,stripos($ID,'_')+1,stripos(substr($ID,$longueur+2),'_')+1);
$monMatch['vis'] = substr($ID,strripos($ID,'_')+1);
return $monMatch;
} 


/////////////////////////////////////////////////////
//
//   Trouve ID de la ligue � partir du nom.
//
////////////////////////////////////////////////////

function trouveIDParNomLigue($nomLi)
{


$resultLigue = mysql_query("SELECT * FROM {$tableLigue}")
or die(mysql_error());  
while($rangeeLigue=mysql_fetch_array($resultLigue))
{
		if(!strcmp($rangeeLigue['Nom_Ligue'],$ligue))
	{$ligueSelect =$rangeeLigue['ID_Ligue'];
	}
		// Prend le ID de la ligue pour trouver les �quipes.
}

return $LigueID;
}


/////////////////////////////////////////////////////
	//
//   Trouve ID de l'equipe � partir du nom.
//
////////////////////////////////////////////////////
function trouveNomParIDEquipe($IEq)
{


$resultEquipe = mysql_query("SELECT * FROM {$tableEquipe}")
or die(mysql_error());  
while($rangeeEquipe=mysql_fetch_array($resultEquipe))
{
		if(!strcmp($rangeeEquipe['nom_equipe'],$equipe))
	{$equipeID =$rangeeEquipe['equipe_id'];// Ce sont de INT
	}
}
return $NomEquipe;
}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////




$getLigue = $_GET["ligueId"];



$resultSaison = mysql_query("SELECT * FROM TableSaison WHERE ligueRef = '{$getLigue}' AND saisonActive=1")
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
$resultMatch = mysql_query("SELECT * FROM TableMatch WHERE ligueRef=$getLigue")
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

$resultEvent = mysql_query("SELECT TableEvenement0.*, TableEquipe.nom_equipe,TableEquipe.ficId, TableEquipe.ligue_equipe_ref, TableJoueur.NomJoueur, TableJoueur.NumeroJoueur,TableJoueur.Ligue   
				FROM TableJoueur 
					JOIN TableEvenement0
						 ON (TableJoueur.joueur_id=TableEvenement0.joueur_event_ref) 
					LEFT JOIN TableEquipe
						 ON (TableJoueur.equipe_id_ref=TableEquipe.equipe_id) 
						 
					WHERE  match_event_id = '{$lesMatchs[$Im]}'")
or die(mysql_error());  

while($rangeeEv=mysql_fetch_array($resultEvent))
	{
				$JoueurSommeEvenement[$I0]['event_id']= $rangeeEv['event_id'];
				$JoueurSommeEvenement[$I0]['joueur_event_ref']= $rangeeEv['joueur_event_ref'];
				$JoueurSommeEvenement[$I0]['code']= $rangeeEv['code'];
				$JoueurSommeEvenement[$I0]['souscode']= $rangeeEv['souscode'];
				$JoueurSommeEvenement[$I0]['nom_equipe']= $rangeeEv['nom_equipe'];
				$JoueurSommeEvenement[$I0]['ficId']= $rangeeEv['ficId'];
				$JoueurSommeEvenement[$I0]['nom']= $rangeeEv['NomJoueur'];
				$JoueurSommeEvenement[$I0]['numero']= $rangeeEv['NumeroJoueur'];
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
		$rangeeStats[$Itrouve][4]=$JoueurSommeEvenement[$Ievent]['nom_equipe'];
		$rangeeStats[$Itrouve][5]=$JoueurSommeEvenement[$Ievent]['ficId'];
		$rangeeStats[$Itrouve][6]=$ligneEvent1;
		$rangeeStats[$Itrouve][7]=$JoueurSommeEvenement[$Ievent]['numero'];
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
			{if(!strcmp($rangeeStats[$indexJoueur][0],$JoueurSommeEvenement[$Ievent]['nom'])&&$JoueurSommeEvenement[$Ievent]['souscode']==0)
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
			if($rangeeStats[$Ievent][3]!=0)
			{
$stats[$Ievent]['nom'] = $rangeeStats[$Ievent][0];
$stats[$Ievent]['nbButs'] = $rangeeStats[$Ievent][1];
$stats[$Ievent]['nbPasses'] = $rangeeStats[$Ievent][2];
$stats[$Ievent]['pj'] = $rangeeStats[$Ievent][3];
$stats[$Ievent]['nomEquipe'] = $rangeeStats[$Ievent][4];
$stats[$Ievent]['ficId'] = $rangeeStats[$Ievent][5];
$stats[$Ievent]['id'] = $rangeeStats[$Ievent][6];
$stats[$Ievent]['numero'] = $rangeeStats[$Ievent][7];

				$JSONstring .= json_encode($stats[$Ievent]).",";
			}
		$Ievent++;
		}  
			$JSONstring = substr($JSONstring, 0,-1);
	$JSONstring .= "]}";
	
echo $JSONstring;
		


?>
