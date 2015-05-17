<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);

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
	
	
function trouveIDParNomUser($nomUser)
{
$fResultUser = mysql_query("SELECT noCompte 
								FROM TableUser 
								WHERE username='{$nomUser}'")
or die(mysql_error());  
$rU = mysql_fetch_row($fResultUser);
if (mysql_num_rows($fResultUser)>0)
{
return $rU[0];
}
else{return -1;}

}	

$userId = trouveIDParNomUser($_POST['userId']);
$ligueId = $_POST['ligueId'];




	
//$jDom = json_decode($jDomJSON, true);
//$jVis = json_decode($jVisJSON, true);
if($ligueId!=0&&$ligueId!=undefined)
	{
		if ($userId==-1)
		{
			$retour = mysql_query("SELECT *	
						FROM AbonnementLigue
						 WHERE ligueid='{$ligueId}'
						 	")or die(mysql_error());
			$rArb = mysql_query("SELECT *	
						FROM abonArbitreLigue
						 WHERE ligueId='{$ligueId}'
						 	")or die(mysql_error());
		}
		else{
		$retour = mysql_query("SELECT *	
						FROM AbonnementLigue
						 WHERE ligueid='{$ligueId}'
						 AND userid='{$userId}'
						 	")or die(mysql_error());
		$rArb = mysql_query("SELECT *	
						FROM abonArbitreLigue
						JOIN TableArbitre
							ON (TableArbitre.arbitreId=abonArbitreLigue.arbitreId)
						 WHERE ligueId='{$ligueId}'
						 AND userId='{$userId}'
						 	")or die(mysql_error());
		 $rJou= mysql_query("SELECT  TableJoueur.proprio, abonJoueurEquipe.permission  , abonEquipeLigue.ligueId, abonJoueurEquipe.joueurId

		 				FROM TableJoueur
					 JOIN abonJoueurEquipe 
						 ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId)
					LEFT JOIN abonEquipeLigue 
						 ON (abonJoueurEquipe.equipeId=abonEquipeLigue.equipeId)
						  						 						 
					WHERE  TableJoueur.proprio={$userId}
						AND	abonJoueurEquipe.debutAbon<=NOW()
						AND abonJoueurEquipe.finAbon>=NOW()
						AND abonEquipeLigue.debutAbon<=NOW()
						AND abonEquipeLigue.finAbon>=NOW()
						AND abonEquipeLigue.ligueId = 	{$ligueId}		")
			or die(mysql_error()."query gros stock A");  

			 $rLibre= mysql_query("SELECT  TableJoueur.proprio, abonJoueurLigue.permission  , abonJoueurLigue.ligueId, abonJoueurLigue.joueurId

		 				FROM TableJoueur
					 JOIN abonJoueurLigue
						 ON (TableJoueur.joueur_id=abonJoueurLigue.joueurId)
						  						 						 
					WHERE  TableJoueur.proprio={$userId}
						AND abonJoueurLigue.debutAbon<=NOW()
						AND abonJoueurLigue.finAbon>=NOW()
						AND abonJoueurLigue.ligueId = 	{$ligueId}		")
			or die(mysql_error()."query gros stock AA");  
			
										
						 								
		}
	}
else 
	{
		$retour = mysql_query("SELECT *	
						FROM AbonnementLigue
						WHERE userid='{$userId}'
						 	")or die(mysql_error());
		$rArb = mysql_query("SELECT *	
						FROM abonArbitreLigue
						JOIN TableArbitre
							ON (TableArbitre.arbitreId=abonArbitreLigue.arbitreId)
						 WHERE userId='{$userId}'
						 	")or die(mysql_error());
			 $rJou= mysql_query("SELECT  TableJoueur.proprio, abonJoueurEquipe.permission  , abonEquipeLigue.ligueId

		 				FROM TableJoueur
					 JOIN abonJoueurEquipe 
						 ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId)
					LEFT JOIN abonEquipeLigue 
						 ON (abonJoueurEquipe.equipeId=abonEquipeLigue.equipeId)
						  						 						 
					WHERE  TableJoueur.proprio='{$userId}'
						AND	abonJoueurEquipe.debutAbon<=NOW()
						AND abonJoueurEquipe.finAbon>=NOW()
						AND abonEquipeLigue.debutAbon<=NOW()
						AND abonEquipeLigue.finAbon>=NOW()")
			or die(mysql_error()."query gros stock B");  

			 $rLibre= mysql_query("SELECT  TableJoueur.proprio, abonJoueurLigue.permission  , abonJoueurLigue.ligueId, abonJoueurLigue.joueurId

		 				FROM TableJoueur
					 JOIN abonJoueurLigue
						 ON (TableJoueur.joueur_id=abonJoueurLigue.joueurId)
						  						 						 
					WHERE  TableJoueur.proprio='{$userId}'
						AND abonJoueurLigue.debutAbon<=NOW()
						AND abonJoueurLigue.finAbon>=NOW()
							")
			or die(mysql_error()."query gros stock BB");  

	
	}
$strRetour.= mysql_num_rows($retour);

$vecRole = array();
$Ir=0;
while($r = mysql_fetch_assoc($retour)) {
    $vecRole[$Ir]=array();
	$vecRole[$Ir]['userId']=$r['userid'];
	$vecRole[$Ir]['ligueId']=$r['ligueid'];
	$vecRole[$Ir]['type']=$r['type'];
	$Ir++;
}

$vecArb = array();
$Ia=0;
while($rA = mysql_fetch_assoc($rArb)) {
    $vecArb[$Ia]=array();
	$vecArb[$Ia]['userId']=$rA['userId'];
	$vecArb[$Ia]['ligueId']=$rA['ligueId'];
	$Ia++;
}
$vecJou = array();
$Ij=0;
while($rJ = mysql_fetch_assoc($rJou)) {
    $vecJou[$Ij]=array();
	$vecJou[$Ij]['userId']=$rJ['proprio'];
	$vecJou[$Ij]['ligueId']=$rJ['ligueId'];
	$Ij++;
}
while($rL = mysql_fetch_assoc($rLibre)) {
	
    $vecJou[$Ij]=array();
	$vecJou[$Ij]['userId']=$rL['proprio'];
	$vecJou[$Ij]['ligueId']=$rL['ligueId'];
	$Ij++;
}


$jRet = array();
$jRet['admin']=$vecRole;
$jRet['arbitre']=$vecArb;
$jRet['joueur']=$vecJou;
$adomper= stripslashes(json_encode($jRet));
echo utf8_encode($adomper);


	//		header("HTTP/1.1 200 OK");
?>