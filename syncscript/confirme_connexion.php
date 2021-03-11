<?php 

require '../scriptsphp/defenvvar.php';


function onConfirme($courriel, $lien) {

		$SECRET_KEY = "0HuTSq-dAhxvaGnIMfaQ6nJrIBWWMFTc";
		$token = bin2hex(random_bytes(256));

		enregistreJeton($courriel, $token, $lien);
		//echo 2;
		$cookie = $courriel . ':' . $token;
		$mac = hash_hmac('sha256', $cookie, $SECRET_KEY);
		$cookie .= ':' . $mac;

		setcookie('rememberme', $cookie);


}

function trouveUserDeCourriel($courriel, $lien) {
	$queer = "SELECT noCompte 
						FROM TableUser
					WHERE username='{$courriel}' OR courriel='{$courriel}'"  ;
	//echo $queer;
	$retour = mysqli_query($lien, $queer) or die(mysqli_error($lien));
	//echo mysqli_num_rows($retour);
	if (mysqli_num_rows($retour) > 0) {
		$tmp = mysqli_fetch_row($retour);
		//echo 7;
		return $tmp[0];
	} else {
		//echo 8;
		return false;
	}
}


function enregistreJeton($courriel, $token, $lien) {
	//echo 5;
	$userId = trouveUserDeCourriel($courriel, $lien);
	//echo $userId.'6';
	if ($userId) {

		$retour = mysqli_query($lien, "SELECT * 
						FROM UtilisateurJeton 
						WHERE userId='{$userId}'") or die(mysqli_error($lien));

		if (mysqli_num_rows($retour) > 0) {
			$tmp = mysqli_fetch_row($retour);
			$retour = mysqli_query($lien, "UPDATE UtilisateurJeton SET jeton='{$token}' WHERE userId='{$userId}'") or die(mysqli_error($lien) . " UPDATE ");

		} else {
			$queer2 = "INSERT INTO UtilisateurJeton (userId,jeton) 
VALUES ('{$userId}','{$token}')";
			//echo $queer2;
			$retour = mysqli_query($lien, $queer2) or die(mysqli_error($lien) . " INSERT INTO");

		}

	}
}




////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

$lien1=mysqli_connect($db_host, $db_user, $db_pwd);
if (!$lien1)
    die("Can't connect to database");
mysqli_set_charset('utf8',$lien1);

if (!mysqli_select_db($lien1,$database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}

mysqli_query("SET NAMES 'utf8'");
mysqli_query("SET CHARACTER SET 'utf8'");



$courriel= utf8_decode($_POST["courriel"]); 

$mdp= $_POST["mdp"]; 
$reponse=Array();
$qLog = "SELECT * FROM TableUser
	WHERE
		courriel='$courriel'
		AND
		password='$mdp'
		";
$retLog = mysqli_query($lien1, $qLog)or Die(mysqli_error($lien1));

$rangLog = Array();
if(mysqli_num_rows($retLog)>0){
$rangLog = mysqli_fetch_assoc($retLog);
$reponse['statut']=1;
$reponse['donnees']=$rangLog;
onConfirme($courriel,$lien1);
}
else{
	$reponse['statut']=0;
}


echo json_encode($reponse) ;


mysqli_close($conn);


 ?>