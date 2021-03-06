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
////////////////////////////////////////
///	Sélectionne les ligues du joueur.

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

echo "{\"Ligues\":".json_encode($listeLigue)."}";

		
mysqli_close($conn);

?>
