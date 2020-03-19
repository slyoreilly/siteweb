<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';e donn�es
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

	$vecInfo = Array();


//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////
	
//$receveur = $_POST["receveur"];
$ligueId = $_POST["ligueId"];
$joueurId = $_POST["joueurId"];

if(strcmp($joueurId,""))
{$reqChrono = "SELECT * 
			FROM Video
			WHERE tagPrincipal={$joueurId} ORDER BY chrono DESC";}
if(strcmp($ligueId,""))
{$reqChrono = "SELECT * 
			FROM Video
			JOIN TableMatch
				ON (Video.nomMatch = TableMatch.matchIdRef)
			JOIN TableEvenement0
				ON (Video.reference=TableEvenement0.event_id)	
			WHERE ligueRef={$ligueId} And type=0 ORDER BY reference DESC, camId ASC";}

$rChrono = mysql_query($reqChrono)
or die(mysql_error());  

$IM=0;
$recentsVideos = Array();
$ref0=0;
$maLigne = Array();
$maLigne['video']=Array();
		
/////////////  A r'checker!
while ($rangChrono = mysql_fetch_array($rChrono))
{
	if($rangChrono['reference']!=$ref0){
			if($ref0>0){
			array_push($recentsVideos,$maLigne);}
			$maLigne = Array();
			$maLigne['video']=Array();
			$ref0=$rangChrono['reference'];
		
	}
		if($rangChrono['angleOk']>=0){
	
	
	$maLigne['eval']=$rangChrono['eval'];
	$maLigne['nbVues']+=$rangChrono['nbVues'];
	$maLigne['chrono']=$rangChrono['chrono'];
	$maLigne['nomMatch']=$rangChrono['nomMatch'];
	$monVid=Array();
	$monVid['videoId']=$rangChrono['videoId'];
	$monVid['cam']=$rangChrono['camId'];
	$monVid['fic']=$rangChrono['nomFichier'];
	$monVid['angleOk']=$rangChrono['angleOk'];

	if($rangChrono['tagPrincipal']!=null)
		$maLigne['tag1']=$rangChrono['tagPrincipal'];
	
		}
	array_push($maLigne['video'],$monVid);
		
}
array_push($recentsVideos,$maLigne);

	
	
///////////////////   Pop Videos
	
	
if(strcmp($joueurId,""))
{$reqPop = "SELECT * 
			FROM Video
			WHERE tagPrincipal={$joueurId} ORDER BY nbVues DESC";}
if(strcmp($ligueId,""))
{$reqPop = "SELECT * 
			FROM Video
			JOIN TableMatch
				ON (Video.nomMatch = TableMatch.matchIdRef)
			JOIN TableEvenement0
				ON (Video.reference=TableEvenement0.event_id)	
			WHERE ligueRef={$ligueId} And type=0 ORDER BY  nbVues DESC, reference DESC, camId ASC";
}
			$rPop = mysql_query($reqPop)
or die(mysql_error().$reqPop);  



$ref0=0;
unset($maligne);
unset($monVid);
$maLigne = Array();
$maLigne['video']=Array();
$plusVuesVideos = Array();
while ($rangPop = mysql_fetch_array($rPop))
{
	if($rangPop['reference']!=$ref0){
			if($ref0>0){
			$plusVuesVideos[]=$maLigne;
			}
			$maLigne = Array();
			$maLigne['video']=Array();
			$ref0=$rangPop['reference'];
		
	}
		//if($rangPop['angleOk']>=0){
	$maLigne = Array();
	
	
	$maLigne['eval']=$rangPop['eval'];
	$maLigne['nbVues']=$rangPop['nbVues'];
	$maLigne['chrono']=$rangPop['chrono'];
	$maLigne['nomMatch']=$rangPop['nomMatch'];
	$monVid=Array();
	$monVid['videoId']=$rangPop['videoId'];
	$monVid['cam']=$rangPop['camId'];
	$monVid['fic']=$rangPop['nomFichier'];
	$monVid['angleOk']=$rangPop['angleOk'];
	
	//$maLigne['angleOk']=$rangPop['angleOk'];
	if($rangPop['tagPrincipal']!=null)
		{$maLigne['tag1']=$rangPop['tagPrincipal'];}
		
		 $maLigne['video'][] = $monVid;
		//$diagnost = array_push($maLigne['video'],$monVid);
		//if($diagnost<1)
		//{ array_push($vecInfo, implode("|",$maLigne));}
		//$plusVuesVideos[]=$maLigne;
	//array_push(,$maLigne);
	}
	
//}
$plusVuesVideos[]=$maLigne;



////////////////////////    Top Videos

if(strcmp($joueurId,""))
{$reqTop = "SELECT * 
			FROM Video
			WHERE tagPrincipal={$joueurId} ORDER BY eval DESC";}
if(strcmp($ligueId,""))
{$reqTop = "SELECT * 
			FROM Video
			JOIN TableMatch
				ON (Video.nomMatch = TableMatch.matchIdRef)
			JOIN TableEvenement0
				ON (Video.reference=TableEvenement0.event_id)	
			WHERE ligueRef={$ligueId} And type=0 ORDER BY eval DESC, reference DESC, camId ASC";}
			
			$rTop = mysql_query($reqTop)
or die(mysql_error());  






$IM=0;
$ref0=0;
unset($maligne);
unset($monVid);
$maLigne = Array();
$maLigne['video']=Array();


$topVideos = Array();
while ($rangTop = mysql_fetch_array($rTop))
{
if($rangTop['reference']!=$ref0){
			if($ref0>0){
			//array_push($topVideos,$maLigne);
			$topVideos[]=$maLigne;}
			$maLigne = Array();
			$maLigne['video']=Array();
			$ref0=$rangTop['reference'];
		
	}
			
			//$maLigne = Array();
		if($rangTop['angleOk']>=0){
	
		
	
	
		$maLigne['eval']=$rangTop['eval'];
		$maLigne['nbVues']=$rangTop['nbVues'];
		$maLigne['chrono']=$rangTop['chrono'];
		$maLigne['nomMatch']=$rangTop['nomMatch'];
		$monVid=Array();
		$monVid['videoId']=$rangTop['videoId'];
		$monVid['cam']=$rangTop['camId'];
		$monVid['fic']=$rangTop['nomFichier'];
		$monVid['angleOk']=$rangTop['angleOk'];
	
	
		if($rangTop['tagPrincipal']!=null)
		{$maLigne['tag1']=$rangTop['tagPrincipal'];}
		
		$maLigne['video'][] = $monVid;
		
		//$topVideos[]=$maLigne;
	
		}

}
	$topVideos[]=$maLigne;


$retour=array();
$retour['topVideos']=$topVideos;
$retour['recentsVideos']=$recentsVideos;

$retour['popVideos']=$plusVuesVideos;
$retour['info']=$vecInfo;

echo json_encode($retour);
	


?>
