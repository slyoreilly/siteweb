<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';




//////////////////////////////////////////////////////
//
//  	Section "Matchs"
//
//////////////////////////////////////////////////////
	
//$receveur = $_POST["receveur"];
$ligueId = $_POST["ligueId"];

$reqMes = "SELECT * 
			FROM TableMessage
			JOIN TableUser
				ON (TableMessage.expediteur=TableUser.noCompte)
			
			WHERE recepteur={$ligueId}";
$rMes = mysqli_query($conn,$reqMes)
or die(mysqli_error($conn));  

$IM=0;
$cv = Array();
$message=Array();
while ($rangMes = mysqli_fetch_array($rMes))
{
	$cv =(array) json_decode($rangMes['cleValeur']);
	if(is_array($cv))
	{
		if(strcmp($cv['contexteRecepteur'],"ligue")==0&&strcmp($cv['medium'],"babillard")==0) 
	{$message[$IM]=array();
	$message[$IM]['corps']=$rangMes['corps'];
	$message[$IM]['titre']=$rangMes['titre'];
	$message[$IM]['messageId']=$rangMes['messageId'];
	$message[$IM]['parent']=isset($cv['messageParent'])?$cv['messageParent']:NULL;
	$message[$IM]['expediteur']=$rangMes['username'];
	$message[$IM]['dateEmission']=$rangMes['dateEmission'];
	$message[$IM]['dateSuppression']=$rangMes['dateSuppression'];
			$IM++;
			}
	}
}


echo json_encode($message);
	
mysqli_close($conn);

?>
