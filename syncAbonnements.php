<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
$tableEq = 'TableEquipe';
$tableLigue = 'Ligue';
$tableMatch = 'TableMatch';
$tableJoueur = 'TableJoueur';
$tableAbon = 'AbonnementLigue';
$tableUser = 'TableUser';

$username = $_POST['username'];

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");

}
	
	
	// Retrieve all the data from the "example" table
$resultUser = mysql_query("SELECT * FROM TableUser")
or die(mysql_error());  
while($rangeeUser=mysql_fetch_array($resultUser))
{
		if(!strcmp($rangeeUser['username'],$username))
	{$userSelect =$rangeeUser['ref_id'];
	}
		// Prend le ID du user pour trouver les ligues abonnées.
}

$resultAbon = mysql_query("SELECT * FROM AbonnementLigue ORDER BY ligueid")
or die(mysql_error());  

$AbonSelect = array();
while($rangeeAbon=mysql_fetch_array($resultAbon))
	{
		if($rangeeAbon['userid']==$userSelect)
			array_push($AbonSelect, $rangeeAbon['ligueid']);
	}
	
	// On obtient un array de ligueID auquel userSelect est abonné.
	

$ligueSelect = array();
$equipeSelect = array();
$joueurSelect = array();

$noLigue =0;
$Iligue = 0;
$Iequipe = 0;
/*
	
 */header("HTTP/1.1 200 OK");
//echo " ".count($AbonSelect);
	while($Iligue<count($AbonSelect))
	{
		$resultLigue = mysql_query("SELECT * FROM {$tableLigue} ORDER BY ID_Ligue")
or die(mysql_error());  
$resultJoueur = mysql_query("SELECT * FROM {$tableJoueur}")
or die(mysql_error());  
		
	$JSONstring = "[ ";
		
		while($rangeeLigue=mysql_fetch_array($resultLigue))
		{
			
			if($rangeeLigue['ID_Ligue']==$AbonSelect[$Iligue])
			{
			$JSONstring .=  "ln".$rangeeLigue['Nom_Ligue'].", ";
			$JSONstring .=  "ln".$rangeeLigue['ID_Ligue'].", ";
		$c=0;
		$rangeeEquipe=null;
				$resultEquipe = NULL;
				$resultEquipe = mysql_query("SELECT * FROM {$tableEq}")
				or die(mysql_error());  

				while($rangeeEquipe=mysql_fetch_array($resultEquipe))
				{
				if($rangeeEquipe['ligue_equipe_ref']==$rangeeLigue['ID_Ligue'])
				{
				$JSONstring .=  "en".$rangeeEquipe['nom_equipe'].", ";
				$JSONstring .=  "ei".$rangeeEquipe['equipe_id'].", ";
				$JSONstring .=  "el".$rangeeEquipe['logo'].", ";
				$resultJoueur = mysql_query("SELECT * FROM {$tableJoueur}")
				or die(mysql_error());  
				$rangeeJoueur=0;
				while($rangeeJoueur=mysql_fetch_array($resultJoueur))
				{if($rangeeJoueur['equipe_id_ref']==$rangeeEquipe['equipe_id'])
					{
					$JSONstring .=  "ji".$rangeeJoueur['joueur_id'].", ";
					$JSONstring .=  "jn".$rangeeJoueur['NomJoueur'].", ";
					$JSONstring .=  "ju".$rangeeJoueur['NumeroJoueur'].", ";
						}
					}
		
				}}
			
			$Iligue++;
	
			}
		}
		$noLigue++;
	}
	
		$JSONstring = substr($JSONstring, 0,-1);
	$JSONstring .= "]";
	
//echo json_encode($Sommaire);
echo $JSONstring;
	

	
  
 //echo "Bidon "	
	
	
	
//$json=json_encode($ligueSelect);
//echo "[".$ligueSelect[0]."]";
//return $JSONobjet;

////////////////////  Reste à faire le mapping des ID de ligue vers des noms de ligues.	




?>
<?php  ?>
