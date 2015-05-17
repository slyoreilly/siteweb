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

$dateDeb = $_POST['dateDeb'];
$dateFin = $_POST['dateFin'];
$type = $_POST['type'];
$code = $_POST['code'];
$ligueId = $_POST['ligueId'];
$saisonId = $_POST['saisonId'];
$nom = $_POST['nom'];


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

//////////////////////////////////
//
//	Les queries
//



	if(1==$code)  // Code 1:  Cr�er une nouvelle ligue.
{


$qVielleSaison="SELECT saisonId FROM TableSaison WHERE ligueRef='{$ligueId}' order by dernierMatch desc limit 0,1";
$resVS=mysql_query($qVielleSaison) or die(mysql_error().'Error, query failed'.$qVielleSaison);
		while ($rVS = mysql_fetch_array($resVS)) {
	$qSUp = "UPDATE TableSaison 
							SET dernierMatch='{$dateDeb}'
							WHERE saisonId='{$rVS[0]}' ";
		mysql_query($qSUp) or die(mysql_error().' Error, query failed'.$qSUp);

		}
		
	$query_saison = "INSERT INTO TableSaison (typeSaison,saisonActive, premierMatch, dernierMatch,ligueRef,nom) ".
"VALUES ($type, 1, '$dateDeb','$dateFin',$ligueId,'{$nom}' )";
mysql_query($query_saison) or die(mysql_error().'Error, query failed');


}


	
	if(10==$code)  // Code 10:  Modifie ligue existante.
	{
		echo "dans code 10".$dateDeb." / ".$dateFin." / ".$ligueId." / ".$type." / ".$saisonId;
	$query_saison = "UPDATE TableSaison 
							SET premierMatch='{$dateDeb}', dernierMatch='{$dateFin}', typeSaison='{$type}',saisonActive='1',ligueRef='{$ligueId}',nom='{$nom}'
							WHERE saisonId='{$saisonId}' ";
		mysql_query($query_saison) or die(mysql_error().' Error, query failed');
		
	
	}
echo "Fin du script modifiersaison".$query_saison;
?>
