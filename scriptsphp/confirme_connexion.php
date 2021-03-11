<?php 


require '../scriptsphp/defenvvar.php';


function onConfirme($courriel, $lien) {
//echo 2;
		$SECRET_KEY = "0HuTSq-dAhxvaGnIMfaQ6nJrIBWWMFTc";
		$token = bin2hex(openssl_random_pseudo_bytes(256));
		//Quand on sera en PHP 7, changer openssl_random_pseudo_bytes pour randomBytes
//echo 3;
//echo "courriel: ".$courriel;
		enregistreJeton($courriel, $token, $lien);
		
		$cookie = $courriel . ':' . $token;
		$mac = hash_hmac('sha256', $cookie, $SECRET_KEY);
		$cookie .= ':' . $mac;

		setcookie('rememberme', $cookie);


}

function trouveUserDeCourriel($courriel, $lien) {
	//echo $courriel." baba";
	$queer = "SELECT noCompte 
						FROM TableUser
					WHERE courriel='{$courriel}' OR username='{$courriel}'";
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
		//	echo $queer2;
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

mysqli_query($lien1,"SET NAMES 'utf8'");
mysqli_query($lien1,"SET CHARACTER SET 'utf8'");


//echo 0;

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$courriel = $request->courriel;
$mdp = $request->mdp;
//echo 1;
$reponse=Array();
$qLog = "SELECT * FROM TableUser
	WHERE
		(courriel='$courriel' OR username='$courriel')
		AND
		password='$mdp'
		";
$retLog = mysqli_query($lien1, $qLog)or die(mysqli_error($lien1));

$rangLog = Array();
if(mysqli_num_rows($retLog)>0){
$rangLog = mysqli_fetch_assoc($retLog);
$reponse['statut']=1;
$reponse['donnees']=$rangLog;
//echo "1,5";
onConfirme($courriel,$lien1);
}
else{
	$reponse['statut']=0;
}

echo json_encode($reponse) ;





 ?>