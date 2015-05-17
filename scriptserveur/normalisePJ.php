<HTML> 
<HEAD> 
	<title>Statistiques du joueur</title>
<link rel="stylesheet" href="style/general.css" type="text/css">
<script src="/scripts/fonctions.js" type="text/javascript"></script>

</HEAD>
<body>


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

$resultEquipe = mysql_query("SELECT * FROM {$tableEquipe}")
or die(mysql_error());  
while($rangeeEquipe=mysql_fetch_array($resultEquipe))
{
		if(!strcmp($rangeeEquipe['nom_equipe'],$equipe))
	{$equipeID =$rangeeEquipe['equipe_id'];// Ce sont de INT
	}
}


$rEvent = mysql_query("SELECT * FROM TableJoueur where equipe_id_ref=0 and Ligue=12")
or die(mysql_error());  
 while($Event=mysql_fetch_array($rEvent))
{
	
	$monMatch = parseMatchID($Event['match_event_id']);
	$uneVal=strtotime($monMatch['date']." ".$Event['chrono']);
	$intDate=strtotime($monMatch['date']);
	$monChrono = $Event['chrono']-10000;
	$intChrono=intval($Event['chrono']);
		$retour = mysql_query("INSERT INTO abonJoueurLigue (joueurId, ligueId, permission, debutAbon,finAbon) 
VALUES ('{$Event['joueur_id']}', 12, 30, '2012-09-02','2050-01-01')");	
		
	
	echo $uneVal."</br>";
}
if(!empty($uneVal))
{
//				$rUpIf = mysql_query("UPDATE TableEvenement0 SET tempChrono = $Event['chrono'] WHERE event_id = {$Event['event_id']}")
//				or die(mysql_error());
}
//	$rUpElse = mysql_query("UPDATE TableEvenement0 SET chrono = {$Event['tempChrono']} WHERE event_id = {$Event['event_id']}")
//	or die(mysql_error());		
	
	

?>


	
	YO
	</body>

</html>	
