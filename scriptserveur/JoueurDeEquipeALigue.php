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



$rEvent = mysql_query("SELECT * 
						FROM abonJoueurEquipe
						JOIN abonEquipeLigue
							ON(abonJoueurEquipe.equipeId=abonEquipeLigue.equipeId) 
						WHERE 1")
or die(mysql_error());  

 while($Event=mysql_fetch_array($rEvent))
{
$r2 = mysql_query("SELECT * 
						FROM abonJoueurLigue

						WHERE joueurId={$Event['joueurId']}
							AND ligueId={$Event['ligueId']}")
or die(mysql_error());  

if($r2==false)
	{
		$retour = mysql_query("INSERT INTO abonJoueurLigue (joueurId, ligueId, permission, debutAbon,finAbon) 
VALUES ({$Event['joueur_id']}, {$Event['ligueId']}, 30, {$Event['abonJoueurEquipe.joueurId']},'2050-01-01')");	
		echo $retour;
	}
}
//	$rUpElse = mysql_query("UPDATE TableEvenement0 SET chrono = {$Event['tempChrono']} WHERE event_id = {$Event['event_id']}")
//	or die(mysql_error());		
	
	

?>


	
	YO
	</body>

</html>	
