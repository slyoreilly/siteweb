<?php
require '../scriptsphp/defenvvar.php';
////////////////////////////////////////////////////////
//  TraiteSync est la fonction principale appelée par l'app SyncStats
//  pour à la fois télécharger les données des ligues et téléverser le contenu du téléphone.


$username = $_POST['username'];
$deviceId = $_POST['deviceId'];
$versionCode = $_POST['versionCode'];
$syncJ = $_POST['syncJ'];
$syncMav = $_POST['syncMav'];
$transE = $_POST['transE'];
$transL = $_POST['transL'];
$transPJ = $_POST['transPJ'];
$matchsTS = $_POST['matchs'];
$heure = $_POST['heure'];
$heureServeur = time()*1000;
$controlTemps=array();
array_push($controlTemps,time());

//echo $_POST['matchs'];
//$t1 = $_POST['transJ'];
$t1 = stripslashes(stripslashes(stripslashes($_POST['transJ'])));
$t2 = str_replace('"{','{',$t1);
$transJ = str_replace('}"','}',$t2);
$decTransJ = json_decode($transJ, true);

$t1 = stripslashes(stripslashes(stripslashes($_POST['transE'])));
$t2 = str_replace('"{','{',$t1);
$transE = str_replace('}"','}',$t2);
$decTransE = json_decode($transE, true);



$m1 = stripslashes($matchsTS);
$m2 = str_replace('"{','{',$m1);
$leMatch=array();

$matchjson = str_replace('}"','}',$m2);
$leMatch = json_decode($matchjson, true);
//error_log("matchjson\n".$matchjson, 3, "/home1/syncsta1/public_html/scriptsphp/error_log");
//echo "\n Le match ".$leMatch."\n";
//echo json_last_error()."!";
//echo $matchjson;
//echo "\n var_dump leMatch ".var_dump($leMatch)."\n";
//echo "\n var_dump m1 ".var_dump($m1)."\n";
//echo "\n".json_encode($leMatch)."\n";




// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");
	
///////////////////////////////////////////////////////////////////////////////////////



	// Retrieve all the data from the "example" table
$resultUser = mysqli_query($conn,"SELECT * FROM TableUser")
or die(mysqli_error($conn));  
while($rangeeUser=mysqli_fetch_array($resultUser))
{
	
		if(!strcmp($rangeeUser['username'],$username))
	{$userSelect =$rangeeUser['noCompte'];
	}
		// Prend le ID du user pour trouver les ligues abonn�es.
}
array_push($controlTemps,time());

$resultAbon = mysqli_query($conn,"SELECT * FROM AbonnementLigue ORDER BY ligueid")
or die(mysqli_error($conn));  

$AbonSelect = array();
$dernierLogApp= array();
while($rangeeAbon=mysqli_fetch_array($resultAbon))
	{
		if($rangeeAbon['userid']==$userSelect)
			{array_push($AbonSelect, $rangeeAbon['ligueid']);}
	}
	
	$qAbonArb="SELECT * FROM TableArbitre 
								JOIN abonArbitreLigue
									ON (TableArbitre.arbitreId=abonArbitreLigue.arbitreId)
								JOIN TableUser
									ON 	(TableArbitre.userId=TableUser.noCompte)
								WHERE TableArbitre.userId='{$userSelect}'
								ORDER BY ligueId";
$resultAbonArb = mysqli_query($conn,$qAbonArb)
or die(mysqli_error($conn).$qAbonArb);  

while($rangeeAbonArb=mysqli_fetch_array($resultAbonArb))
	{
		if(!in_array($rangeeAbonArb['ligueId'],$AbonSelect))
			{array_push($AbonSelect, $rangeeAbonArb['ligueId']);}
	}
	array_push($controlTemps,time());
	// On obtient un array de ligueID auquel userSelect est abonn�.

	mysqli_close($conn);

$lesSync=array();
$lesSync=json_decode(stripslashes($syncJ));
$lesSyncMAV=array();
$lesSyncMAV=json_decode(stripslashes($syncMav));
//echo "syncJ:   ".stripslashes($syncJ)."\n";
//echo "lesSync:   ".$lesSync."\n";

/*foreach($lesSync['ligueId'] as $mesLid)
{echo $mesLid."\n";}*/
 //**/
  
//echo " ".count($AbonSelect);
$rep=array();
$lesLigues=array();
$lesMAV=array();
$lesMAV2=array();

function my_walk_recursive(&$array, $mode) {
    foreach ($array as $k => &$v) {
        if (!is_array($v)) {
            // leaf node (file) -- print link
            switch ($mode)
            {
				case 1:
					changerId($v,$k);
				break;
				case 2: 
					changerEqId($v,$k);
				break;
            }
         }
        else {
        	switch ($mode)
            {
				case 1:
						if(!strcmp($k,'alDom')||!strcmp($k,'alVis' ))
					{
					if(is_array($v))
						foreach($v as &$lesJ)
						{
							if($lesJ>999999)
							{
								$lesJ=vieuAnouveau($lesJ);
							}
						}
					}
					else{
						my_walk_recursive($v,$mode);
					}
					changerId($v,$k);
				break;
				case 2:
					my_walk_recursive($v,$mode);
				break;
			}


        }
    }
}

