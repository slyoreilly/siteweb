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

//$jDomJSON = stripslashes($_POST['jDom']);
//$jVisJSON = stripslashes($_POST['jVis']);



if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");
	}

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

$arrRecept = json_decode($_POST['recepteur']);

$expediteur = trouveIDParNomUser($_POST['expediteur']);
$titre = mysql_real_escape_string($_POST['titre']);
$corps = mysql_real_escape_string($_POST['corps']);

$IR=0;
while($IR<count($arrRecept))
{
$recepteur=$arrRecept[$IR];
	$retour = mysql_query("INSERT INTO TableMessage (expediteur, recepteur, titre, corps, dateEmission) 
VALUES ('{$expediteur}','{$recepteur}','{$titre}','{$corps}',NOW())")or die(mysql_error()." INSERT INTO");

$ret = mysql_query("SELECT *
				FROM TableMessage
					WHERE expediteur='{$expediteur}'
					AND recepteur='{$recepteur}'
					ORDER BY messageId DESC")or die(mysql_error()." SELECT");

$tmp= mysql_fetch_row($ret);
$retour=$tmp[0];	


	$retour2 = mysql_query("INSERT INTO ReceptionMessage (messageId, receveur, dateLu, extraInfo) 
VALUES ('{$retour}', '{$recepteur}',NOW(),0)")or die(mysql_error()." INSERT INTO RM");

	$aEnv = mysql_query("SELECT courriel
							FROM TableUser
							WHERE noCompte='{$recepteur}' ");
						
$tmp= mysql_fetch_row($aEnv);
$courriel=$tmp[0];	
$reponse="\n\n"."Pour répondre à ce courriel, suivez ce lien: "."http://www.syncstats.com/zadmin/messages.html?messageId=".$retour."&mode=reply";


 $to = $courriel;
 $subject = $titre;
 $body = "Exp�diteur: ".$_POST['expediteur']. "\n\n"."Message: ".$corps.$reponse;

     $headers = 'From: noreply@syncstats.com' . "\r\n" ;
     //'Reply-To: no reply' . "\r\n" ;
 
 $success =mail($to, $subject, $body,$headers);

if($success)
{ echo "Message envoyé à: ".$courriel."\n".$subject."\n".$body;}
else
{ echo "Echec d'envoie à: ".$courriel."\n".$subject."\n".$body;} 

$IR++;
}

if(count($arrRecept)==0){
	echo "Erreur, count(arrRecpet)=0), arrRecept = ".$arrRecept."orig: ".$_POST['JSON'];
	
}


?>
<?php  ?>
