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

//$username = $_POST['username'];
//$vielledate =$_POST['vielledate'];


//$ligueId = $_POST['ligueId'];





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
	

////    Besoin de vieilledate et ligueId
$compte=0;
	
	// Retrieve all the data from the "example" table
$resultUser = mysql_query("SELECT * FROM TableUser")
or die(mysql_error());  
while($rangeeUser=mysql_fetch_array($resultUser))
{
		if(!strcmp($rangeeUser['username'],$username))
	{$userSelect =$rangeeUser['noCompte'];
	}
		// Prend le ID du user pour trouver les ligues abonn�es.
}


	$resultLigue = mysql_query("SELECT * FROM {$tableLigue} 
									WHERE ID_Ligue={$ligueId}")
	or die(mysql_error());  
	//$resultJoueur = mysql_query("SELECT * FROM {$tableJoueur}")
	//or die(mysql_error());  
		
		/////////////////////////////////
		//
		//	Structure JSON:
		//	Ligue[i]
		//		nomLigue
		//		ligueId
		//		equipe[j]
		//			nomEquipe
		//			equipeId
		//			logo
		//			joueur[k]
		//				nomJoueur
		//				joueurId
		//				numeroJoueur
		//
		//////////////////////////////////
		$forceSync=false;
//		echo "ICI";
	$ligue=array();
		while($rangeeLigue=mysql_fetch_array($resultLigue))
			{
				$ligue['nomLigue']=$rangeeLigue['Nom_Ligue'];
				$ligue['ligueId']=$rangeeLigue['ID_Ligue'];
				$ligue['equipe']=array();
				
				$c=0;
				$rangeeEquipe=null;
				$resultEquipe = NULL;
				unset($joueurs);
				$joueurs=array();
				$resultEquipe = mysql_query("SELECT TableEquipe.*,abonEquipeLigue.* 
												FROM abonEquipeLigue
												JOIN {$tableEq} 
													ON(TableEquipe.equipe_id=abonEquipeLigue.equipeId)
													WHERE ligueId={$rangeeLigue['ID_Ligue']}
														AND permission<31
														AND debutAbon <= NOW() 
														AND finAbon >= NOW()")
				or die(mysql_error());  
				$IEq=0;
				while($rangeeEquipe=mysql_fetch_array($resultEquipe))
					{
//					if($rangeeEquipe['ligue_equipe_ref']==$rangeeLigue['ID_Ligue'])
//						{
						unset($resultJoueur);
						if($rangeeEquipe['dernierMAJ']>$vielledate)
							$forceSync=true;
	
						$ligue['equipe'][$IEq]['nomEquipe']=$rangeeEquipe['nom_equipe'];
						$ligue['equipe'][$IEq]['equipeId']=$rangeeEquipe['equipe_id'];
						$ligue['equipe'][$IEq]['logo']=$rangeeEquipe['logo'];
						$ligue['equipe'][$IEq]['cleValeur']=$rangeeEquipe['cleValeur'];
						$ligue['equipe'][$IEq]['joueur']=array();	
						
						$resultJoueur = mysql_query("SELECT TableJoueur.*, abonJoueurEquipe.* 
													FROM abonJoueurEquipe
													JOIN {$tableJoueur}
														ON (TableJoueur.joueur_id=abonJoueurEquipe.joueurId)
														WHERE equipeId={$rangeeEquipe['equipe_id'] }
														AND debutAbon <= NOW() 
														AND finAbon >= NOW()
															AND UNIX_TIMESTAMP(dernierMAJ)>'{$vielledate}'")
						or die(mysql_error());  
						$rangeeJoueur=0;
						$IJ=0;
						
						//if(mysql_num_rows($resultJoueur)==0)
						//	{unset($ligue['equipe'][$IEq]);
						//	$IEq--;}
						$compte+=mysql_num_rows($resultJoueur);
						while($rangeeJoueur=mysql_fetch_array($resultJoueur))
							{if($rangeeJoueur['equipeId']==$rangeeEquipe['equipe_id'])
								{
									$ligue['equipe'][$IEq]['joueur'][$IJ]['nomJoueur']=	$rangeeJoueur['NomJoueur'];
									$ligue['equipe'][$IEq]['joueur'][$IJ]['joueurId']=	$rangeeJoueur['joueurId'];
									$ligue['equipe'][$IEq]['joueur'][$IJ]['maj']=	$rangeeJoueur['dernierMAJ'];
									$ligue['equipe'][$IEq]['joueur'][$IJ]['position']=	$rangeeJoueur['position'];
									$ligue['equipe'][$IEq]['joueur'][$IJ]['noJoueur']=	$rangeeJoueur['NumeroJoueur'];
	//								$ligue['equipe'][$IEq]['joueur'][$IJ]=json_encode($ligue['equipe'][$IEq]['joueur'][$IJ]);
									array_push($joueurs,$rangeeJoueur['joueur_id']);
								$IJ++;
									}
							}//Fin du scan des joueurs
//					$ligue['equipe'][$IEq]['joueur']=json_encode($ligue['equipe'][$IEq]['joueur']);
//					$ligue['equipe'][$IEq]=json_encode($ligue['equipe'][$IEq]);
										
					$IEq++;
					}// Fin des scans d'�quipes
					
				
				////////////////////////////
				//	Section agents libres
						$ligue['equipe'][$IEq]['nomEquipe']=$rangeeLigue['Nom_Ligue']." - Libres ";
						$ligue['equipe'][$IEq]['equipeId']=0;
						$ligue['equipe'][$IEq]['logo']="rien";
						$ligue['equipe'][$IEq]['cleValeur']="";
						$ligue['equipe'][$IEq]['joueur']=array();
				
						$IJ=0;
				
					$resultJoueur2 = mysql_query("SELECT * 
													FROM abonJoueurLigue
														JOIN TableJoueur
															ON (TableJoueur.joueur_id=abonJoueurLigue.joueurId)
																WHERE ligueId={$rangeeLigue['ID_Ligue']}
																AND UNIX_TIMESTAMP(dernierMAJ)>'{$vielledate}'
																AND debutAbon <= NOW() 
																AND finAbon >= NOW()")
						
						or die(mysql_error());  
						$rangeeJoueur=0;
												if(mysql_num_rows($resultJoueur2)==0)
							{unset($ligue['equipe'][$IEq]);
							$IEq--;}
													$compte+=mysql_num_rows($resultJoueur2);
							
						
						while($rangeeJoueur=mysql_fetch_array($resultJoueur2))
								{
									$mJoueur=array();
									if(in_array($rangeeJoueur['joueur_id'],$joueurs)==false)
										{
										$ligue['equipe'][$IEq]['joueur'][$IJ]['nomJoueur']=	$rangeeJoueur['NomJoueur'];
									$ligue['equipe'][$IEq]['joueur'][$IJ]['joueurId']=	$rangeeJoueur['joueurId'];
									$ligue['equipe'][$IEq]['joueur'][$IJ]['maj']=	$rangeeJoueur['dernierMAJ'];
									$ligue['equipe'][$IEq]['joueur'][$IJ]['position']=	$rangeeJoueur['position'];
									$ligue['equipe'][$IEq]['joueur'][$IJ]['noJoueur']=	$rangeeJoueur['NumeroJoueur'];
	//								$ligue['equipe'][$IEq]['joueur'][$IJ]=json_encode($ligue['equipe'][$IEq]['joueur'][$IJ]);
	//								echo $ligue['equipe'][$IEq]['joueur'][$IJ];
																			$IJ++;
										array_push($joueurs,$rangeeJoueur['joueur_id']);
										}
								}//Fin du scan des joueurs
	//				$ligue['equipe'][$IEq]['joueur']=json_encode($ligue['equipe'][$IEq]['joueur']);
	//				$ligue['equipe'][$IEq]=json_encode($ligue['equipe'][$IEq]);
	//				$ligue['equipe']=json_encode($ligue['equipe']);
								
								
				////////////////////////////////////////////////////////////
					
				} // Fin de la ligue visée.
				
				if($compte==0)
				{unset($ligue);}
/*
	
 *///header("HTTP/1.1 200 OK");
//echo " ".count($AbonSelect);

?>
