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

$joueurAId = $_POST['joueurAId'];
$joueurBId = $_POST['joueurBId'];
$ligueBId = $_POST['ligueBId'];
$nomUser = $_POST['userId'];
//fusionne B dans A.


if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}
	

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

//////////////////////////////////
//
//	V�rifications
//
//////////////////////////////////

$fJA = mysql_query("SELECT proprio
								FROM TableJoueur 
								WHERE joueur_id='{$joueurAId}'")
or die(mysql_error()+' jA');  
$rJA = mysql_fetch_row($fJA);
$pJA=$rJA[0];
//echo $pJA;
$fJB = mysql_query("SELECT proprio
								FROM TableJoueur 
								WHERE joueur_id='{$joueurBId}'")
or die(mysql_error()+' jB');  
$rJB = mysql_fetch_row($fJB);
$pJB=$rJB[0];
//echo $pJB;
$fL = mysql_query("SELECT type
								FROM AbonnementLigue 
								WHERE userid='{$userId}'
								AND ligueid='{$ligueBId}'")
or die(mysql_error()+' L');  
$rL = mysql_fetch_row($fL);
if (mysql_num_rows($fL)>0)
{
$pL=-1;
}
else{$pL=$rL[0];}

////////////////////
///  Codes d'erreur
//
//	1: Les deux joueurs ont un propio défini (nécessairement différent)
//	2: L'utilisateur n'est pas admin de la ligue dans lequel il détruit un joueur.
//	3: Un des joueurs a un proprio et c'est un admin qui essaie de le détruire.
//	4: L'utilisateur n'est ni admin, ni proprio.

$erreur=0;
$codes=array();
$retour=array();
if($pJA!=0&&$pJB!=0)   /// Si les deux joueurs sont attribués.
{ $erreur=1;
array_push($codes,1);}


if($pL==-1 || $pL>19 )   // Si l'utilisateur n'a pas les privilège admin
{
	if($userId!=$pJA&&$userId!=$pJB)  // Si l'utilsiteur n'est pas proprio
		{ $erreur=1;
		array_push($codes,4);
		
		if($pJA!=0||$pJB!=0)
		{ $erreur=1;
		array_push($codes,3);}	
		
		}

}



				
				
								
		

//////////////////////////////////
//
//	Mise � jour des bases de donn�es
//
//////////////////////////////////

if($erreur==0)
{
			$nP= 0;
			if($pJA!=0)
				$nP=$pJA;
			if($pJB!=0)
				$nP=$pJB;
		$query_update = "UPDATE TableJoueur SET proprio={$nP} WHERE joueur_id={$joueurAId}";	
	mysql_query($query_update);	
	
	mysql_query("DELETE FROM `TableJoueur` WHERE joueur_id={$joueurBId}");
		
	mysql_query("UPDATE abonJoueurLigue SET joueurId={$joueurAId} WHERE joueurId={$joueurBId}");	
	mysql_query("UPDATE abonJoueurEquipe SET joueurId={$joueurAId} WHERE joueurId={$joueurBId}");	
	mysql_query("UPDATE TableEvenement0 SET joueur_event_ref='{$joueurAId}' WHERE joueur_event_ref='{$joueurBId}'");	
	$fAD=mysql_query("SELECT * 
					FROM MatchAVenir
					WHERE alignementDom
					LIKE '%\"".$joueurBId."\"%'")or die(mysql_error());
		while($rAD=mysql_fetch_array($fAD))
		{
			$aRemp = str_replace($joueurBId,$joueurAId,$rAD['alignementDom']);
			mysql_query("UPDATE MatchAVenir SET alignementDom={$aRemp} WHERE mavId={$rAD['mavId']}");
		}	
	$fAV=mysql_query("SELECT * 
					FROM MatchAVenir
					WHERE alignementVis
					LIKE '%\"".$joueurBId."\"%'")or die(mysql_error());
		while($rAV=mysql_fetch_array($fAV))
		{
			$aRemp = str_replace($joueurBId,$joueurAId,$rAV['alignementVis']);
			mysql_query("UPDATE MatchAVenir SET alignementVis={$aRemp} WHERE mavId={$rAV['mavId']}");
		}					
	echo "Fusion terminée.";
}

$retour['erreur']=$erreur;
$retour['codes']=$codes;

echo json_encode($retour);
	

?>
