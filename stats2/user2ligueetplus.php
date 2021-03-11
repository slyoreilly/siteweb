<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';
$tableAbon = 'AbonnementLigue';

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_set_charset($conn, "utf8");
///////////////////////////////////////////
	
$uname = $_POST["userId"];

$fResultUser = mysqli_query($conn,"SELECT noCompte 
								FROM TableUser 
								WHERE username='{$uname}'")
or die(mysqli_error($conn));  
$rU = mysqli_fetch_row($fResultUser);
if (mysqli_num_rows($fResultUser)>0)
{
	$userId = $rU[0];
}
else{$userId = -1;}

	
$liste=array();
$joueur=array();
$arbitre=array();
$listeLigue=array();
$listeEquipe=array();
$ligues=array();
$equipes=array();
$IL =0;
$ILL =0;
$ILE =0;

$resultEvent = mysqli_query($conn,"SELECT AbonnementLigue.*,Ligue.* FROM AbonnementLigue 
									JOIN Ligue
									ON (AbonnementLigue.ligueid=Ligue.ID_Ligue)
									WHERE userid='{$userId}'")or die(mysqli_error($conn));  	

while($rangeeEv=mysqli_fetch_array($resultEvent))
{
$liste[$IL]['type']=$rangeeEv['type'];
$liste[$IL]['ligueId']=$rangeeEv['ligueid'];

	if(!in_array($rangeeEv['ligueid'], $listeLigue))
	{
		$ILL=count($ligues);
	$ligues[$ILL]['ligueId']=	$rangeeEv['ligueid'];
	$ligues[$ILL]['nom']=	$rangeeEv['Nom_Ligue'];
	$ligues[$ILL]['lieu']=	$rangeeEv['Lieu'];
	$ligues[$ILL]['horaire']=	$rangeeEv['Horaire'];
		array_push($listeLigue,$rangeeEv['ligueid']);
		
	}
	$IL++;
}





$resJou = mysqli_query($conn,"SELECT * FROM TableJoueur WHERE proprio='{$userId}'")
or die(mysqli_error($conn));  	
$IJ=0;
while($rangJou=mysqli_fetch_array($resJou))
{
$joueur['nomJoueur']=$rangJou['NomJoueur'];
$joueur['numero']=$rangJou['NumeroJoueur'];
$joueur['nom']=$rangJou['nom'];
$joueur['prenom']=$rangJou['prenom'];
$joueur['joueurId']=$rangJou['joueur_id'];
$joueur['position']=$rangJou['position'];
$joueur['taille']=$rangJou['taille'];
$joueur['poids']=$rangJou['poids'];
$joueur['sexe']=$rangJou['sexe'];
$joueur['anneeNaissance']=$rangJou['anneeNaissance'];
$joueur['villeOrigine']=$rangJou['villeOrigine'];
$joueur['ficIdJoueur']=$rangJou['ficIdJoueur'];
$joueur['ficIdPortrait']=$rangJou['ficIdPortrait'];
$joueur['dernierMAJ']=$rangJou['dernierMAJ'];
$joueur['abonLigues']=array();
$joueur['abonEquipes']=array();

	$resLig = mysqli_query($conn,"SELECT Ligue.*, abonJoueurLigue.* 
						FROM abonJoueurLigue 
						JOIN Ligue
							ON (Ligue.ID_Ligue=abonJoueurLigue.ligueId)
						WHERE joueurId='{$joueur['joueurId']}'")
	or die(mysqli_error($conn));  	
	$IAL=0;
	while($rangLig=mysqli_fetch_array($resLig))
	{
	$joueur['abonLigues'][$IAL]['ligueId']=$rangLig['ligueId'];
	$joueur['abonLigues'][$IAL]['debutAbon']=$rangLig['debutAbon'];
	$joueur['abonLigues'][$IAL]['finAbon']=$rangLig['finAbon'];
	
	if(!in_array($rangLig['ligueId'], $listeLigue))
	{
		$ILL=count($ligues);
	$ligues[$ILL]['ligueId']=	$rangLig['ligueId'];
	$ligues[$ILL]['nom']=	$rangLig['Nom_Ligue'];
	$ligues[$ILL]['lieu']=	$rangLig['Lieu'];
	$ligues[$ILL]['horaire']=	$rangLig['Horaire'];
		array_push($listeLigue,$rangLig['ligueId']);
		
	}
	
	$IAL++;		
	}
// A ajouter si bogue:	, abonEquipeLigue.*
	$resEq = mysqli_query($conn,"SELECT Ligue.*, abonJoueurEquipe.*, TableEquipe.* 
						FROM abonJoueurEquipe
						JOIN TableEquipe
							ON (abonJoueurEquipe.equipeId=TableEquipe.equipe_id) 
						JOIN abonEquipeLigue
							ON (abonJoueurEquipe.equipeId=abonEquipeLigue.ligueId)
						JOIN Ligue
							ON (abonEquipeLigue.ligueId=Ligue.ID_Ligue)
							
						WHERE joueurId='{$joueur['joueurId']}'
						AND abonJoueurEquipe.finAbon>=abonEquipeLigue.debutAbon
						AND abonJoueurEquipe.debutAbon<=abonEquipeLigue.finAbon")
	or die(mysqli_error($conn));  	
	$IAE=0;
	
	while($rangEq=mysqli_fetch_array($resEq))
	{
	$joueur['abonEquipes'][$IAL]['equipeId']=$rangEq['equipeId'];
	$joueur['abonEquipes'][$IAL]['ligueId']=$rangEq['ID_Ligue'];
	$joueur['abonEquipes'][$IAL]['debutAbon']=$rangEq['debutAbon'];
	$joueur['abonEquipes'][$IAL]['finAbon']=$rangEq['finAbon'];


	if(!in_array($rangEq['equipeId'], $listeEquipe))
	{
		$ILE=count($equipes);
	$equipes[$ILE]['equipeId']=	$rangEq['equipeId'];
	$equipes[$ILE]['nom']=	$rangEq['nom_equipe'];
	$equipes[$ILE]['logo']=	$rangEq['logo'];
	$equipes[$ILE]['ficId']=	$rangEq['ficId'];
		array_push($listeEquipe,$rangEq['equipeId']);
		
	}
	
	
	if(!in_array($rangEq['ID_Ligue'], $listeLigue))
	{
		$ILL=count($ligues);
	$ligues[$ILL]['ligueId']=	$rangEq['ID_Ligue'];
	$ligues[$ILL]['nom']=	$rangEq['Nom_Ligue'];
	$ligues[$ILL]['lieu']=	$rangEq['Lieu'];
	$ligues[$ILL]['horaire']=	$rangEq['Horaire'];
		array_push($listeLigue,$rangEq['ID_Ligue']);
		
	}
	
			$IAE++;
	}



$IJ++;
}


$resArb = mysqli_query($conn,"SELECT TableArbitre.*, abonArbitreLigue.*, Ligue.*
						FROM TableArbitre 
						JOIN abonArbitreLigue 
							ON (TableArbitre.arbitreId=abonArbitreLigue.arbitreId) 
							JOIN Ligue
							ON (abonArbitreLigue.ligueId=Ligue.ID_Ligue)
						WHERE userId='{$userId}'")
or die(mysqli_error($conn));  	

$IA=0;
while($rangArb=mysqli_fetch_array($resArb))
{
	if($IA==0)
	{$arbitre['arbitreId']=$rangArb['arbitreId'];
	$arbitre['ligues']=array();}
	$arbitre['ligues'][$IA]=$rangArb['ligueId'];
		if(!in_array($rangArb['ligueId'], $listeLigue))
	{
		$ILL=count($ligues);
	$ligues[$ILL]['ligueId']=	$rangArb['ligueId'];
	$ligues[$ILL]['nom']=	$rangArb['Nom_Ligue'];
	$ligues[$ILL]['lieu']=	$rangArb['Lieu'];
	$ligues[$ILL]['horaire']=	$rangArb['Horaire'];
		array_push($listeLigue,$rangArb['ligueId']);
		
	}
	
	$IA++;
}


$JSONstring ="{\"abonnements\": ".json_encode($liste). ",\"joueur\": ".json_encode($joueur). ",\"arbitre\": ".json_encode($arbitre)
				.",\"ligues\": ".json_encode($ligues).",\"equipes\": ".json_encode($equipes)."}";
	
	
//echo json_encode($Sommaire);
echo $JSONstring;
	
mysqli_close($conn);

?>
