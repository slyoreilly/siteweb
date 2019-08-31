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

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error($conn));
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");



//////////////////////////////////////////////////////
//
//  	Section "Recents Videos"
//
//////////////////////////////////////////////////////
	
//$receveur = $_POST["receveur"];
$ligueId = $_POST["ligueId"];
$joueurId = $_POST["joueurId"];

if(strcmp($joueurId,""))
{$reqChrono = "SELECT * 
from TableEvenement0 
INNER JOIN Video 
on
(TableEvenement0.event_id=Video.reference)
where 
(code=0 or code=5) AND 
joueur_event_ref={$joueurId} 
order by tableEvenement0.chrono DESC";
}
if(strcmp($ligueId,""))
{$reqChrono = "SELECT * 
			FROM Video
			JOIN TableMatch
				ON (Video.nomMatch = TableMatch.match_id)
			WHERE ligueRef={$ligueId} ORDER BY chrono DESC, eval DESC";}
//Anciennement TableMatch.matchIdRef


$rChrono = mysqli_query($conn, $reqChrono)
or die(mysqli_error($conn));  

$IM=0;
$recentsVideos = Array();
$iRef=0;
while ($rangChrono = mysqli_fetch_array($rChrono))
{
		if($rangChrono['angleOk']>=0){
			if($iRef!=$rangChrono['reference']){
				$iVid=0;
				if(isset($monEv)){
				array_push($recentsVideos,$monEv);
					
				}	
				
				$monEv=Array();
				$monEv['videos']=Array();
				$monEv['videos'][$iVid]=Array();	
				$monEv['reference']=$rangChrono['reference'];
				
	
	
				
					$monEv['videos'][$iVid]['eval']=$rangChrono['eval'];
					$monEv['videos'][$iVid]['nbVues']=$rangChrono['nbVues'];
					$monEv['videos'][$iVid]['chrono']=$rangChrono['chrono'];
					$monEv['videos'][$iVid]['nomMatch']=$rangChrono['nomMatch'];
					$monEv['videos'][$iVid]['nomFichier']=$rangChrono['nomFichier'];
					$monEv['videos'][$iVid]['videoId']=$rangChrono['videoId'];
					$iRef=$rangChrono['reference'];
			}else{
					$iVid++;
								$monEv['videos'][$iVid]=Array();	
					$monEv['videos'][$iVid]['eval']=$rangChrono['eval'];
					$monEv['videos'][$iVid]['nbVues']=$rangChrono['nbVues'];
					$monEv['videos'][$iVid]['chrono']=$rangChrono['chrono'];
					$monEv['videos'][$iVid]['nomMatch']=$rangChrono['nomMatch'];
					$monEv['videos'][$iVid]['nomFichier']=$rangChrono['nomFichier'];
					$monEv['videos'][$iVid]['videoId']=$rangChrono['videoId'];
				
				
				
			}

		}
}
if(isset($monEv)){
				array_push($recentsVideos,$monEv);
					
				}	


//////////////////////////////////////////////////////
//
//  	Section "Populaire Videos"
//
//////////////////////////////////////////////////////
	

if(strcmp($joueurId,""))
{$reqPop = "SELECT * 
			FROM Video
			WHERE tagPrincipal={$joueurId} ORDER BY nbVues DESC";}
if(strcmp($ligueId,""))
{$reqPop = "SELECT * 
			FROM Video
			JOIN TableMatch
				ON (Video.nomMatch = TableMatch.match_id)
			WHERE ligueRef={$ligueId} ORDER BY nbVues DESC";
}
			$rPop = mysqli_query($conn,$reqPop)
or die(mysqli_error($conn));  

$plusVuesVideos = Array();
while ($rangPop = mysqli_fetch_array($rPop))
{
	if($rangPop['angleOk']>=0){
	
	$maLigne = Array();
	
	
	$maLigne['eval']=$rangPop['eval'];
	$maLigne['nbVues']=$rangPop['nbVues'];
	$maLigne['chrono']=$rangPop['chrono'];
	$maLigne['nomMatch']=$rangPop['nomMatch'];
	$maLigne['nomFichier']=$rangPop['nomFichier'];
	$maLigne['videoId']=$rangPop['videoId'];
	$maLigne['angleOk']=$rangPop['angleOk'];
	if($rangPop['tagPrincipal']!=null)
		{$maLigne['tag1']=$rangPop['tagPrincipal'];}
	
	array_push($plusVuesVideos,$maLigne);
	}
}
//////////////////////////////////////////////////////
//
//  	Section "Top Rated Videos"
//
//////////////////////////////////////////////////////
	

if(strcmp($joueurId,""))
{$reqTop = "SELECT * 
			FROM Video
			WHERE tagPrincipal={$joueurId} ORDER BY eval DESC";}
if(strcmp($ligueId,""))
{$reqTop = "SELECT * 
			FROM Video
			JOIN TableMatch
				ON (Video.nomMatch = TableMatch.match_id)
			WHERE ligueRef={$ligueId} ORDER BY eval DESC";
}
$rTop = mysqli_query($conn,$reqTop)
or die(mysqli_error($conn));  

$IM=0;
$topVideos = Array();
while ($rangTop = mysqli_fetch_array($rTop))
{
		if($rangTop['angleOk']>=0){
	
	$maLigne = Array();
	
	
	$maLigne['eval']=$rangTop['eval'];
	$maLigne['nbVues']=$rangTop['nbVues'];
	$maLigne['chrono']=$rangTop['chrono'];
	$maLigne['nomMatch']=$rangTop['nomMatch'];
	$maLigne['nomFichier']=$rangTop['nomFichier'];
	$maLigne['videoId']=$rangTop['videoId'];
	
	if($rangTop['tagPrincipal']!=null)
	{$maLigne['tag1']=$rangTop['tagPrincipal'];}
	
	array_push($topVideos,$maLigne);
	}
}

$retour=array();

$retour['topVideos']=$topVideos;
$retour['recentsVideos']=$recentsVideos;
$retour['popVideos']=$plusVuesVideos;

echo json_encode($retour);
	


?>
