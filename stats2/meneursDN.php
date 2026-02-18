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

$selection =null;
$ligue = null;
$equipe = null;
$matchId = null;

if(isset($_POST['saisonId'])){
	$saisonId = $_POST['saisonId'];
	}
	if(isset($_POST['ligueId'])){
		$ligueId = $_POST['ligueId'];
	}
	if(isset($_POST['equipeId'])){
			$equipeId = $_POST['equipeId'];
	}if(isset($_POST['matchId'])){
				$matchId = $_POST['matchId'];
			}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
// 
//
function trouveSaisonActiveDeLigueId($ID){ 
$rfSaison = mysql_query("SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ID}' ORDER BY premierMatch DESC")
or die(mysql_error()." Select saisonId");  
//echo mysql_result($rfSaison, 0)."\n";  
//$tmp= (mysql_fetch_array($rfSaison));
//echo $tmp['saisonId']."\n";  
 return (mysql_result($rfSaison, 0)); 
} 




$getLigue = $_GET["ligueId"];
$saisonId = $_GET["saisonId"];

if($saisonId=="null"||$saisonId=="undefined"||$saisonId=="")// Sp�cifie par la saison
	{
		$rfSaison = mysqli_query($conn,"SELECT saisonId FROM TableSaison WHERE ligueRef = '{$getLigue}' ORDER BY premierMatch DESC LIMIT 0,1")
or die(mysqli_error($conn)." Select saisonId"); 

while($rangeeSaison=mysqli_fetch_array($rfSaison))
{
	$saisonId= $rangeeSaison['saisonId'];
	
}
		
		}


	$prSaison = mysqli_query($conn,"SELECT premierMatch FROM TableSaison where saisonId =$saisonId LIMIT 0,1")
or die(mysqli_error($conn)."qPM sID: ".$saisonId);  

while($rangeePM=mysqli_fetch_array($prSaison))
{
	$premierMatch= $rangeePM['premierMatch'];
	
}
//$premierMatch=mysql_result($prSaison, 0);
	$drSaison = mysqli_query($conn,"SELECT dernierMatch FROM TableSaison where saisonId =$saisonId LIMIT 0,1")
or die(mysqli_error($conn)."qDM");  
while($rangeeDM=mysqli_fetch_array($drSaison))
{
	$dernierMatch= $rangeeDM['dernierMatch'];
	
}

//  
$lesMatchs = array();
$I2=0;
$resultMatch = mysqli_query($conn,"SELECT * FROM TableMatch WHERE ligueRef=$getLigue")
or die(mysqli_error($conn));  

while($rangeeMatch=mysqli_fetch_array($resultMatch))
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

$resultEvent = mysqli_query($conn,"SELECT TableEvenement0.*, TableEquipe.nom_equipe,TableEquipe.ficId, TableEquipe.ligue_equipe_ref, TableJoueur.NomJoueur, TableJoueur.NumeroJoueur,TableJoueur.Ligue   
				FROM TableJoueur 
					JOIN TableEvenement0
						 ON (TableJoueur.joueur_id=TableEvenement0.joueur_event_ref) 
					LEFT JOIN TableEquipe
						 ON (TableJoueur.equipe_id_ref=TableEquipe.equipe_id) 
						 
					WHERE  match_event_id = '{$lesMatchs[$Im]}'")
or die(mysqli_error($conn));  

while($rangeeEv=mysqli_fetch_array($resultEvent))
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
			{if(!strcmp($rangeeStats[$indexJoueur][0],$JoueurSommeEvenement[$Ievent]['nom'])&&(floor($JoueurSommeEvenement[$Ievent]['souscode']/10)==4))
        	{$rangeeStats[$indexJoueur][1]++;}
			$indexJoueur++;
			}
        	break;
    	case 1:
			$indexJoueur=0;
			while($indexJoueur<$NbRangeeStats)
			{if(!strcmp($rangeeStats[$indexJoueur][0],$JoueurSommeEvenement[$Ievent]['nom'])&&(floor($JoueurSommeEvenement[$Ievent]['souscode']/10)==4))
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
				if($Ievent!=0)
			$JSONstring = substr($JSONstring, 0,-1);
	$JSONstring .= "]}";
	
echo $JSONstring;
		
//mysqli_close($conn);

?>
