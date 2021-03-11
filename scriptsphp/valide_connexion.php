<?php

require '../scriptsphp/defenvvar.php';


function onLogin($courriel, $lien) {

	$cookie = isset($_COOKIE['rememberme']) ? $_COOKIE['rememberme'] : '';
	if ($cookie) {
		return testJeton($courriel, $lien,$cookie);

	} else {
		
		return false;
	}

	//echo 3;

}

function trouveUserDeCourriel($courriel, $lien) {
	$queer = "SELECT utilisateurId 
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

function retrouveJetonParCourriel($courriel, $lien) {
	$userId = trouveUserDeCourriel($courriel, $lien);
//	echo $userId;
	$retour = mysqli_query($lien, "SELECT jeton
						FROM UtilisateurJeton
					WHERE userId='{$userId}'") or die(mysqli_error($lien));
	if (mysqli_num_rows($retour) > 0) {
		$tmp = mysqli_fetch_row($retour);
		return $tmp[0];
	} else
		return false;
}


function testJeton($courriel, $lien,$cookie) {
	$SECRET_KEY = "0HuTSq-dAhxvaGnIMfaQ6nJrIBWWMFTc";
	if ($cookie) {
		list($courriel, $token, $mac) = explode(':', $cookie);	
		if (!hash_equals(hash_hmac('sha256', $courriel . ':' . $token, $SECRET_KEY), $mac)) {
//echo 5;
			return false;
		}

		$usertoken = retrouveJetonParCourriel($courriel, $lien);
		//echo $usertoken;
		if (hash_equals($usertoken, $token)) {
			return true;

		} else {
	//		echo 6;
			return false;
		}
	}
}

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

$lien1 = mysqli_connect($db_host, $db_user, $db_pwd);
if (!$lien1)
	die("Can't connect to database");
mysqli_set_charset('utf8', $lien1);

if (!mysqli_select_db($lien1, $database)) {
	echo "<h1>Database: {$database}</h1>";
	die("Can't select database");

}

mysqli_query($lien1, "SET NAMES 'utf8'");
mysqli_query($lien1, "SET CHARACTER SET 'utf8'");

$courriel = utf8_decode($_POST["courriel"]);
if(onLogin($courriel, $lien1)){
	echo 1;
}
else{echo 0; }
?>