function changerEqId(&$valeur,$cle)
{
	if(!strcmp($cle,'eqDom')||!strcmp($cle,'eqVis')||!strcmp($cle,'eqId')||!strcmp($cle,'advId'))
	{
		if($valeur>999999&&$valeur<1000100)
		{
			$valeur=vieuAnouveauEq($valeur);
		}
	}
	
}


function changerId(&$valeur,$cle)
{
	if(!strcmp($cle,'m')||!strcmp($cle,'p1')||!strcmp($cle,'p2')||!strcmp($cle,'joueur')||!strcmp($cle,'gardien')||!strcmp($cle,'gDom')||!strcmp($cle,'gVis'))
	{
		if($valeur>999999)
		{
			$valeur=vieuAnouveau($valeur);
		}
	}
	if(!strcmp($cle,'alDom')||!strcmp($cle,'alVis'))
	{
		if(is_array($valeur))
		foreach($valeur as &$lesJ)
		{
				if($lesJ>999999)
					{
					$lesJ=vieuAnouveau($lesJ);
				}
		
		}
		
	}
	
	
	
}



function vieuAnouveau($vieu)
{
	global $retTransJ;
	if(is_array($retTransJ))
	{
		foreach($retTransJ as $chJ){
				if($chJ['vieuId']==$vieu)
					return $chJ['nouveauId'];
				}
		unset($chJ);
	}
	
				return null;
}

function vieuAnouveauEq($vieu)
{
	global $retTransE;
	if(is_array($retTransJ))
	{
		foreach($retTransE as $chE){
				if($chE['vieuId']==$vieu)
					return $chE['nouveauId'];
				}
		unset($chE);
	}
				return null;
}


//		if($transJ!=null)
//			{
include 'dechargeTransactions.php';
array_push($controlTemps,time());
//				$lesLigues[$ILigue]=$ligue;
				
foreach($leMatch as &$vec){
//					$vec->{'vieuId'};
		my_walk_recursive($vec, 1);
	}
unset($vec);
foreach($leMatch as &$vec){
	//				$vec->{'vieuId'};
	my_walk_recursive($vec, 2);
}
unset($vec);
				

//			}



$ILigue=0;
$nbLigueCons=0;
	while($ILigue<count($AbonSelect))
	{	
//	$resultLigue = mysql_query("SELECT * FROM {$tableLigue} ORDER BY ID_Ligue")
//	or die(mysql_error());  
	$ligueId=$AbonSelect[$ILigue];
	$trouveLigue=false;
		for($a=0;$a<count($lesSync);$a++)
			{
			if($lesSync[$a]->{'ligueId'}==$ligueId)	
				{$vielledate=$lesSync[$a]->{'dernierMAJ'};
				$trouveLigue=true;}
			}
		for($a=0;$a<count($lesSyncMAV);$a++)
			{
			if($lesSyncMAV[$a]->{'ligueId'}==$ligueId)	
				$vielledateMAV=$lesSyncMAV[$a]->{'dernierMAJ'};
			}		
			if(!$trouveLigue)
			{$vielledate=0;}


		if($syncJ!=null)
			{	include 'construitLigue.php';
				if(isset($ligue))
				{
			
				if($ligue!=null)
				{
				$lesLigues[$nbLigueCons]=$ligue;
				$nbLigueCons++;}
				}
			}
		if($syncMav!=null)
			{	include 'dechargeMAV.php';
				include 'dechargeMAV2.php';
				$lesMAV[$ILigue]=$vecMAV;
				$lesMAV2[$ILigue]=$vecMAV2;
			}
		$ILigue++;
		
		
		
		
	}//Fin du scan des ligues auquel l'utilisateur est abbonn�.
	array_push($controlTemps,time());
	
//echo json_encode($Sommaire);
//////////////////////////////////////////////////////////////////////////////////////////

$syncOK = array();
$extra = array();
$extra['info0']=$controlTemps;

//if(35>(int)$versionCode){
//		$extra['DM']=1;
//include 'dechargeMatchs.php';
//
//}
//else{
	$extra['DM']=2;
	include 'dechargeMatchs_2.php';
	if(isset($DMX)){
$extra['DM']=$DMX;}
//}
array_push($controlTemps,time());
//include('../scriptsphp/actualiseMatchs.php');			// ActualiseMAtch a emmener de gros problème de répétition des entrées...

array_push($controlTemps,time());
include 'majJoueur2.php';
array_push($controlTemps,time());
//$extra['info']=json_encode($leMatch);
//$extra['info2']=stripslashes($matchsTS);
//$extra['info3']=stripslashes($_POST['matchs']);
//$extra['info4']=stripslashes($m1);
//$extra['info5']=$infoMav;
$extra['info6']=$heureServeur - time()*1000;

$rep['syncOK']=$syncOK;
$rep['extra']=$extra;
$rep['transJ']=$retTransJ;
$rep['transE']=$retTransE;
$rep['MAV']=$lesMAV;
$rep['MAV2']=$lesMAV2;
$rep['ligues']=$lesLigues;
//echo json_encode($extra);
echo json_encode($rep);

//echo "\n".$matchjson."\n";

//echo "\n".json_encode($leMatch)."\n";
//echo "post: ".$_POST['matchs']."\n";
//echo "m1:".$m1."\n";

//header('Content-type: text/plain; charset=utf-8');
 //header("HTTP/1.1 200 OK");

?>