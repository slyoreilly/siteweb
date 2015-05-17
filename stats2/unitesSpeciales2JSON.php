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
$tableMatch = 'TableMatch';

$saisonId = $_POST['saisonId'];
$ligueId = $_POST['ligueId'];
$equipeId = $_POST['equipeId'];
$matchId = $_POST['matchId'];

$stringOut=" ";


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


function trouveNoJoueurParID($ID){ 
unset($resultJoueur);
$resultJoueur = mysql_query("SELECT * FROM TableJoueur WHERE joueur_id = '{$ID}'")
or die(mysql_error());  
while($rangeeJoueur=mysql_fetch_array($resultJoueur))
	{if(strcmp($rangeeJoueur['NumeroJoueur'],"null"))
		  {return ($rangeeJoueur['NumeroJoueur']);}
	else { return (0); }}		   
 return (0); 
} 


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



/////////////////////////////////////////////////////////////////////////////////////
//
//	D�but du corps
//
////////////////////////////////////////////////////////////////////////////////////
//echo "saisonId: ".$saisonId."\n"."Is null? ".($saisonId=="null");

if($saisonId=="null")// Sp�cifie par la saison
	{$saisonId = trouveSaisonActiveDeLigueId($ligueId);}

//$rSaison = mysql_query("SELECT * FROM {$tableSaison} where saisonId ={$saisonId}")
//or die(mysql_error());  
//$lSaison=mysql_result($rSaison, 0);



//if(!strcmp($saisonId,"null")&&strcmp($ligueId,"null"))// Sp�cifie par la ligue
//{
//	$saisonId =2;
	$prSaison = mysql_query("SELECT premierMatch FROM TableSaison where saisonId ='{$saisonId}'")
or die(mysql_error()."SELECT premierMatch ");  
$premierMatch=mysql_result($prSaison, 0);
	$drSaison = mysql_query("SELECT dernierMatch FROM TableSaison where saisonId ='{$saisonId}'")
or die(mysql_error()."SELECT premierMatch ");  
$dernierMatch=mysql_result($drSaison, 0);
//}

$statsUniSpec = array();
$Im=0;
//echo "saisonId: ".$saisonId.", premierMatch: ".$premierMatch.", dernierMatch: ".$dernierMatch;

//  S�lectionne les matchs de la saison

//$resultEvent = mysql_query("SELECT TableEvenement0.*, TableJoueur.NomJoueur, TableJoueur.NumeroJoueur  FROM TableEvenement0 JOIN TableJoueur ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id) WHERE equipe_event_id = '{$getEquipe}' AND match_event_id = '{$lesMatchs[$Im]}'")
//or die(mysql_error());  
//$rMatchs = mysql_query("SELECT * FROM {$tableMatch} where ligueRef ='{$ligueId}' and date>='{$premierMatch}'  and date<='{$dernierMatch}'")
//or die(mysql_error()); 
$qSelEq = "SELECT TableSaison.*, abonEquipeLigue.*
						FROM abonEquipeLigue  
							JOIN TableSaison
								ON (TableSaison.ligueRef=abonEquipeLigue.ligueId) 
						WHERE TableSaison.ligueRef ='{$ligueId}' 
							AND abonEquipeLigue.finAbon>='{$premierMatch}'  
							AND abonEquipeLigue.debutAbon<='{$dernierMatch}' 
							GROUP BY abonEquipeLigue.equipeId";
$qOld ="SELECT TableMatch.*, TableEvenement0.equipe_event_id 
						FROM TableEvenement0  
							JOIN {$tableMatch}
								ON (TableMatch.matchIdRef=TableEvenement0.match_event_id) 
						WHERE TableMatch.ligueRef ='{$ligueId}' 
							AND TableMatch.date>='{$premierMatch}'  
							AND TableMatch.date<='{$dernierMatch}' 
						GROUP BY TableEvenement0.equipe_event_id";

$rEquipe = mysql_query($qSelEq)
or die(mysql_error()." Select TableMatch no1"); 
$Id=0;
while($lEquipes=mysql_fetch_array($rEquipe))  /// On regarde tous les matchs de la saison.
{
$statsUniSpec[$Id]['equipeId']=$lEquipes['equipeId'];
$statsUniSpec[$Id]['bpdn']=0;
$statsUniSpec[$Id]['bpan']=0;
$statsUniSpec[$Id]['bcdn']=0;
$statsUniSpec[$Id]['bcan']=0;
$statsUniSpec[$Id]['pun']=0;
$statsUniSpec[$Id]['occ']=0;

$rMatchs = mysql_query("SELECT TableMatch.*, TableEvenement0.equipe_event_id ,TableEvenement0.code, TableEvenement0.souscode
						FROM {$tableMatch} 
							JOIN TableEvenement0 
								ON (TableMatch.matchIdRef=TableEvenement0.match_event_id) 
						WHERE (TableMatch.eq_dom ='{$statsUniSpec[$Id]['equipeId']}' OR TableMatch.eq_vis ='{$statsUniSpec[$Id]['equipeId']}')
							AND TableMatch.date>='{$premierMatch}' AND TableMatch.date<='{$dernierMatch}' 
							AND (TableEvenement0.code='4' OR (TableEvenement0.code=0 AND TableEvenement0.souscode>=40 AND TableEvenement0.souscode<60))")
or die(mysql_error()." Select TableMatch no2"); 
while($lMatchs=mysql_fetch_array($rMatchs))  /// On regarde tous les matchs de la saison.
	{
			 
				 	if($lMatchs['equipe_event_id']==$statsUniSpec[$Id]['equipeId'])
					{// S'il y a une �quipe de d�finie, le gardien fait-il partie de cette �quipe?
					if($lMatchs['code']==0)
					{
						if($lMatchs['souscode']>=40&&$lMatchs['souscode']<50)
						{$statsUniSpec[$Id]['bpdn']++;}
						if($lMatchs['souscode']>=50&&$lMatchs['souscode']<60)
						{$statsUniSpec[$Id]['bpan']++;}
						}
					if($lMatchs['code']==4)
					{$statsUniSpec[$Id]['pun']++;							
						}
										
					}
				else
					{
					if($lMatchs['code']==0)
					{
						
						
						if($lMatchs['souscode']>=40&&$lMatchs['souscode']<50)
						{$statsUniSpec[$Id]['bcan']++;}
						if($lMatchs['souscode']>=50&&$lMatchs['souscode']<60)
						{$statsUniSpec[$Id]['bcdn']++;}
					}
					if($lMatchs['code']==4)
					{
						$statsUniSpec[$Id]['occ']++;}							
					
					}					


					
	}
$Id++;
}

echo json_encode($statsUniSpec);


?>
