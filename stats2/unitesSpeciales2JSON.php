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
$tableMatch = 'TableMatch';

$saisonId = $_POST['saisonId'];
$ligueId = $_POST['ligueId'];

$stringOut=" ";



/////////////////////////////////////////////////////////////
// 
//

function trouveSaisonActiveDeLigueId($ID,$conn2){ 
$rfSaison = mysqli_query($conn2,"SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ID}' ORDER BY premierMatch DESC")
or die(mysqli_error($conn2)." Select saisonId");  
//echo mysql_result($rfSaison, 0)."\n";  
//$tmp= (mysql_fetch_array($rfSaison));
//echo $tmp['saisonId']."\n";  
 return (mysqli_data_seek($rfSaison, 0)); 
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
	$prSaison = mysqli_query($conn,"SELECT premierMatch,dernierMatch  FROM TableSaison where saisonId ='{$saisonId}'")
or die(mysqli_error($conn)."SELECT premierMatch ");  
mysqli_data_seek($prSaison, 0);
$rSaison=mysqli_fetch_row($prSaison);
$premierMatch=$rSaison[0];
$dernierMatch=$rSaison[1];
//}

$statsUniSpec = array();
$Im=0;
//echo "saisonId: ".$saisonId.", premierMatch: ".$premierMatch.", dernierMatch: ".$dernierMatch;

//  S�lectionne les matchs de la saison

//$resultEvent = mysql_query("SELECT TableEvenement0.*, TableJoueur.NomJoueur, TableJoueur.NumeroJoueur  FROM TableEvenement0 JOIN TableJoueur ON (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id) WHERE equipe_event_id = '{$getEquipe}' AND match_event_id = '{$lesMatchs[$Im]}'")
//or die(mysql_error());  
//$rMatchs = mysql_query("SELECT * FROM {$tableMatch} where ligueRef ='{$ligueId}' and date>='{$premierMatch}'  and date<='{$dernierMatch}'")
//or die(mysql_error()); 
$qSelEq = "SELECT abonEquipeLigue.equipeId
						FROM abonEquipeLigue  
						WHERE abonEquipeLigue.ligueId ='{$ligueId}' 
							AND abonEquipeLigue.finAbon>='{$premierMatch}'  
							AND abonEquipeLigue.debutAbon<='{$dernierMatch}' 
							GROUP BY abonEquipeLigue.equipeId";


$rEquipe = mysqli_query($conn,$qSelEq)
or die(mysqli_error($conn)." Select TableMatch no1"); 
$Id=0;
while($lEquipes=mysqli_fetch_array($rEquipe))  /// On regarde tous les matchs de la saison.
{
$statsUniSpec[$Id]['equipeId']=$lEquipes['equipeId'];
$statsUniSpec[$Id]['bpdn']=0;
$statsUniSpec[$Id]['bpan']=0;
$statsUniSpec[$Id]['bcdn']=0;
$statsUniSpec[$Id]['bcan']=0;
$statsUniSpec[$Id]['pun']=0;
$statsUniSpec[$Id]['occ']=0;

$rMatchs = mysqli_query($conn,"SELECT TableMatch.*, TableEvenement0.equipe_event_id ,TableEvenement0.code, TableEvenement0.souscode
						FROM {$tableMatch} 
							JOIN TableEvenement0 
								ON (TableMatch.matchIdRef=TableEvenement0.match_event_id) 
						WHERE (TableMatch.eq_dom ='{$statsUniSpec[$Id]['equipeId']}' OR TableMatch.eq_vis ='{$statsUniSpec[$Id]['equipeId']}')
							AND TableMatch.date>='{$premierMatch}' AND TableMatch.date<='{$dernierMatch}' 
							AND (TableEvenement0.code='4' OR (TableEvenement0.code=0 AND TableEvenement0.souscode>=40 AND TableEvenement0.souscode<60))")
or die(mysqli_error($conn)." Select TableMatch no2"); 
while($lMatchs=mysqli_fetch_array($rMatchs))  /// On regarde tous les matchs de la saison.
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
