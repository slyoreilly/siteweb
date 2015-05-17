<?php
$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

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
$arbitreId = $_POST['arbitreId'];
$ligueId = $_POST['ligueId'];
$username = $_POST['username'];

if (!mysql_connect($db_host, $db_user, $db_pwd))
	die("Can't connect to database");

if (!mysql_select_db($database)) {
	echo "<h1>Database: {$database}</h1>";
	echo "<h1>Table: {$table}</h1>";
	die("Can't select database");
}

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

$retAbon=null;

if ($username != undefined && $username != null) {
	$retour = mysql_query("SELECT TableArbitre.*, TableUser.*
						FROM TableArbitre
						LEFT JOIN TableUser
							ON (TableArbitre.userId=TableUser.noCompte)
						 WHERE TableUser.username='{$username}'") or die(mysql_error());
} else {

	if ($arbitreId != undefined && $arbitreId != null) {
		
		if ($ligueId != undefined && $ligueId != null) {
			$retour = mysql_query("SELECT TableArbitre.*, TableUser.*
						FROM TableArbitre
						LEFT JOIN TableUser
							ON (TableArbitre.userId=TableUser.noCompte)
						WHERE TableArbitre.arbitreId='{$arbitreId}'
						") or die(mysql_error());			
						
			$retAbon = mysql_query("SELECT abonArbitreLigue.*
						FROM abonArbitreLigue
						WHERE abonArbitreLigue.ligueId='{$ligueId}'
						AND abonArbitreLigue.arbitreId='{$arbitreId}'
						AND debutAbon<=NOW()
						AND finAbon>Now()") or die(mysql_error());					
			
		}
		
		else{
		$retour = mysql_query("SELECT TableArbitre.*, TableUser.*, abonArbitreLigue.*
						FROM TableArbitre
						LEFT JOIN TableUser
							ON (TableArbitre.userId=TableUser.noCompte)
						LEFT JOIN abonArbitreLigue
							ON (TableArbitre.arbitreId=abonArbitreLigue.arbitreId)
						 WHERE TableArbitre.arbitreId='{$arbitreId}'") or die(mysql_error());
		}
	} else {

		if ($ligueId != undefined && $ligueId != null) {
			$retour = mysql_query("SELECT TableArbitre.*, TableUser.*,abonArbitreLigue.*
						FROM TableArbitre
						LEFT JOIN TableUser
							ON (TableArbitre.userId=TableUser.noCompte)
						LEFT JOIN abonArbitreLigue
							ON (TableArbitre.arbitreId=abonArbitreLigue.arbitreId)
						WHERE abonArbitreLigue.ligueId='{$ligueId}'
						AND debutAbon<=NOW()
						AND finAbon>Now()") or die(mysql_error());

		} else {
			$retour = mysql_query("SELECT TableArbitre.arbitreId, TableUser.prenom,	TableUser.nom
						FROM TableArbitre
						LEFT JOIN TableUser
							ON (TableArbitre.userId=TableUser.noCompte)
						 WHERE 1") or die(mysql_error());

		}
	}
}
$vecMatch = array();
while ($r = mysql_fetch_assoc($retour)) {
	$vecMatch[] = $r;
}
$adomper = stripslashes(json_encode($vecMatch));

$adomper = str_replace('"[', '[', $adomper);
$adomper = str_replace(']"', ']', $adomper);

$vecAbon= array();
while ($rAbon = mysql_fetch_assoc($retAbon)) {
	$vecAbon[] = $rAbon;
}
$adompAbon = stripslashes(json_encode($vecAbon));

$adompAbon = str_replace('"[', '[', $adompAbon);
$adompAbon = str_replace(']"', ']', $adompAbon);


if($retAbon==null)
{echo $adomper;}
else {
	echo "{\"profil\":".$adomper.",\"abonnements\":".$adompAbon."}";
}

//		header("HTTP/1.1 200 OK");
?>

