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


$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, "utf8");


///////////////////////////////////
//
//   NOUVEAU!!!!!

function getLigueIndex($ligues,$ligueId){
	$i=0;
foreach($ligues as $uneligue){
	 if($uneline['ID_Ligue']==$ligueId){
		 return $i;
	 }
	 $i++;
	}

	return -1;
}
function getSaisonIndex($saisons,$saisonId){
	$i=0;
foreach($saisons as $unesaison){
	 if($unesaison['saisonId']==$saisonId){
		 return $i;
	 }
	 $i++;
	}

	return -1;
}
function getEquipeIndex($equipes,$equipeId){
	$i=0;
foreach($equipes as $uneequipe){
	 if($uneequipe['equipe_id']==$equipeId){
		 return $i;
	 }
	 $i++;
	}

	return -1;
}

$result = mysqli_query($conn, "
SELECT Ligue.ID_Ligue, Ligue.Nom_Ligue, saisonId,premierMatch,dernierMatch nom, SUM(e.PJ) as PJ, TableEquipe.nom_equipe, TableEquipe.equipe_id, SUM(b.B) as Buts,  SUM(Pun.Pun) as Pun,  SUM(P.P) as Passes FROM TableSaison as s1
JOIN Ligue 
 on Ligue.ID_Ligue=s1.ligueRef
 JOIN abonJoueurLigue 
 on abonJoueurLigue.ligueId=s1.ligueRef
 LEFT JOIN abonEquipeLigue
 on abonEquipeLigue.ligueId=Ligue.ID_Ligue
 JOIN TableEquipe 
 ON abonEquipeLigue.equipeId=TableEquipe.equipe_id
 JOIN TableMatch as t1
on (t1.date BETWEEN s1.premierMatch AND s1.dernierMatch AND t1.ligueRef=s1.ligueRef  AND  (t1.eq_dom=TableEquipe.equipe_id OR t1.eq_vis=TableEquipe.equipe_id ))

Join (
		SELECT count(*) as PJ, TableEvenement0.match_event_id, Min(TableEvenement0.equipe_event_id) as eq
		FROM TableEvenement0 
		where TableEvenement0.code = 3 and TableEvenement0.joueur_event_ref='$joueurId'
		GROUP by TableEvenement0.match_event_id
	) as e
on (TableEquipe.equipe_id=e.eq AND t1.matchIdRef=e.match_event_id )

LEfT Join (
		SELECT count(*) as B, TableEvenement0.match_event_id, Min(TableEvenement0.equipe_event_id) as eq
		FROM TableEvenement0 
		where TableEvenement0.code = 0 and TableEvenement0.joueur_event_ref='$joueurId'
		GROUP by TableEvenement0.match_event_id
	) as b
on (TableEquipe.equipe_id=b.eq AND t1.matchIdRef=b.match_event_id )
LEfT Join (
		SELECT count(*) as P, TableEvenement0.match_event_id, Min(TableEvenement0.equipe_event_id) as eq
		FROM TableEvenement0 
		where TableEvenement0.code = 1 and TableEvenement0.joueur_event_ref='$joueurId'
		GROUP by TableEvenement0.match_event_id
	) as P
on (TableEquipe.equipe_id=P.eq AND t1.matchIdRef=P.match_event_id )
LEfT  Join (
		SELECT count(*) as Pun, TableEvenement0.match_event_id, Min(TableEvenement0.equipe_event_id) as eq
		FROM TableEvenement0 
		where TableEvenement0.code = 4 and TableEvenement0.joueur_event_ref='$joueurId'
		GROUP by TableEvenement0.match_event_id
	) as Pun
on (TableEquipe.equipe_id=Pun.eq AND t1.matchIdRef=Pun.match_event_id )


where abonJoueurLigue.joueurId='$joueurId'
group by  saisonId, abonEquipeLigue.equipeId ORder By Ligue.ID_Ligue, saisonId, abonEquipeLigue.equipeId"
)or die(mysqli_error($conn));
$Ligues = array();
while($row=mysqli_fetch_array($result))
{
	$indLigue =getLigueIndex($Ligues,$row['ID_Ligue']);
	if($indLigue=-1){
		$mLigue=array();
		$mLigue['nom']=$row['Nom_Ligue'];
		$mLigue['ligueId']=$row['ID_Ligue'];
		$mLigue['saisons']=array();
		array_push($Ligues,$mLigue);
		$indLigue=count($Ligues)-1;
	}
	$indSaison =getSaisonIndex($Ligues[$indLigue]['saisons'],$row['saisonId']);
	if($indSaison=-1){
		$mSaison=array();
		$mSaison['saisonId']=$row['saisonId'];
		$mSaison['pm']=$row['premierMatch'];
		$mSaison['dm']=$row['dernierMatch'];
		$mSaison['equipes']=array();
		array_push($Ligues[$indLigue]['saisons'],$mSaison);
		$indSaison=count($Ligues[$indLigue]['saisons'])-1;
	}
	$indEquipe =getEquipeIndex($Ligues[$indLigue]['saisons'][$indSaison]['equipes'],$row['equipe_id']);
	if($indEquipe=-1){
		$mEquipe=array();
		$mEquipe['equipeId']=$row['equipe_id'];
		$mEquipe['nom']=$row['nom_equipe'];
		$mEquipe['pj']=$row['PJ'];
		$mEquipe['buts']=$row['Buts'];
		$mEquipe['passes']=$row['Passes'];
		$mEquipe['minPun']=$row['Pun'];	
		array_push($Ligues[$indLigue]['saisons'][$indSaison]['equipes'],$mEquipe);
		$indEquipe=count($Ligues[$indLigue]['saisons'][$indSaison]['equipes'])-1;
	}

}

