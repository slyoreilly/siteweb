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

$joueurId = $_GET['joueurId'];

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
/*
$resultLigue = mysql_query("SELECT * FROM {$tableLigue}")
or die(mysql_error());  
while($rangeeLigue=mysql_fetch_array($resultLigue))
{
		if(!strcmp($rangeeLigue['Nom_Ligue'],$ligue))
	{$ligueSelect =$rangeeLigue['ID_Ligue'];
	}
		// Prend le ID de la ligue pour trouver les �quipes.
}
*/
/////////////////////////////////////////////////////
	//
//   Trouve ID de l'equipe � partir du nom.
//
////////////////////////////////////////////////////
/*
$resultEquipe = mysql_query("SELECT * FROM {$tableEquipe}")
or die(mysql_error());  
while($rangeeEquipe=mysql_fetch_array($resultEquipe))
{
		if(!strcmp($rangeeEquipe['nom_equipe'],$equipe))
	{$equipeID =$rangeeEquipe['equipe_id'];// Ce sont de INT
	}
}

*///////////////  S�LECTIONNE LA LIGUE  ///////////////////////////////////////

$rJoueur = mysql_query("SELECT ligueId FROM abonJoueurLigue WHERE joueurId = '$joueurId'")
or die(mysql_error());

$maLigue1 = mysql_fetch_array($rJoueur);
$maLigue = $maLigue1['ligueId'];

//////////////  S�LECTIONNE LES MATCHS  ///////////////////////////////////////


$rMatch = mysql_query("SELECT matchIdRef,date FROM TableMatch WHERE ligueRef = $maLigue")
or die(mysql_error());
$saison = array();
$fiche = array();
$Isaison=0;
$uneSai;
$IS4=0;
	$rSaison = mysql_query("SELECT saisonId,premierMatch,dernierMatch FROM TableSaison WHERE ligueRef =$maLigue 
	order by premierMatch")
	or die(mysql_error());
	while($maSaison=mysql_fetch_array($rSaison))
	{
		$saison[$IS4][0] =$maSaison['saisonId'];
		$saison[$IS4][1] =$maSaison['premierMatch'];
		$saison[$IS4][2] =$maSaison['dernierMatch'];
		$IS4++;
	}
	
	$JSONstring = "{\"saisons\": [";

	$IS4=0;

while($IS4<count($saison))
	{
	
			$pm=$saison[$IS4][1];
			$dm=$saison[$IS4][2];

	$JSONstring .= "{\"premierMatch\": \"".$pm."\",";
	$JSONstring .= "\"dernierMatch\": \"".$dm."\",";



		$IS2=0;
		unset($rEquipe);
		$rEquipe = mysql_query("SELECT equipe_event_id FROM TableEvenement0 where joueur_event_ref ='$joueurId' 
		AND chrono>=(UNIX_TIMESTAMP('{$pm}')*1000) AND chrono<=(UNIX_TIMESTAMP('{$dm}')*1000)
				 GROUP BY equipe_event_id")
		or die(mysql_error());
		$JSONstring .= "\"equipe\": [";
		
		while($Equipe=mysql_fetch_array($rEquipe))
		{
			$monEq = $Equipe['equipe_event_id'];
			$JSONstring .= "{\"equipeId\": \"".$monEq."\",";
			unset($resultEquipe);
			$resultEquipe = mysql_query("SELECT nom_equipe FROM {$tableEquipe} where equipe_id=$monEq")
			or die(mysql_error());  
			
			unset($nomEq);
			$nomEq= mysql_fetch_array($resultEquipe);
			$JSONstring .= "\"equipeNom\": \"".$nomEq['nom_equipe']."\",";
		
			$JSONstring .= "\"fiche\": ";
			unset($fiche);
			$fiche = array(0,0,0,0);

//			$fiche[0]=0;
//			$fiche[1]=0;
//			$fiche[2]=0;
//			$fiche[3]=0;
			unset($rEvent);
			
			$rEvent = mysql_query("SELECT * FROM TableEvenement0 WHERE joueur_event_ref ='$joueurId' 
			AND chrono>=(UNIX_TIMESTAMP('$pm')*1000) AND chrono<=(UNIX_TIMESTAMP('$dm')*1000)		
			AND equipe_event_id = {$monEq}")
		
			or die(mysql_error());
		
			while($Event = mysql_fetch_array($rEvent))
				{
					$code = (int) $Event['code'];
				$fiche[$code]++;
				}
			$JSONstring .= "{\"buts\": \"".$fiche[0]."\",";
			$JSONstring .= "\"passes\": \"".$fiche[1]."\",";
			$JSONstring .= "\"minPun\": \"".$fiche[2]."\",";
			$JSONstring .= "\"pj\": \"".$fiche[3]."\"}},";
			$IS2++;	
			}
		if($IS2!=0)
			$JSONstring = substr($JSONstring, 0,-1);
		$JSONstring .= "]},";
		$Isaison++;	
		$IS4++;
		}	
	
		if($Isaison!=0)
			$JSONstring = substr($JSONstring, 0,-1);
		$JSONstring .= "]}";
echo $JSONstring;

		


?>
