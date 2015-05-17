
<html> 
<title>Statistiques par équipes</title> 
<head> 
</head> 
<body> 
<p>


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

$selection = $_POST['selection'];
$ligue = $_POST['ligue'];
$equipe = $_POST['equipe'];

	echo "Point 1";

////////////////////////////////////////////////////////////
//
// 	Connections à la base de données
//
////////////////////////////////////////////////////////////

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

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
//   Trouve ID de la ligue à partir du nom.
//
////////////////////////////////////////////////////

$resultLigue = mysql_query("SELECT * FROM {$tableLigue}")
or die(mysql_error());  
while($rangeeLigue=mysql_fetch_array($resultLigue))
{
		if(!strcmp($rangeeLigue['Nom_Ligue'],$ligue))
	{$ligueSelect =$rangeeLigue['ID_Ligue'];
	}
		// Prend le ID de la ligue pour trouver les équipes.
}

/////////////////////////////////////////////////////
	//
//   Trouve ID de l'equipe à partir du nom.
//
////////////////////////////////////////////////////

$resultEquipe = mysql_query("SELECT * FROM {$tableEquipe}")
or die(mysql_error());  
while($rangeeEquipe=mysql_fetch_array($resultEquipe))
{
		if(!strcmp($rangeeEquipe['nom_equipe'],$equipe))
	{$equipeID =$rangeeEquipe['equipe_id'];// Ce sont de INT
	}
}


//////////////////////////////////////////////////////
//
//  	Section "Statistiques"
//
//////////////////////////////////////////////////////
	
	if(!strcmp($selection,"stats"))
{
	// Retrieve all the data from la table
$resultEvent = mysql_query("SELECT * FROM TableEvenement0 WHERE equipe_event_id = '$equipeID'")
or die(mysql_error());  
$I0=0;
$JoueurSommeEvenement = array();
while($rangeeEv=mysql_fetch_array($resultEvent))
	{
		if($rangeeEv['equipe_event_id']==$equipeID)
			{
				$JoueurSommeEvenement[$I0]['event_id']= $rangeeEv['event_id'];
				$JoueurSommeEvenement[$I0]['joueur_event_ref']= $rangeeEv['joueur_event_ref'];
				$JoueurSommeEvenement[$I0]['code']= $rangeeEv['code'];
				$I0++;
			}
	}
///////////////////////////////////////////////////////////	
//
// 	Construit la liste de joueur, Initialise les stats.

	$rangeeStats;
	$Inom = 0;
	$Ibuts = 0;
	$Ipasses = 0;
	$NbEntre = count($JoueurSommeEvenement,1)/3;
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
		if($joueursEntres[$Itrouve]==$ligneEvent1)
			{$boule=1;}
		$Itrouve++;
		}
		if($boule==0)//joueur pas dans la liste
		{$joueursEntres[$Itrouve]= $ligneEvent1;

///////////////////////////
//////  tests...

		
		$rangeeStats[count($joueursEntres)-1][0]=trouveNomJoueurParID($ligneEvent1);
		$rangeeStats[count($joueursEntres)-1][1]=0;
		$rangeeStats[count($joueursEntres)-1][2]=0;
		}
		
		$Ievent++;
		
	}
	
	///////////////////////////////////////////////////////
	//
	// 	Construit la matrice de stats
	
	
	$NbRangeeStats = count($rangeeStats,1)/3;
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
			{if(!strcmp($rangeeStats[$indexJoueur][0],trouveNomJoueurParID($JoueurSommeEvenement[$Ievent]['joueur_event_ref'])))
        	{$rangeeStats[$indexJoueur][1]++;}
			$indexJoueur++;
			}
        	break;
    	case 1:
			$indexJoueur=0;
			while($indexJoueur<$NbRangeeStats)
			{if(!strcmp($rangeeStats[$indexJoueur][0],trouveNomJoueurParID($JoueurSommeEvenement[$Ievent]['joueur_event_ref'])))
        	{$rangeeStats[$indexJoueur][2]++;}
			$indexJoueur++;
			}
    	    break;
    	case 2:
    	    break;
					}
		$Ievent++;
	}
	
	//////////////////////////////////////////////////
	//
	// 	Affichage des stats
	$Ievent=0;
	while($Ievent<$NbEntre)
		{

	echo "<br />";
	echo $rangeeStats[$Ievent][0];
	echo "....";
	echo $rangeeStats[$Ievent][1];
	echo "....";
	echo $rangeeStats[$Ievent][2];
	
		$Ievent++;
		}  
		
	}
//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////
	
	if(!strcmp($selection,"matchs"))
{
$matchID = "2011/10/13_Hawks_BlackHawks";
	// Retrieve all the data from la table
$resultEvent = mysql_query("SELECT * FROM TableEvenement0 WHERE match_event_id = '{$matchID}' GROUP BY equipe_event_id")
or die(mysql_error());  
$equipe=array();
$Ieq =0;
while($rangeeEv=mysql_fetch_array($resultEvent))
{
	$equipe[$Ieq] = $rangeeEv['equipe_event_id'];
	$Ieq++;
}

$I0=0;
unset($Ieq);
$Sommaire = array();
$Ibuts = 0;
$JSONstring = "{\"matchID\": \"".$matchID."\",";
$leMatch = parseMatchID($matchID);
$JSONstring .="\"date\": \"".$leMatch['date']."\",";
$JSONstring .="\"eqDom\": \"".$leMatch['dom']."\",";
$JSONstring .="\"eqVis\": \"".$leMatch['vis']."\",";
$JSONstring .="\"buts\": [";

foreach($equipe as $Ieq)
{
$resultEvent = mysql_query("SELECT * FROM TableEvenement0 WHERE match_event_id = '{$matchID}' AND equipe_event_id= '{$Ieq}' AND code = '0'")
or die(mysql_error());  	
while($rangeeEv=mysql_fetch_array($resultEvent))
	{
				$Sommair[$Ibuts]['chrono']=$rangeeEv['chrono'];
				$Sommaire[$Ibuts]['marqueur']=trouveNomJoueurParID($rangeeEv['joueur_event_ref']);
				$sql_passeurs = mysql_query("SELECT joueur_event_ref FROM TableEvenement0 WHERE match_event_id = '{$matchID}' AND equipe_event_id= '{$Ieq}' AND chrono = '{$rangeeEv['chrono']}' AND code = '1'")
				or die(mysql_error()); 
				$Ipas = 0;
				while($rangeeEv=mysql_fetch_array($sql_passeurs))
				{
							if($Ipas==0)
								$Sommaire[$Ibuts]['passeur1']=trouveNomJoueurParID($rangeeEv['joueur_event_ref']);				 	
							else
								$Sommaire[$Ibuts]['passeur2']=trouveNomJoueurParID($rangeeEv['joueur_event_ref']);				 	
				$Ipas++;	
				}
				$JSONstring .= json_encode($Sommaire[$Ibuts]).",";
				$Ibuts++;
				
	}
	
$resultEvent = mysql_query("SELECT * FROM TableEvenement0 WHERE match_event_id = '{$matchID}' AND equipe_event_id= '{$Ieq}'")
or die(mysql_error());  	
	
//while($rangeeEv=mysql_fetch_array($resultEvent))
//	{
		

//	}
	
	
}
	$JSONstring = substr($JSONstring, 0,-1);
	$JSONstring .= "]}";
	
//echo json_encode($Sommaire);
echo $JSONstring;
	
}


?>
</body> 
</html> 	