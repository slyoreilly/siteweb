<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';


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

	
	mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");


	$aEnv = mysql_query("SELECT courriel,password
							FROM TableUser
							WHERE username='{$_POST['recepteur']}' ");
						
$tmp= mysql_fetch_row($aEnv);
$courriel=$tmp[0];	
$MdP=$tmp[1];	
$reponse="";


 $to = $courriel;
 $subject = "Mot de passe SyncStats";
 $body = "Votre mot de passe pour SyncStats est: ".$MdP;

     $headers = 'From: noreply@syncstats.com' . "\r\n" ;
     //'Reply-To: no reply' . "\r\n" ;
 
 $success =mail($to, $subject, $body,$headers);

if($success)
{ echo 1;}
else
{ echo 0;} 

?>
<?php  ?>
