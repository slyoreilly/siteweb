<?php
require '../scriptsphp/defenvvar.php';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
$mavId = $_POST['mavId'];
$ligueId = $_POST['ligueId'];


//$ligueId = $_POST['ligueId'];

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
//	mysql_set_charset('utf8');
	
	$qMavId="SELECT *
						FROM MatchAVenir
						 WHERE mavId=$mavId";
$retour = mysql_query($qMavId)or die(mysql_error().$qMavId);	

$vecRes =mysql_fetch_row($retour) ;   //mavId; matchId; date; dateFin; ligueId; eqDom; alignementDom; gardienDom; eqVis; alignementVis; gardienVis; dernierMAJ; arenaId; arbitreId.
$alDom=json_decode($vecRes[6]);
$alVis=json_decode($vecRes[9]);

$qIndJDom =	"SELECT evalue,AVG(valeur)
						FROM EvaluationJoueurs
						 WHERE ligueId=$ligueId
						 GROUP BY evalue";
$mEval = mysql_query($qIndJDom)or die(mysql_error().$qIndJDom);	

$b=0;
while($r = mysql_fetch_array($mEval)) {
    $vecEval[$b][0] = $r[0];
	    $vecEval[$b][1] = $r[1];
	
$b++;	
}
	
	$sommeDom=array();
	$sommeVis=array();
	$td=0;
	$tv=0;
	
for($c=0;$c<count($vecEval);$c++)
{
		if(in_array($vecEval[$c][0], $alDom))
		{array_push($sommeDom,$vecEval[$c][1]);
		$td=$td+$vecEval[$c][1];}
		if(in_array($vecEval[$c][0], $alVis))
		{array_push($sommeVis,$vecEval[$c][1]);
		$tv=$tv+$vecEval[$c][1];}
		$c++;
	
}	
		
						 	

$retEval=array();

$retEval['avgJoueursDom']=$td/count($sommeDom);
$retEval['avgJoueursVis']=$tv/count($sommeVis);
$retEval['JoueursDom']=$sommeDom;
$retEval['JoueursVis']=$sommeVis;
$retEval['vecRval']=$vecEval;
echo json_encode($retEval);


		header("HTTP/1.1 200 OK");
?>

