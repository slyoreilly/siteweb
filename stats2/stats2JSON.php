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

$selection = $_POST['selection'];
$ligue = $_POST['ligue'];
$equipe = $_POST['equipe'];
$matchId = $_POST['matchId'];

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
or die(mysql_error()."SELECT * FROM TableJoueur WHERE joueur_id = '{$ID}'");  
while($rangeeJoueur=mysql_fetch_array($resultJoueur))
	{if(strcmp($rangeeJoueur['NomJoueur'],"null"))
		  {return ($rangeeJoueur['NomJoueur']);}
	else { return ("Anonyme"); }}		   
 return ("Anonyme"); 
} 




/////////////////////////////////////////////////////
//
//   Trouve ID de la ligue � partir du nom.
//
////////////////////////////////////////////////////

$resultLigue = mysql_query("SELECT * FROM {$tableLigue}")
or die(mysql_error());  
while($rangeeLigue=mysql_fetch_array($resultLigue))
{
		if(!strcmp($rangeeLigue['Nom_Ligue'],$ligue))
	{$ligueSelect =$rangeeLigue['ID_Ligue'];
	}
		// Prend le ID de la ligue pour trouver les �quipes.
}

/////////////////////////////////////////////////////
	//
//   Trouve ID de l'equipe � partir du nom.
//
////////////////////////////////////////////////////


if($equipe=='H')
{$rMatchEquipe= mysql_query("SELECT eq_dom FROM TableMatch where matchIdRef = '$matchId'")
or die(mysql_error()."SELECT eq_dom FROM TableMatch where matchIdRef = '$matchId'");  
$rangEq=mysql_fetch_row($rMatchEquipe);
$equipeID=$rangEq[0];
}
else if($equipe=='V')
{$rMatchEquipe= mysql_query("SELECT eq_vis FROM TableMatch where matchIdRef = '$matchId'")
or die(mysql_error()."SELECT eq_vis FROM TableMatch where matchIdRef = '$matchId'");  
$rangEq=mysql_fetch_row($rMatchEquipe);
$equipeID=$rangEq[0];}
else {
$resultEquipe = mysql_query("SELECT * FROM {$tableEquipe}")
or die(mysql_error());  
while($rangeeEquipe=mysql_fetch_array($resultEquipe))
{
		if(!strcmp($rangeeEquipe['nom_equipe'],$equipe))
	{$equipeID =$rangeeEquipe['equipe_id'];// Ce sont de INT
	}
}
	
}	



	// Retrieve all the data from la table
$resultEvent = mysql_query("SELECT * FROM TableEvenement0 WHERE equipe_event_id = '$equipeID' AND code <9 AND match_event_id = '$matchId'")
or die(mysql_error()."SELECT * FROM TableEvenement0 WHERE equipe_event_id = '$equipeID' AND code <9 AND match_event_id = '$matchId'");  
$I0=0;
$JoueurSommeEvenement = array();
while($rangeeEv=mysql_fetch_array($resultEvent))
	{
		if($rangeeEv['equipe_event_id']==$equipeID)
			{
				$JoueurSommeEvenement[$I0]['event_id']= $rangeeEv['event_id'];
				$JoueurSommeEvenement[$I0]['joueur_event_ref']= $rangeeEv['joueur_event_ref'];
				$JoueurSommeEvenement[$I0]['code']= $rangeeEv['code'];
				$JoueurSommeEvenement[$I0]['souscode']= $rangeeEv['souscode'];
				$I0++;
			}
	}
///////////////////////////////////////////////////////////	
//
// 	Construit la liste de joueur, Initialise les stats.

	$rangeeStats=array();
	$Inom = 0;
	$Ibuts = 0;
	$Ipasses = 0;
//	$NbEntre = count($JoueurSommeEvenement,1)/3;
//$NbEntre=max(array_map('count', $JoueurSommeEvenement));
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

