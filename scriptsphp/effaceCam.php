<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';

////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

$lien1=mysql_connect($db_host, $db_user, $db_pwd);
if (!$lien1)
    die("Can't connect to database");
mysql_set_charset('utf8',$lien1);

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}

$numDelFiles=0;
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");

$camId=$_POST["camId"];
$matchIdRef=$_POST["matchId"];

$qSelId ="SELECT match_id FROM TableMatch
			WHERE matchIdRef='{$matchIdRef}' ";
$resCamId=mysql_query($qSelId) or die(mysql_error());

$rCI = mysql_fetch_row($resCamId);

$matchId=$rCI[0];
$qSel ="SELECT nomFichier FROM Video
			WHERE (nomMatch='{$matchIdRef}' OR nomMatch='{$matchId}')
			AND camId='{$camId}' LIMIT 30";
$resCam=mysql_query($qSel) or die(mysql_error());



while($rangCam = mysql_fetch_array($resCam)){
	if(@unlink(realpath(__DIR__.'/../lookatthis/'.$rangCam[0]))){$numDelFiles++;}
else{echo __DIR__.'/../lookatthis/'.$rangCam[0];
}

		
}
$qDel="DELETE FROM Video
		 WHERE (nomMatch='{$matchIdRef}' OR nomMatch='{$matchId}')
			AND camId='{$camId}' LIMIT 30";
$resCam=mysql_query($qDel) or die(mysql_error().' damn');

echo "Number of files deleted: ".$numDelFiles;

?>
