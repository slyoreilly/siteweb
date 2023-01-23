<?php
require '../scriptsphp/defenvvar.php';
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

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
	

//////////////////////////////////
//
//	V�rifications
//
//////////////////////////////////

$fResultUser = mysqli_query($conn, "SELECT noCompte 
								FROM TableUser 
								WHERE username='{$nomUser}'")
or die(mysqli_error($conn));  
$rU = mysqli_fetch_row($fResultUser);
if (mysqli_num_rows($fResultUser)>0)
{
	$userId = $rU[0];
}




$fJA = mysqli_query($conn,"SELECT proprio
								FROM TableJoueur 
								WHERE joueur_id='{$joueurAId}'")
or die(mysqli_error($conn)+' jA');  
$rJA = mysqli_fetch_row($fJA);
$pJA=$rJA[0];
//echo $pJA;
$fJB = mysqli_query($conn,"SELECT proprio
								FROM TableJoueur 
								WHERE joueur_id='{$joueurBId}'")
or die(mysqli_error($conn)+' jB');  
$rJB = mysqli_fetch_row($fJB);
$pJB=$rJB[0];
//echo $pJB;
$fL = mysqli_query($conn,"SELECT type
								FROM AbonnementLigue 
								WHERE userid='{$userId}'
								AND ligueid='{$ligueBId}'")
or die(mysqli_error($conn)+' L');  
$rL = mysqli_fetch_row($fL);
if (mysqli_num_rows($fL)==0)
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
	mysqli_query($conn,$query_update);	
	
	mysqli_query($conn,"DELETE FROM `TableJoueur` WHERE joueur_id={$joueurBId}");

		
	mysqli_query($conn,"UPDATE abonJoueurLigue SET joueurId={$joueurAId} WHERE joueurId={$joueurBId}");	


	$breakAndRetry=false;

	do{
		$qSelAbonJLA = "SELECT *
		FROM abonJoueurLigue
		WHERE joueurId={$joueurAId} ORDER BY ligueId, debutAbon ASC";
		
		$fSAJA = mysqli_query($conn,$qSelAbonJLA);	

		while($rSAJA=mysqli_fetch_array($fSAJA)&&!$breakAndRetry)
		{

			while($rSAJAT=mysqli_fetch_array($fSAJA)&&!$breakAndRetry)
			{
				if($rSAJA['ligueId']==$rSAJAT['ligueId']&&$rSAJA['abonJouLig']!=$rSAJAT['abonJouLig']){
					if($rSAJA['finAbon']>$rSAJAT['debutAbon']){
						mysqli_query($conn,"UPDATE abonJoueurLigue SET finAbon={$rSAJAT['finAbon']} WHERE abonJouLig={$rSAJA['abonJouLig']}");
						mysqli_query($conn,"DELETE FROM `abonJoueurLigue` WHERE abonJouLig={$rSAJAT['abonJouLig']}");
					
					}
					$breakAndRetry = true;

				}

			}
			
		}	
	} while($breakAndRetry);




	mysqli_query($conn,"UPDATE abonJoueurEquipe SET joueurId={$joueurAId} WHERE joueurId={$joueurBId}");
	
	



	
	mysqli_query($conn,"UPDATE TableEvenement0 SET joueur_event_ref='{$joueurAId}' WHERE joueur_event_ref='{$joueurBId}'");	
	$fAD=mysqli_query($conn,"SELECT * 
					FROM TableMatch
					WHERE alignementDom
					LIKE '%\"".$joueurBId."\"%'")or die(mysqli_error($conn));
		while($rAD=mysqli_fetch_array($fAD))
		{
			$aRemp = str_replace($joueurBId,$joueurAId,$rAD['alignementDom']);
			mysqli_query($conn,"UPDATE TableMatch SET alignementDom={$aRemp} WHERE match_id={$rAD['match_id']}");
		}	
	$fAV=mysqli_query($conn,"SELECT * 
					FROM TableMatch
					WHERE alignementVis
					LIKE '%\"".$joueurBId."\"%'")or die(mysqli_error($conn));
		while($rAV=mysqli_fetch_array($fAV))
		{
			$aRemp = str_replace($joueurBId,$joueurAId,$rAV['alignementVis']);
			mysqli_query($conn, "UPDATE TableMatch SET alignementVis={$aRemp} WHERE match_id={$rAV['match_id']}");
		}					
	echo "Fusion terminée.";
}

$retour['erreur']=$erreur;
$retour['codes']=$codes;

echo json_encode($retour);
	

?>
