<?php
require '../scriptsphp/defenvvar.php';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableEvent = 'TableEvenement0';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);
$arbitreId =null;
$ligueId = null;
$username = null;
if(isset($_POST['arbitreId'])){
$arbitreId = $_POST['arbitreId'];
}if(isset($_POST['ligueId'])){
$ligueId = $_POST['ligueId'];
}if(isset($_POST['username'])){
$username = $_POST['username'];
}


$retAbon=null;

if ($username != 'undefined' && $username != null) {
	$retour = mysqli_query($conn,"SELECT TableArbitre.*, TableUser.*
						FROM TableArbitre
						LEFT JOIN TableUser
							ON (TableArbitre.userId=TableUser.noCompte)
						 WHERE TableUser.username='{$username}'") or die(mysqli_error($conn));
} else {

	if ($arbitreId != 'undefined' && $arbitreId != null) {
		
		if ($ligueId != 'undefined' && $ligueId != null) {
			$retour = mysqli_query($conn,"SELECT TableArbitre.*, TableUser.*
						FROM TableArbitre
						LEFT JOIN TableUser
							ON (TableArbitre.userId=TableUser.noCompte)
						WHERE TableArbitre.arbitreId='{$arbitreId}'
						") or die(mysqli_error($conn));			
						
			$retAbon = mysqli_query($conn,"SELECT abonArbitreLigue.*
						FROM abonArbitreLigue
						WHERE abonArbitreLigue.ligueId='{$ligueId}'
						AND abonArbitreLigue.arbitreId='{$arbitreId}'
						AND debutAbon<=NOW()
						AND finAbon>Now()") or die(mysqli_error($conn));					
			
		}
		
		else{
		$retour = mysqli_query($conn,"SELECT TableArbitre.*, TableUser.*, abonArbitreLigue.*
						FROM TableArbitre
						LEFT JOIN TableUser
							ON (TableArbitre.userId=TableUser.noCompte)
						LEFT JOIN abonArbitreLigue
							ON (TableArbitre.arbitreId=abonArbitreLigue.arbitreId)
						 WHERE TableArbitre.arbitreId='{$arbitreId}'") or die(mysqli_error($conn));
		}
	} else {

		if ($ligueId != 'undefined' && $ligueId != null) {
			$retour = mysqli_query($conn,"SELECT TableArbitre.*, TableUser.*,abonArbitreLigue.*
						FROM TableArbitre
						LEFT JOIN TableUser
							ON (TableArbitre.userId=TableUser.noCompte)
						LEFT JOIN abonArbitreLigue
							ON (TableArbitre.arbitreId=abonArbitreLigue.arbitreId)
						WHERE abonArbitreLigue.ligueId='{$ligueId}'
						AND debutAbon<=NOW()
						AND finAbon>Now()") or die(mysqli_error($conn));

		} else {
			$retour = mysqli_query($conn,"SELECT TableArbitre.arbitreId, TableUser.prenom,	TableUser.nom
						FROM TableArbitre
						LEFT JOIN TableUser
							ON (TableArbitre.userId=TableUser.noCompte)
						 WHERE 1") or die(mysqli_error($conn));

		}
	}
}
$vecMatch = array();
while ($r = mysqli_fetch_assoc($retour)) {
	$vecMatch[] = $r;
}
$adomper = stripslashes(json_encode($vecMatch));

$adomper = str_replace('"[', '[', $adomper);
$adomper = str_replace(']"', ']', $adomper);

$vecAbon= array();
if($retAbon!=null){
	while ($rAbon = mysqli_fetch_assoc($retAbon)) {
		$vecAbon[] = $rAbon;
	}
}

$adompAbon = stripslashes(json_encode($vecAbon));

$adompAbon = str_replace('"[', '[', $adompAbon);
$adompAbon = str_replace(']"', ']', $adompAbon);


if($retAbon==null)
{echo $adomper;}
else {
	echo "{\"profil\":".$adomper.",\"abonnements\":".$adompAbon."}";
}

mysqli_close($conn);
//		header("HTTP/1.1 200 OK");
?>

