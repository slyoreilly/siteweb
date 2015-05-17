<?php
		
//$json=json_decode("'".$matchjson."'");


$retTransJ=array();
$a=0;
while($a<count($decTransJ))
{
$traiteJ=$decTransJ[$a];	
$retTransJ[$a]=array();


	$intEquipe = $traiteJ['equipeId'];
	$intLigue = $traiteJ['ligueId'];
	$intJoueur = $traiteJ['nomJoueur'];
	$intNo = $traiteJ['noJoueur'];
	$intNomEq = $traiteJ['equipe'];
	$vieuId = $traiteJ['vieuId'];

	
$retour = mysql_query("INSERT INTO {$tableJoueur} (NomJoueur, NumeroJoueur, equipe_id_ref, Ligue, ficIdPortrait) 
VALUES ('{$intJoueur}', '{$intNo}', NULL, NULL,95)")or die(mysql_error()."insert bug");	
//	mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono, match_event_id) 
//VALUES ( 'test	Match2', 'testMatch2', 'testMatch2', 'testMatch2','testMatch2')");	
	
	$resultNouveau = mysql_query("SELECT joueur_id FROM {$tableJoueur} WHERE Nomjoueur='{$intJoueur}' AND NumeroJoueur='{$intNo}' ORDER BY joueur_id DESC")
				or die(mysql_error()."select bug");  
	
	$nId = mysql_fetch_row($resultNouveau);
	$retTransJ[$a]['vieuId']=$vieuId;
	$retTransJ[$a]['nouveauId']=$nId[0];
	
	
//$retour = mysql_query("INSERT INTO abonJoueurEquipe (joueurId, equipeId, permission, debutAbon, finAbon) 
//VALUES ('{$nId[0]}', '{$intEquipe}',30, NOW(),'2050-01-01')");	
$retour = mysql_query("INSERT INTO abonJoueurLigue (joueurId, ligueId, permission, debutAbon, finAbon) 
VALUES ('{$nId[0]}', '{$intLigue}',30, NOW(),'2020-01-01')");	

if($intEquipe!=0)
{
$retour = mysql_query("INSERT INTO abonJoueurEquipe (joueurId, equipeId, permission, debutAbon, finAbon) 
VALUES ('{$nId[0]}', '{$intEquipe}',30, NOW(),'2020-01-01')");	
}
	
//		echo $retTransJ;

$a++;
}

if($a==0)
{$retTransJ[$a]=array();}





$retTransE=array();
$b=0;
while($b<count($decTransE))
{
$traiteE=$decTransE[$b];	
$retTransE[$b]=array();


$intEquipe = $traiteE['nom'];
	$intLigue = $traiteE['ligueId'];
	$intLogo = $traiteE['logo'];
	$vieuId = $traiteE['vieuId'];

	
$retour = mysql_query("INSERT INTO TableEquipe (nom_equipe,logo,ficId, equipeActive,dernierMAJ) 
VALUES ('{$intEquipe}', '{$intLogo}', 16, 1, NOW())");	
//	mysql_query("INSERT INTO {$tableEvent} (joueur_event_ref, equipe_event_id, code, chrono, match_event_id) 
//VALUES ( 'test	Match2', 'testMatch2', 'testMatch2', 'testMatch2','testMatch2')");	
	
	$resultNouveau = mysql_query("SELECT equipe_id FROM TableEquipe WHERE nom_equipe='{$intEquipe}'  ORDER BY equipe_id DESC")
				or die(mysql_error());  
	
	$nId = mysql_fetch_row($resultNouveau);
	$retTransE[$b]['vieuId']=$vieuId;
	$retTransE[$b]['nouveauId']=$nId[0];
	
	
//$retour = mysql_query("INSERT INTO abonJoueurEquipe (joueurId, equipeId, permission, debutAbon, finAbon) 
//VALUES ('{$nId[0]}', '{$intEquipe}',30, NOW(),'2050-01-01')");	
$retour = mysql_query("INSERT INTO abonEquipeLigue (equipeId, ligueId, permission, debutAbon, finAbon) 
VALUES ('{$nId[0]}', '{$intLigue}',30, NOW(),'2020-01-01')");	


	
//		echo $retTransJ;

$b++;
}

if($b==0)
{$retTransE[$b]=array();}





//	$retTransJ[$a]['info']=json_encode($decTransJ);
//	$retTransJ[$a]['info2']=$transJ;
//$retTransJ[$a]['info3']=$t1;
//$retTransJ[$a]['info4']=$t2;

?>