////////////////////////////////////////
///	Sélectionne les ligues du joueur.
/*
$rJoueur = mysqli_query($conn, "SELECT L.ID_Ligue, L.Nom_Ligue 
						FROM Ligue as L 
						JOIN 
							( SELECT aJ.ligueId 
								FROM abonJoueurLigue as aJ 
								WHERE joueurId='$joueurId' ) as abons
						 on abons.ligueId = L.ID_Ligue 
						 WHERE true group by ID_Ligue")or die(mysqli_error($conn));


$IL=0;
$listeLigue=Array();
$maLigue=null;
while($maLigue=mysqli_fetch_array($rJoueur))
{
	$listeLigue[$IL]=Array();
	$listeLigue[$IL]['ligueId']=$maLigue['ID_Ligue'];
	$listeLigue[$IL]['nom']=$maLigue['Nom_Ligue'];
	$listeLigue[$IL]['saisons']=Array();
	$IL++;
}

$rJoueur2 = mysqli_query($conn, "SELECT abonEquipeLigue.*, Ligue.*,abonJoueurEquipe.* 
								FROM abonEquipeLigue 
								JOIN abonJoueurEquipe
									ON (abonJoueurEquipe.equipeId=abonEquipeLigue.equipeId)
								JOIN Ligue
									ON (Ligue.ID_Ligue=abonEquipeLigue.ligueId)
								WHERE joueurId = '$joueurId'")or die(mysqli_error($conn));
while($maLigue2 = mysqli_fetch_array($rJoueur2))
{
	
		$trouve=false;
		$b=0;
		while($b<$IL)
		{
		if($maLigue2['ligueId']==$listeLigue[$b]['ligueId'])
			{$trouve=true;}
		$b++;
		}
		if(!$trouve)
			{
			$listeLigue[$b]['ligueId']=$maLigue2['ligueId'];
			$listeLigue[$b]['nom']=$maLigue2['Nom_Ligue'];			
			$listeLigue[$b]['saisons']=Array();			
			}
}

/////////////////////////////////////////////

//////////////  S�LECTIONNE LES MATCHS  ///////////////////////////////////////

for($a=0;$a<count($listeLigue);$a++)
{
	$ISai=0;
$rSai = mysqli_query($conn,"SELECT * 
								FROM TableSaison 
								WHERE ligueRef = '{$listeLigue[$a]['ligueId']}'")or die(mysqli_error($conn));
while($rangSai=mysqli_fetch_array($rSai))
	{
	$listeLigue[$a]['saisons'][$ISai]=Array();
	$listeLigue[$a]['saisons'][$ISai]['saisonId']=$rangSai['saisonId'];
	$listeLigue[$a]['saisons'][$ISai]['pm']=$rangSai['premierMatch'];
	$listeLigue[$a]['saisons'][$ISai]['dm']=$rangSai['dernierMatch'];
	$listeLigue[$a]['saisons'][$ISai]['equipes']=Array();


	$reqMatchs="SELECT TableMatch.*, TableEvenement0.*, TableEquipe.* 
		 		FROM TableEvenement0
		 		JOIN TableMatch
		 			ON (TableMatch.matchIdRef=TableEvenement0.match_event_id)
		 		JOIN TableEquipe
		 			ON (TableEvenement0.equipe_event_id=TableEquipe.equipe_id)
		 		WHERE  
		 				joueur_event_ref='{$joueurId}' 
		 			AND chrono>=(UNIX_TIMESTAMP('{$listeLigue[$a]['saisons'][$ISai]['pm']}')*1000) 
		 			AND chrono<=(UNIX_TIMESTAMP('{$listeLigue[$a]['saisons'][$ISai]['dm']}')*1000) 
		 			AND TableMatch.ligueRef='{$listeLigue[$a]['ligueId']}'
		 			ORDER BY equipe_event_id";
mysqli_query($conn,"SET SQL_BIG_SELECTS=1");
	$rMatch = mysqli_query($conn,$reqMatchs)or die(mysqli_error($conn)." reqMatchs");
	$IE = -1;
	$eqId=0;
	while($rangMatch=mysqli_fetch_array($rMatch))
	{
		if($rangMatch['equipe_event_id']!=$eqId)
		{
			$IE++;	
			$listeLigue[$a]['saisons'][$ISai]['equipes'][$IE]['equipeId']=$rangMatch['equipe_event_id'];
			$listeLigue[$a]['saisons'][$ISai]['equipes'][$IE]['nom']=$rangMatch['nom_equipe'];
			$listeLigue[$a]['saisons'][$ISai]['equipes'][$IE]['buts']=0;
			$listeLigue[$a]['saisons'][$ISai]['equipes'][$IE]['passes']=0;
			$listeLigue[$a]['saisons'][$ISai]['equipes'][$IE]['minPun']=0;
			$listeLigue[$a]['saisons'][$ISai]['equipes'][$IE]['pj']=0;
			$eqId=$rangMatch['equipe_event_id'];
		}
		switch($rangMatch['code'])
		{
		case 0:
			$listeLigue[$a]['saisons'][$ISai]['equipes'][$IE]['buts']++;
			break;
		case 1:
			$listeLigue[$a]['saisons'][$ISai]['equipes'][$IE]['passes']++;
			break;
		case 3:	
			$listeLigue[$a]['saisons'][$ISai]['equipes'][$IE]['pj']++;
			break;
		case 4:
			$listeLigue[$a]['saisons'][$ISai]['equipes'][$IE]['minPun']++;
			break;
		}
	}

	$ISai++;
	}								
								
}
*/
echo "{\"Ligues\":".json_encode($Ligues)."}";

		
mysqli_close($conn);

?>
