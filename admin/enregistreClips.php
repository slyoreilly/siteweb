<?php
require '../scriptsphp/defenvvar.php';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
$params = array();
$params =json_decode($_POST['clips'],true);
$heure = $_POST['heure'];
$avanceServeur=time()*1000-$heure;

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
	
	
$syncOK=array();	
for($a=0;$a<count($params);$a++)
{	
$camID = $params[$a]['camID'];
$rSServ=$params[$a]['chrono']+$avanceServeur;
	
if($params[$a]['webId']==0)
	{
		$monObj=array();
	$qSel="SELECT * FROM Clips WHERE nomMatch='{$params[$a]['nomMatch']}'";	
	$retSel=mysql_query($qSel) or die("Erreur: "+$qSel+"\n"+mysql_error());
	if(mysql_num_rows($retSel)==0)
		{	
		$query = "INSERT INTO Clips (nomMatch,chrono) ".
		"VALUES ('{$params[$a]['nomMatch']}','{$rSServ}')";
		mysql_query($query) or die("Erreur: "+$query+"\n"+mysql_error());
		$monObj['nomFic']=$nomFic;
		$monObj['etat']='insert';
		$monObj['chrono']=$rSServ;
		
		array_push($syncOK, $monObj);
		
		}
	else {
		if(file_exists('../lookatthis/'.$nomFic))
			{
			$monObj['etat']='deja';
			}
		else
			{
			$monObj['etat']='insert';
			}
		$monObj['nomFic']=$nomFic;
		$monObj['chrono']=$rSServ;
		
		array_push($syncOK, $monObj);
	}
	}

}
	
	echo json_encode($syncOK);
	
	if(json_encode($syncOK)==False)
	{echo "erreur, count(syncOK:): ".count($syncOK)."- count($params): ".count($params);}
?>
