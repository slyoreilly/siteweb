<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';

session_start();


//$fichier = $_POST['fichier'];
//echo $_POST['videos'];
$params = array();
$params =json_decode($_POST['videos'],true);
$heure = $_POST['heure'];
$emplacement = $_POST['emplacement'];
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
$camID = $params[$a]['video']['camID'];
$nomFic=array_pop(explode('/',$params[$a]['video']['nomFic']));
$rSServ=$params[$a]['video']['chrono']+$avanceServeur;
	
if(!empty($nomFic))
	{
		$monObj=array();   /// 11/12/2017 j'ai remplacé matchIdRef par match_id.
	$qSel="SELECT * FROM Video 
			JOIN TableMatch
				ON (match_id = nomMatch)  
		WHERE nomMatch='{$params[$a]['video']['nomMatch']}' AND camId='{$camID}' AND nomFichier='{$nomFic}'";	
	$retSel=mysql_query($qSel) or die("Erreur: "+$qSel+"\n"+mysql_error());
		if(mysql_num_rows($retSel)>0){
			while ($rangSel = mysql_fetch_array($retSel))
			{
			// N'entre pas dans cette boucle si non-enregistré dans table Video.
			
			
			
				$type=1000;
				$reference=1000;
		
				$emplacement=$rangSel['emplacement'];
				if(is_null($emplacement)){
					$emplacement="www.syncstats.com";
				}
				$cv= json_decode(stripslashes($params[$a]['video']['cv']),true);
			
				if(isset($cv['type'])){
					$type=$cv['type'];
				}	
				if(isset($cv['reference'])){
					$reference=$cv['reference'];
				}
			}
			$esSimple=$params[$a]['video']['esSimple'];
			if(file_exists('http://'.$emplacement.'/lookatthis/'.$nomFic))
			{
				if($esSimple!=12){
				$monObj['etat']='insert';					
				}else{
				$monObj['etat']='deja';					
				}
			}
			else
			{
				$monObj['etat']='insert';
			}
			$monObj['nomFic']=$nomFic;
			$monObj['chrono']=$rSServ;
		
			array_push($syncOK, $monObj);
				
		}
	
		
			else {
				
					$cv= json_decode(stripslashes($params[$a]['video']['cv']),true);
			
				if(isset($cv['type'])){
					$type=$cv['type'];
				}	
				if(isset($cv['reference'])){
					$reference=$cv['reference'];
				}		
		$query = "INSERT INTO Video (nomFichier,nomMatch,chrono,camId,type,reference,emplacement) ".
		"VALUES ('{$nomFic}','{$params[$a]['video']['nomMatch']}','{$rSServ}','{$camID}','{$type}','{$reference}','{$emplacement}')";
		mysql_query($query) or die("Erreur: "+$query+"\n"+mysql_error());
		
		$monObj['nomFic']=$nomFic;
		$monObj['etat']='insert';
		$monObj['chrono']=$rSServ;
		
		array_push($syncOK, $monObj);
			
				
				
				
			}
		

		
		
		
		///////  Section Forfait, pas achevé.

		$qForfait="SELECT cleValeur FROM Ligue 
			
			WHERE ID_Ligue='{$rangSel['ligueRef']}'";	
	$retFor=mysql_query($qForfait) or die("Erreur: "+$qForfait+"\n"+mysql_error());
		$resForfait = mysql_fetch_row($retFor);
		$cleValeurForfait = json_decode($resForfait[0],true);
		
		$forfaitId = $cleValeurForfait['contrat']['forfaitId'];
		
		
		
		$_SESSION['forfaitId'] = $forfaitId;
		$_SESSION['nomFichier'] = $nomFic;
		$_SESSION['ligueId'] = $rangSel['ligueRef'];
	
		// include 'gestionnaireVideoSJHT.php';

		
		}



	

}
	
	echo json_encode($syncOK);
	
	if(json_encode($syncOK)==False)
	{echo "erreur, count(syncOK:): ".count($syncOK)."- count($params): ".count($params);}
	mysql_close();
?>
