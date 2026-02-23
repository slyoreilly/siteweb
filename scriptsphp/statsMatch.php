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

$matchId = $_POST['matchId'];


/////////////////////////////////////////////////////
	//
//   Trouve ID de l'equipe � partir du nom.
//
////////////////////////////////////////////////////


$rMatchEquipe= mysqli_query($conn,"SELECT eq_dom,eq_vis FROM TableMatch where matchIdRef = '$matchId'")
or die(mysqli_error($conn)."SELECT eq_dom FROM TableMatch where matchIdRef = '$matchId'");  
$rangEq=mysqli_fetch_row($rMatchEquipe);
$equipeDomID=$rangEq[0];
$equipeVisID=$rangEq[1];


///////////////////////////////
//
//
//
//

$butAlloueDom=0;
$butAlloueVis=0;



	// Retrieve all the data from la table
$resultEvent = mysqli_query($conn,"SELECT TableEvenement0.*, TableJoueur.NomJoueur, TableJoueur.NumeroJoueur
 FROM TableEvenement0 
 Left Join TableJoueur
 on (TableEvenement0.joueur_event_ref=TableJoueur.joueur_id) WHERE (equipe_event_id = '$equipeDomID' OR equipe_event_id = '$equipeVisID') AND joueur_event_ref >0 AND code <9 AND match_event_id = '$matchId' ORDER BY TableJoueur.NomJoueur")
or die(mysqli_error($conn)."SELECT * FROM TableEvenement0 WHERE (equipe_event_id = '$equipeDomID' OR equipe_event_id = '$equipeVisID') AND code <9 AND match_event_id = '$matchId'");  
$I0=0;
$evenements = array('dom' =>array(),'vis' =>array());
while($rangeeEv=mysqli_fetch_array($resultEvent))
	{
        $mLigneEvent = array();
        $mLigneEvent['event_id']= $rangeeEv['event_id'];
        $mLigneEvent['nom']= $rangeeEv['NomJoueur'];
        $mLigneEvent['numero']= $rangeeEv['NumeroJoueur'];
        $mLigneEvent['joueurId']= $rangeeEv['joueur_event_ref'];
        $mLigneEvent['code']= $rangeeEv['code'];
        $mLigneEvent['souscode']= $rangeeEv['souscode'];
        if( $rangeeEv['equipe_event_id']==$equipeDomID){
            array_push($evenements['dom'], $mLigneEvent);
        } 
        else if($rangeeEv['equipe_event_id']==$equipeVisID){
            array_push($evenements['vis'], $mLigneEvent);
        }
    }
    
    $equipes = array('dom' =>array('joueurs'=>array(),'gardiens'=>array()),'vis' =>array('joueurs'=>array(),'gardiens'=>array()));

foreach($evenements as $domvis=>$valDomVis )    
    foreach($valDomVis as $k){
//       echo json_encode($k)." | ";
//       echo " | ";
        $trouve=false;

        foreach($equipes[$domvis]['joueurs'] as &$eq){
            if($k['joueurId']==$eq['joueurId']){        
                $trouve=true;
                switch($k['code']){
                    case 0:
                    $eq['nbButs']++;
   //                 echo $k['code'];
                    break;
                    case 1:
                    $eq['nbPasses']++;
  //                  echo $k['code'];
                    break;
                    case 3:
                     if($k['souscode']==0){
                        $eq['pj']=1;
                     }
                     if($k['souscode']==5){
                        $eq['pj']=1;
                     }
 //                    echo $k['code'];
                     break;
                }
 //               echo json_encode($eq)." # ";

            }
        }
        if($trouve==false){
            $mJoueur = array();
            $mJoueur['joueurId']=$k['joueurId'];
            $mJoueur['nom']=$k['nom'];
            $mJoueur['numero']=$k['numero'];
            $mJoueur['nbButs']=0;
            $mJoueur['nbPasses']=0;
            $mJoueur['pj']=0;
            switch($k['code']){
                case 0:
                $mJoueur['nbButs']=$mJoueur['nbButs']+1;
                break;
                case 1:
                $mJoueur['nbPasses']=$mJoueur['nbPasses']+1;
                break;
                case 3:
                if($k['souscode']==0){
                    $mJoueur['pj']=1;
                }
                if($k['souscode']==5){
                     $mJoueur['pj']=1;
                    $mGardien =array();
                    $mGardien['nom']=$mJoueur['nom'];
                    $mGardien['joueurId']=$mJoueur['joueurId'];
                    array_push($equipes[$domvis]['gardiens'],$mGardien);
                }
                break;
            }



            array_push($equipes[$domvis]['joueurs'],$mJoueur);
        }
    
}


	
	
echo json_encode($equipes);
//echo json_encode($evenements);
//echo $JSONstring;
		
//mysqli_close($conn);

?>
