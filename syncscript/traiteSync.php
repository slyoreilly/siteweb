<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$username = $_POST['username'];

$syncJ = $_POST['syncJ'];
$syncMav = $_POST['syncMav'];
$transE = $_POST['transE'];
$transL = $_POST['transL'];
$transPJ = $_POST['transPJ'];
$matchsTS = $_POST['matchs'];
$heure = $_POST['heure'];
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


if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");

}
	mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");
	
///////////////////////////////////////////////////////////////////////////////////////



	// Retrieve all the data from the "example" table
$resultUser = mysql_query("SELECT * FROM TableUser")
or die(mysql_error());  
while($rangeeUser=mysql_fetch_array($resultUser))
{
		if(!strcmp($rangeeUser['username'],$username))
	{$userSelect =$rangeeUser['noCompte'];
	}
		// Prend le ID du user pour trouver les ligues abonn�es.
}

$resultAbon = mysql_query("SELECT * FROM AbonnementLigue ORDER BY ligueid")
or die(mysql_error());  

$AbonSelect = array();
$dernierLogApp= array();
while($rangeeAbon=mysql_fetch_array($resultAbon))
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
$resultAbonArb = mysql_query($qAbonArb)
or die(mysql_error().$qAbonArb);  

while($rangeeAbonArb=mysql_fetch_array($resultAbonArb))
	{
		if(!in_array($rangeeAbonArb['ligueId'],$AbonSelect))
			{array_push($AbonSelect, $rangeeAbonArb['ligueId']);}
	}
	
	// On obtient un array de ligueID auquel userSelect est abonn�.
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
	if(!strcmp($cle,'m')||!strcmp($cle,'p1')||!strcmp($cle,'p2')||!strcmp($cle,'joueur')||!strcmp($cle,'gardien'))
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
//				$lesLigues[$ILigue]=$ligue;
				
				foreach($leMatch as &$vec){
//					$vec->{'vieuId'};
					array_walk_recursive($vec, 'changerId');
				}
				unset($vec);
				foreach($leMatch as &$vec){
	//				$vec->{'vieuId'};
					array_walk_recursive($vec, 'changerEqId');
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
				$lesMAV[$ILigue]=$vecMAV;
			}
		$ILigue++;
		
		
		
		
	}//Fin du scan des ligues auquel l'utilisateur est abbonn�.
	
	
//echo json_encode($Sommaire);
//////////////////////////////////////////////////////////////////////////////////////////

$syncOK = array();
$extra = array();
$extra['info0']="pouite";

include 'dechargeMatchs.php';
include 'majJoueur2.php';

//$extra['info']=json_encode($leMatch);
$extra['info2']=stripslashes($matchsTS);
$extra['info3']=stripslashes($_POST['matchs']);
$extra['info4']=stripslashes($m1);
$extra['info5']=$infoMav;


$rep['syncOK']=$syncOK;
$rep['extra']=$extra;
$rep['transJ']=$retTransJ;
$rep['transE']=$retTransE;
$rep['MAV']=$lesMAV;
$rep['ligues']=$lesLigues;
//echo json_encode($extra);
echo json_encode($rep);

//echo "\n".$matchjson."\n";

//echo "\n".json_encode($leMatch)."\n";
//echo "post: ".$_POST['matchs']."\n";
//echo "m1:".$m1."\n";

header('Content-type: text/plain; charset=utf-8');
 header("HTTP/1.1 200 OK");

?>