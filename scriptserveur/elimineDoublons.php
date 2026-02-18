<?php
require '../scriptsphp/defenvvar.php';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $database);
// Check connection/
if (!$conn) {
	error_log("Connection failed: " . mysqli_connect_error());
   die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn,"SET NAMES 'utf8'");
mysqli_query($conn,"SET CHARACTER SET 'utf8'");

	
	//////////////////////////////////////////////////////////////////////
//
//	    A n'utiliser que pour Tournée Alex Burrows Trois-Rivières!
//
/////////////////////////////////////////////////////////////////////


$heureLimite = $_GET['heureLimite'];
$ligueId = $_GET['ligueId'];

	$retour1 = mysqli_query($conn,"SELECT * FROM TableEvenement0 
								WHERE code = 0
								AND chrono>='{$heureLimite}' 
								order by chrono ASC
							 ");
								error_log("Nb Taches: " . mysqli_num_rows($retour1));
		$aFlusher = array();
							$buf=0;
							
		if(mysqli_num_rows($retour1)>0)			
		{while($rangee = mysqli_fetch_assoc($retour1)){
			if($buf==0){
			$buf=intval(substr($rangee['chrono'],5));
			$eqBuf=	intval($rangee['equipe_event_id']);			
			}else{
				$compet = intval(substr($rangee['chrono'],5));
				
			//$tmp =(strtotime($rangee['chrono'])-$buf)<2000;
			//$tmp2 =intval($rangee['equipe_event_id'])==$eqBuf;
			if(abs($compet-$buf)<2000){
			$comp1 =true;	
			}else{$comp1=false;
					$buf=$compet;
			
			}
			if($rangee['equipe_event_id']==$eqBuf){
			$comp2 =true;	
			}else{$comp2=false;
								$eqBuf=	intval($rangee['equipe_event_id']);			
			}
			//echo $buf ."  ".$eqBuf."  ".$rangee['chrono']."  ".$rangee['equipe_event_id']." - ".$tmp." - ".$tmp2." - ".$tmp3."   ///// \n";
			echo $compet." *".$buf." *".$rangee['equipe_event_id']." *".$eqBuf." ***** ".$comp1." ----- ".$comp2." ***** ".$tmp3.'***********';
				if($comp1==true&&$comp2){
					echo "*********GOT IT***********";
					array_push($aFlusher,$rangee['event_id']);
				}
				//else{
				//	$buf=0;
				//	$eqBuf=	0;			
										
				//}
			}
			echo "Nouvel element ";
//			$tmp =(strtotime($rangee['chrono'])-$buf)<2000;
//			$tmp2 =intval($rangee['equipe_event_id'])==$eqBuf;
//			$tmp3=((strtotime($rangee['chrono'])-$buf)<2000)&&(intval($rangee['equipe_event_id'])==$eqBuf);
//			echo $buf ."  ".$eqBuf."  ".$rangee['chrono']."  ".$rangee['equipe_event_id']." - ".$tmp." - ".$tmp2." - ".$tmp3."   ///// \n";
		}
		
		}
		
		foreach($aFlusher as $val){
			 mysqli_query($conn,"DELETE FROM TableEvenement0 
							WHERE event_id='{$val}'
							 ");
		}
		echo json_encode($aFlusher);

//mysqli_close($conn);
//include 'library/closedb.php';
	
?>
