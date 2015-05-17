<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
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

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}
		mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

/////////////////////////////////////////////////////////////
//
//

function trouveNomJoueurParID($ID){ 

$resultJoueur = mysql_query("SELECT * FROM TableJoueur WHERE joueur_id = '{$ID}'")
or die(mysql_error());  
if($rangeeJoueur=mysql_fetch_array($resultJoueur))
		  return ($rangeeJoueur['NomJoueur']); 
else { return ("Anonyme"); }
} 



/////////////////////////////////////////////////////
	//
//   Trouve ID de l'equipe � partir du nom.
//
////////////////////////////////////////////////////

	
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


//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////
	
$uname = $_POST["userId"];
	// Retrieve all the data from la table
$userId = trouveIDParNomUser($uname);


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

$resultEvent = mysql_query("SELECT AbonnementLigue.*,Ligue.* FROM AbonnementLigue 
									JOIN Ligue
									ON (AbonnementLigue.ligueid=Ligue.ID_Ligue)
									WHERE userid='{$userId}'")or die(mysql_error());  	

while($rangeeEv=mysql_fetch_array($resultEvent))
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





$resJou = mysql_query("SELECT * FROM TableJoueur WHERE proprio='{$userId}'")
or die(mysql_error());  	
$IJ=0;
while($rangJou=mysql_fetch_array($resJou))
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

	$resLig = mysql_query("SELECT Ligue.*, abonJoueurLigue.* 
						FROM abonJoueurLigue 
						JOIN Ligue
							ON (Ligue.ID_Ligue=abonJoueurLigue.ligueId)
						WHERE joueurId='{$joueur['joueurId']}'")
	or die(mysql_error());  	
	$IAL=0;
	while($rangLig=mysql_fetch_array($resLig))
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
	$resEq = mysql_query("SELECT Ligue.*, abonJoueurEquipe.*, TableEquipe.* 
						FROM abonJoueurEquipe
						JOIN TableEquipe
							ON (abonJoueurEquipe.equipeId=TableEquipe.equipe_id) 
						JOIN abonEquipeLigue
							ON (abonJoueurEquipe.equipeId=abonEquipeLigue.ligueId)
						JOIN Ligue
							ON (abonEquipeLigue.ligueId=Ligue.ID_Ligue)
							
						WHERE joueurId='{$joueurId['joueurId']}'
						AND abonJoueurEquipe.finAbon>=abonEquipeLigue.debutAbon
						AND abonJoueurEquipe.debutAbon<=abonEquipeLigue.finAbon")
	or die(mysql_error());  	
	$IAE=0;
	
	while($rangEq=mysql_fetch_array($resEq))
	{
	$joueur['abonEquipes'][$IAL]['equipeId']=$rangEq['equipeId'];
	$joueur['abonEquipes'][$IAL]['ligueId']=$rangEq['ligueId'];
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
	
	
	if(!in_array($rangEq['ligueId'], $listeLigue))
	{
		$ILL=count($ligues);
	$ligues[$ILL]['ligueId']=	$rangEq['ligueId'];
	$ligues[$ILL]['nom']=	$rangEq['Nom_Ligue'];
	$ligues[$ILL]['lieu']=	$rangEq['Lieu'];
	$ligues[$ILL]['horaire']=	$rangEq['Horaire'];
		array_push($listeLigue,$rangEq['ligueId']);
		
	}
	
			$IAE++;
	}



$IJ++;
}


$resArb = mysql_query("SELECT TableArbitre.*, abonArbitreLigue.*, Ligue.*
						FROM TableArbitre 
						JOIN abonArbitreLigue 
							ON (TableArbitre.arbitreId=abonArbitreLigue.arbitreId) 
							JOIN Ligue
							ON (abonArbitreLigue.ligueId=Ligue.ID_Ligue)
						WHERE userId='{$userId}'")
or die(mysql_error());  	

$IA=0;
while($rangArb=mysql_fetch_array($resArb))
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


$JSONstring .="{\"abonnements\": ".json_encode($liste). ",\"joueur\": ".json_encode($joueur). ",\"arbitre\": ".json_encode($arbitre)
				.",\"ligues\": ".json_encode($ligues).",\"equipes\": ".json_encode($equipes)."}";
	
	
//echo json_encode($Sommaire);
echo $JSONstring;
	


?>
