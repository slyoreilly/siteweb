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

$saisonId =null ;
$ligueId = null;
$equipeId = null;
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
$stringOut=" ";


////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, "utf8");

/////////////////////////////////////////////////////////////
// 
//
/////////////////////////////////////////////////////////////
//
//

	
if($saisonId=="null"||$saisonId=="undefined"||$saisonId=="")// Sp�cifie par la saison
	{
		$rfSaison = mysqli_query($conn,"SELECT saisonId FROM TableSaison WHERE ligueRef = '{$ligueId}' ORDER BY premierMatch DESC LIMIT 0,1")
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

//$dernierMatch=mysql_result($drSaison, 0);

$butsAccorde = array();
$Im=0;


	mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
$stringOut .="GrosBug";
$reqMatch= "SELECT TableMatch.*, TableEvenement0.joueur_event_ref, TableEvenement0.equipe_event_id , TableJoueur.NomJoueur, TableJoueur.NumeroJoueur, TEdom.nom_equipe AS NEdom,TEvis.nom_equipe AS NEvis, TEdom.equipe_id AS eqDomId,TEvis.equipe_id AS eqVisId
				FROM TableMatch
				JOIN TableEvenement0 
					ON (TableMatch.matchIdRef=TableEvenement0.match_event_id) 
				JOIN TableEquipe AS TEdom 
										ON (TableMatch.eq_dom=TEdom.equipe_id)
									JOIN TableEquipe AS TEvis
										ON (TableMatch.eq_vis=TEvis.equipe_id)
				JOIN TableJoueur 
					on  (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id)
					where TableMatch.ligueRef ='{$ligueId}' and TableMatch.date>='{$premierMatch}'  
					and TableMatch.date<='{$dernierMatch}' and TableEvenement0.code='3' 
					and TableEvenement0.souscode=5";
$rMatchs = mysqli_query($conn,$reqMatch)
or die(mysqli_error($conn)."qM"); 
//echo $reqMatch." - ";
while($lMatchs=mysqli_fetch_array($rMatchs))  /// On regarde tous les matchs de la saison.
	{
		
				 	if(strcmp($lMatchs['equipe_event_id'],$lMatchs['eq_vis'])==0&&((!empty($equipeId)&&($equipeId==$lMatchs['eq_vis']))||(strcmp($equipeId,"null")==0)||$equipeId==null))
					{// S'il y a une �quipe de d�finie, le gardien fait-il partie de cette �quipe?
					//$butsAccorde[$Im][0]=$lMatchs['score_dom'];   // Buts accordés // obsolet, réécrit.
					$rEvent_dom = mysqli_query($conn,"SELECT TableEvenement0.souscode
												FROM TableEvenement0 
												WHERE match_event_id='{$lMatchs['matchIdRef']}' 
												AND equipe_event_id = '{$lMatchs['eq_dom']}'
												AND code = 0 
												AND souscode!=9")
					or die(mysqli_error($conn)."qVis");
					$butsAccorde[$Im][0]=mysqli_num_rows($rEvent_dom);   // Buts accordés
					$butsAccorde[$Im][1]=$lMatchs['eq_vis'];		// Équipe du gardien
					$butsAccorde[$Im][2]=$lMatchs['matchIdRef'];
					$butsAccorde[$Im][3]=$lMatchs['NomJoueur'];
					$butsAccorde[$Im][5]=$lMatchs['NumeroJoueur'];
					$butsAccorde[$Im][6]=$lMatchs['joueur_event_ref'];  // ID
				//	echo " - ".$lMatchs['joueur_event_ref']." / ".$lMatchs['equipe_event_id']." / ".$lMatchs['eq_vis'];
		
					$butsAccorde[$Im][7]=$lMatchs['NEvis'];  // Nom de l'équipe du gardien
						if($lMatchs['score_dom']>$lMatchs['score_vis'])
							{$butsAccorde[$Im][4]=0;}  // D�faite du gardien
						else 
							{
							if($lMatchs['score_dom']<$lMatchs['score_vis'])
							{$butsAccorde[$Im][4]=2;}// Victoire du gardien
						else
							{$butsAccorde[$Im][4]=1;}// Nulle du gardien
							}
						
					}
				 	if(strcmp($lMatchs['equipe_event_id'],$lMatchs['eq_dom'])==0&&((!empty($equipeId)&&($equipeId==$lMatchs['eq_dom']))||(strcmp($equipeId,"null")==0)||$equipeId==null))
					{
					//$butsAccorde[$Im][0]=$lMatchs['score_vis'];
					$rEvent_vis = mysqli_query($conn,"SELECT TableEvenement0.souscode
												FROM TableEvenement0 
												WHERE match_event_id='{$lMatchs['matchIdRef']}' 
												AND equipe_event_id = '{$lMatchs['eq_vis']}'
												AND code = 0 
												AND souscode!=9")
					or die(mysqli_error($conn)."qDom");
					$butsAccorde[$Im][0]=mysqli_num_rows($rEvent_vis);   // Buts accordés
										$butsAccorde[$Im][1]=$lMatchs['eq_dom'];
					$butsAccorde[$Im][2]=$lMatchs['matchIdRef'];
					$butsAccorde[$Im][3]=$lMatchs['NomJoueur'];
					$butsAccorde[$Im][5]=$lMatchs['NumeroJoueur'];
					$butsAccorde[$Im][6]=$lMatchs['joueur_event_ref'];  // ID
					$butsAccorde[$Im][7]=$lMatchs['NEdom'];  // Nom de l'équipe du gardien
						if($lMatchs['score_dom']>$lMatchs['score_vis'])
						{
							$butsAccorde[$Im][4]=2;// Victoire du gardien
						}
						else 
						{if($lMatchs['score_dom']<$lMatchs['score_vis'])
							{$butsAccorde[$Im][4]=0;}   // D�faite du gardien
						else
							{$butsAccorde[$Im][4]=1;}  // Nulle du gardien
						}
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
//	$NbEntre = count($JoueurSommeEvenement,1)/3;
//$NbEntre=max(array_map('count', $JoueurSommeEvenement));
$NbEntre=$Im;//count($butsAccorde);
	unset($joueursEntres);
	$joueursEntres = array(); 
	
	$Ievent = 0;
	
	while($Ievent<$NbEntre)
	{
		
		
		$ligneEvent1 = $butsAccorde[$Ievent][6];
		//echo $Ievent."  ".$butsAccorde[$Ievent][6]." ";
		$Itrouve=0;
		$boule =0;
		while($Itrouve<count($joueursEntres))
		{
		if(!strcmp($joueursEntres[$Itrouve],$ligneEvent1))  // Si trouve le gardien dans la liste
			{$boule=1;}
		$Itrouve++;
		}

		if(($boule==0)/*&&(strcmp($ligneEvent1,null))*/&&(($butsAccorde[$Ievent][1]==$equipeId)||(strcmp($equipeId,"null")==0)||$equipeId==null))//gardien pas dans la liste et dans l'équipe
		{$joueursEntres[$Itrouve]= $ligneEvent1;
		$stringOut.= "----  Liste est rendue a ".$Itrouve;
		$rangeeStats[$Itrouve][0]=$butsAccorde[$Ievent][3];
		$stringOut.= $ligneEvent1." ";
		$rangeeStats[$Itrouve][1]=0;//V
		$rangeeStats[$Itrouve][2]=0;//D
		$rangeeStats[$Itrouve][3]=0;//N
		$rangeeStats[$Itrouve][4]=0;//ButsAccord�s
		$rangeeStats[$Itrouve][5]=$butsAccorde[$Ievent][5];//No joueur
		$rangeeStats[$Itrouve][6]=$butsAccorde[$Ievent][1];//Equipe
		$rangeeStats[$Itrouve][7]=$butsAccorde[$Ievent][6];//ID
		$rangeeStats[$Itrouve][8]=$butsAccorde[$Ievent][7];//Equipe		
		}
			
		$Ievent++;
		
	}
	//echo $stringOut;
	
	///////////////////////////////////////////////////////
	//
	// 	Construit la matrice de stats
	
//	$NbRangeeStats=max(array_map('count',$rangeeStats));
		
	$NbRangeeStats=count($rangeeStats);
	$Ievent=0;
	while($Ievent<$NbEntre)  //$NbEntre=count($butsAccorde); Tous les �v�nements goaler
	{
			$indexJoueur=0;
			while($indexJoueur<$NbRangeeStats)
				{
	
				if(!strcmp($rangeeStats[$indexJoueur][7],$butsAccorde[$Ievent][6]))  //  Si on trouve un evenement compatible avec un joueur
        			{$rangeeStats[$indexJoueur][4]+=$butsAccorde[$Ievent][0];
//					$rangeeStats[$indexJoueur][0]=$butsAccorde[$Ievent][3];
					switch ($butsAccorde[$Ievent][4]) 
						{
				    	case 0:
							$rangeeStats[$indexJoueur][2]++;
							break;
    					case 1:
							$rangeeStats[$indexJoueur][3]++;
							break;
    					case 2:
							$rangeeStats[$indexJoueur][1]++;
    					    break;
						}
					}
			$indexJoueur++;
				}
		$Ievent++;
	}
	
	//////////////////////////////////////////////////
	//
	// 	Affichage des stats
	$Ievent=0;
$stats=array();
	$JSONstring = "{\"gardiens\": [";

	while($Ievent<$NbRangeeStats)
		{
			
$stats[$Ievent]['nom'] = $rangeeStats[$Ievent][0];
$stats[$Ievent]['victoires'] = $rangeeStats[$Ievent][1];
$stats[$Ievent]['defaites'] = $rangeeStats[$Ievent][2];
$stats[$Ievent]['nulles'] = $rangeeStats[$Ievent][3];
$stats[$Ievent]['nbButs'] = $rangeeStats[$Ievent][4];
$stats[$Ievent]['no'] = $rangeeStats[$Ievent][5];
$stats[$Ievent]['equipe'] = $rangeeStats[$Ievent][6];
$stats[$Ievent]['id'] = $rangeeStats[$Ievent][7];
$stats[$Ievent]['nomEquipe'] = $rangeeStats[$Ievent][8];
				$JSONstring .= json_encode($stats[$Ievent]).",";

		$Ievent++;
		}  
		if($Ievent!=0)
			$JSONstring = substr($JSONstring, 0,-1);
	$JSONstring .= "]}";
	
echo $JSONstring;
mysqli_close($conn);

?>
