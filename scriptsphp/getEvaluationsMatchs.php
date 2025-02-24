<?php
require '../scriptsphp/defenvvar.php';

$matchId = $_POST['matchId'];
$ligueId = $_POST['ligueId'];
	
	
	$qMatchId="SELECT *
						FROM MatchAVenir
						 WHERE match_id=$matchId";
$retour = mysqli_query($conn, $qMatchId)or die(mysqli_error($conn).$qMatchId);	

$vecRes =mysqli_fetch_row($retour) ;  
$alDom=json_decode($vecRes['alignementDom']);
$alVis=json_decode($vecRes['alignementVis']);

$qIndJDom =	"SELECT evalue,AVG(valeur)
						FROM EvaluationJoueurs
						 WHERE ligueId=$ligueId
						 GROUP BY evalue";
$mEval = mysqli_query($conn, $qIndJDom)or die(mysqli_error($conn).$qIndJDom);	

$b=0;
while($r = mysqli_fetch_array($mEval)) {
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