$resultJoueur = mysql_query("SELECT * FROM TableJoueur WHERE joueur_id = '{$ligneEvent1}'")
or die(mysql_error()."SELECT * FROM TableJoueur WHERE joueur_id = '{$ligneEvent1}'");  
$rangeeJoueur=mysql_fetch_assoc($resultJoueur);
//while($rangeeJoueur=mysql_fetch_array($resultJoueur))
//	{if(strcmp($rangeeJoueur['NomJoueur'],"null"))
//		  {return ($rangeeJoueur['NomJoueur']);}
//	else { return ("Anonyme"); }}		   
 //return ("Anonyme"); 
//} 

		
		$rangeeStats[$Itrouve][0]=$rangeeJoueur['NomJoueur'];
		$rangeeStats[$Itrouve][1]=0;
		$rangeeStats[$Itrouve][2]=0;
		$rangeeStats[$Itrouve][3]=$ligneEvent1;
		$rangeeStats[$Itrouve][4]=$rangeeJoueur['NumeroJoueur'];
		$rangeeStats[$Itrouve][5]=0;
		}
		
		$Ievent++;
		
	}
	
	///////////////////////////////////////////////////////
	//
	// 	Construit la matrice de stats
	
//	$NbRangeeStats=max(array_map('count',$rangeeStats));
		
	$NbRangeeStats=count($rangeeStats);
//	$NbRangeeStats = count($rangeeStats,1)/3;
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
			{if($rangeeStats[$indexJoueur][3]==$JoueurSommeEvenement[$Ievent]['joueur_event_ref'])
        	{$rangeeStats[$indexJoueur][1]++;}
			$indexJoueur++;
			}
        	break;
    	case 1:
			$indexJoueur=0;
			while($indexJoueur<$NbRangeeStats)
			{if($rangeeStats[$indexJoueur][3]==$JoueurSommeEvenement[$Ievent]['joueur_event_ref'])
        	{$rangeeStats[$indexJoueur][2]++;}
			$indexJoueur++;
			}
    	    break;
    	case 2:
    	    break;
		case 3:
			$indexJoueur=0;
			while($indexJoueur<$NbRangeeStats)
			{if($rangeeStats[$indexJoueur][3]==$JoueurSommeEvenement[$Ievent]['joueur_event_ref']&&$JoueurSommeEvenement[$Ievent]['souscode']==5)
        	{$rangeeStats[$indexJoueur][5]=5;} 
        	else if($rangeeStats[$indexJoueur][3]==$JoueurSommeEvenement[$Ievent]['joueur_event_ref']&&$JoueurSommeEvenement[$Ievent]['souscode']==0){
        		$rangeeStats[$indexJoueur][5]=1;
        	}
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
$joueurs=array();
$joueurs['gardiens']=array();
$joueurs['joueurs']=$stats;
	$JSONstring = "{\"joueurs\": [";

	while($Ievent<$NbRangeeStats)
		{
$stats[$Ievent]['nom'] = $rangeeStats[$Ievent][0];
$stats[$Ievent]['nbButs'] = $rangeeStats[$Ievent][1];
$stats[$Ievent]['nbPasses'] = $rangeeStats[$Ievent][2];
$stats[$Ievent]['joueurId'] = $rangeeStats[$Ievent][3];
$stats[$Ievent]['numero'] = $rangeeStats[$Ievent][4];
switch($rangeeStats[$Ievent][5]){
	case 0:
		$stats[$Ievent]['pj'] = 0;
		array_push($joueurs['joueurs'],$stats[$Ievent]);
		break;
	case 1:
		$stats[$Ievent]['pj'] = 1;
		array_push($joueurs['joueurs'],$stats[$Ievent]);
		break;
	case 5:
		$stats[$Ievent]['pj'] = 1;
		array_push($joueurs['gardiens'],$stats[$Ievent]);
		break;
}

		$Ievent++;
		}  
		//$joueurs['joueurs']=$stats;

	
	
echo json_encode($joueurs);
//echo $JSONstring;
		


?>
