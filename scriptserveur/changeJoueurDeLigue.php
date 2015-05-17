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
}*/

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
}*/
$q1="CREATE TEMPORARY TABLE tmp SELECT * FROM abonJoueurLigue WHERE ligueId=12";
mysql_query($q1)or die(mysql_error());  
$q2 ="UPDATE tmp SET ligueId=27 WHERE ligueId=12";
mysql_query($q2)or die(mysql_error());  
$q3="SELECT * FROM tmp WHERE ligueId=27";
$rligue=mysql_query($q3)or die(mysql_error());  



 while($lLigue=mysql_fetch_array($rligue))
{
	mysql_query("INSERT INTO abonJoueurLigue (joueurId, ligueId, permission, debutAbon,finAbon) VALUES ('{$lLigue['joueurId']}','{$lLigue['ligueId']}','{$lLigue['permission']}','{$lLigue['debutAbon']}','{$lLigue['finAbon']}') ");
}
//	$rUpElse = mysql_query("UPDATE TableEvenement0 SET chrono = {$Event['tempChrono']} WHERE event_id = {$Event['event_id']}")
//	or die(mysql_error());		
	
	

?>


	</body>

</html>	
