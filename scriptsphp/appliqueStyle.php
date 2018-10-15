<?php
$db_host = "localhost";
$db_user = "syncsta1_u01";
$db_pwd = "test";

$database = 'syncsta1_900';

//$fichier = $_POST['fichier'];
//echo $_POST['videos'];

$ligueId = $_POST['ligueId'];

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");


////////////////////////////
//
///		A partir d'un telId et d'un username, trouver les appareils et leurs statuts.

$retLigue = mysqli_query($conn, "SELECT Ligue.cleValeur
						FROM Ligue
						 WHERE ID_Ligue='{$ligueId}' ") or die(mysqli_error($conn));


$vecRes=mysqli_fetch_row($retLigue);
$cleValeur=$vecRes[0];

$mesParams=json_decode(stripslashes($cleValeur),true);
$chemin=$_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."style".DIRECTORY_SEPARATOR."ligues".DIRECTORY_SEPARATOR.$ligueId.DIRECTORY_SEPARATOR."perso.css";
$myfile = fopen($chemin, "w") or die("Unable to open file at ".$chemin);
$txt = ".bouton_1 {background-color:".$mesParams['style']['couleurmenu2']."}\n";
fwrite($myfile, $txt);
//$txt = "div#divTitreDoc {background-color:".$mesParams['style']['couleurmenu1']."}\n";
//fwrite($myfile, $txt);
$txt = ".cliquable,#contBabillard .simpleMessage .cliquable {color:".$mesParams['style']['couleurmenu1']."}\n";
fwrite($myfile, $txt);
$txt = "#barreNav a {color:".$mesParams['style']['couleurmenu1']."}\n".
"#barreNav a:hover {color:".$mesParams['style']['couleurmenu2']."}\n".
"#barreNav >ul >li:hover {background-color:".$mesParams['style']['couleurmenu1']."}\n".
"#barreNav >ul >li:hover {color:".$mesParams['style']['couleurmenu2']."}\n";
fwrite($myfile, $txt);
$txt = "#ulMeneurs a {color:".$mesParams['style']['couleurmenu2']."}\n".
"#ulMeneurs {background-color:".$mesParams['style']['couleurmenu1']."}\n".
"#ulMeneurs a:hover {color:".$mesParams['style']['couleurmenu1']."}\n".
"#ulMeneurs >li {background-color:".$mesParams['style']['couleurmenu1']."}\n".
"#ulMeneurs >li {color:".$mesParams['style']['couleurmenu2']."}\n".
"#ulMeneurs >li:hover {background-color:".$mesParams['style']['couleurmenu2']."}\n".
"#ulMeneurs >li:hover {color:".$mesParams['style']['couleurmenu1']."}\n".
"#ulMeneurs >li.active {background-color:".$mesParams['style']['couleurmenu2']."}\n".
"#ulMeneurs >li.active {color:".$mesParams['style']['couleurmenu1']."}\n";
fwrite($myfile, $txt);
$txt = "#barreNav ul {background-color:".$mesParams['style']['couleurmenu2']."}\n";
fwrite($myfile, $txt);
$txt = ".bg {background-image: url('".$mesParams['style']['arriereplan']."')}\n".
//.
//".tableEnveloppeMois td {background-color: ".$mesParams['style']['couleurmenu1']."}\n".
//".tableEnveloppeMois td {color: ".$mesParams['style']['couleurmenu2']."}\n";
".titreOnglet.actif {background-color: ".$mesParams['style']['couleurmenu1']. "color: ".$mesParams['style']['couleurmenu2']."}\n".
".titreOnglet.passif {background-color: ".$mesParams['style']['couleurmenu2']. "color: ".$mesParams['style']['couleurmenu1']."}\n".
".couleur1 {background-color: ".$mesParams['style']['couleurmenu1']."}\n".
"td.titreTableau {color: ".$mesParams['style']['couleurmenu1']."}\n";

fwrite($myfile, $txt);
fclose($myfile);
/*
                 $('#barreNav >ul >div').hover(function(){
                 $(this).css("background-color",lesInfos.Ligue.cleValeur.style.couleurmenu1);
                 $(this).find( 'a' ).css("color",lesInfos.Ligue.cleValeur.style.couleurmenu2);
                 }, function(){
                    $(this).css("background-color",lesInfos.Ligue.cleValeur.style.couleurmenu2);
                     $(this).find( 'a' ).css("color",lesInfos.Ligue.cleValeur.style.couleurmenu1);

*/



echo utf8_encode(json_encode($mesParams));
mysqli_close($conn);
?>
