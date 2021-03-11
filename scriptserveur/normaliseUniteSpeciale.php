<HTML> 
<HEAD> 
	<title>Statistiques du joueur</title>

</HEAD>
<body>


<?php


/////////////////////////////////////////////////////////////
//
//  D�finitions des variables
// 
////////////////////////////////////////////////////////////

require '../scriptsphp/defenvvar.php';
$tableLigue = 'Ligue';
$tableJoueur = 'TableJoueur';
$tableEvent = 'TableEvenement0';
$tableEquipe = 'TableEquipe';


////////////////////////////////////////////////////////////
//
// 	Connections � la base de donn�es
//
////////////////////////////////////////////////////////////

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	die("Can't select database");

}


$vrai=0;
$faux=0;
echo "Avant 1ere requete. \n";
$result = mysql_query("SELECT souscode, chrono FROM TableEvenement0 WHERE code=0 AND souscode!=0")
or die(mysql_error());
echo "Après 1ere requete. \n";

while($rang=mysql_fetch_array($result))
{
	$result2=mysql_query("UPDATE TableEvenement0 SET souscode={$rang['souscode']} WHERE chrono = '{$rang['chrono']}' AND code=1 ")
or die(mysql_error());

if($result2)
echo $vrai++." changements"."\n";
else
	$faux++;
}














?>


	
	YO, <?php echo $vrai." bons, ".$faux." pas bons." ?>
 	</body>

</html>	
