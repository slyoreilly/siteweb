<?php

//$ligueId
//$saisonId

//-----------------------------------------

if ($saisonId == "null" || $saisonId == "undefined")// Sp�cifie par la saison
{
	$saisonId = trouveSaisonActiveDeLigueId($ligueId);
}

$retCC = Array();
$retCC['ligueId'] = $ligueId;
$retCC['saisons'] = Array();
$JSONstring = "{\"ligueId\": \"" . $ligueId . "\",";

$Is = 0;

//$resultSaison = mysql_query("SELECT * FROM TableSaison WHERE saisonId = '{$saisonId}'")
$resultSaison = mysql_query("SELECT * FROM TableSaison WHERE ligueRef = '{$ligueId}'") or die(mysql_error());
while ($rangeeSaison = mysql_fetch_array($resultSaison)) {

	$premierMatch = $rangeeSaison['premierMatch'];
	$dernierMatch = $rangeeSaison['dernierMatch'];
	$typeSaison = $rangeeSaison['typeSaison'];
	$retCC['saisons'][$Is] = Array();
	$retCC['saisons'][$Is]['pm'] = $premierMatch;
	$retCC['saisons'][$Is]['dm'] = $dernierMatch;
	$retCC['saisons'][$Is]['type'] = $typeSaison;
	$retCC['saisons'][$Is]['nom'] = $rangeeSaison['nom'];
	$retCC['saisons'][$Is]['saisonId'] = $rangeeSaison['saisonId'];
	$retCC['saisons'][$Is]['structureDivision'] = json_decode($rangeeSaison['structureDivision']);

	$resultEquipe = mysql_query("SELECT TableEquipe.*,abonEquipeLigue.* FROM TableEquipe 
								JOIN abonEquipeLigue
									ON (equipeId=equipe_id) 
									WHERE ligueId = '{$ligueId}'
									AND (abonEquipeLigue.finAbon>='{$premierMatch}'
											AND abonEquipeLigue.debutAbon<='{$dernierMatch}')
											AND permission<31") or die(mysql_error());
	$equipe = array();
	$IeCC = 0;
	while ($rangeeEquipe = mysql_fetch_array($resultEquipe)) {
		$equipe[$IeCC]['id'] = $rangeeEquipe['equipe_id'];
		$equipe[$IeCC]['nom'] = $rangeeEquipe['nom_equipe'];
		$equipe[$IeCC]['ville'] = $rangeeEquipe['ville'];
		$equipe[$IeCC]['vicDom'] = 0;
		$equipe[$IeCC]['defDom'] = 0;
		$equipe[$IeCC]['nulDom'] = 0;
		$equipe[$IeCC]['vicVis'] = 0;
		$equipe[$IeCC]['defVis'] = 0;
		$equipe[$IeCC]['nulVis'] = 0;
		$equipe[$IeCC]['defPDom'] = 0;
		$equipe[$IeCC]['defPVis'] = 0;
		$equipe[$IeCC]['bp'] = 0;
		$equipe[$IeCC]['bc'] = 0;
		$equipe[$IeCC]['ficId'] = $rangeeEquipe['ficId'];
		$IeCC++;
	}

	$IeCC = 0;
	$statsEq = array();
	while ($IeCC < count($equipe)) {

		unset($resultMatch);
		unset($rangeeMatch);
		///// Recherche tous les matchs où notre equipe est impliquée.
		$resultMatch = mysql_query("
SELECT * 
FROM TableMatch 
INNER JOIN(
  SELECT match_event_id,code,souscode
    FROM TableEvenement0 
    WHERE code=11 OR code =10
    ORDER BY match_event_id, code DESC, souscode DESC
) AS s1 
	ON (TableMatch.matchIdRef=s1.match_event_id)
							WHERE (eq_dom = '{$equipe[$IeCC]['id']}' OR eq_vis = '{$equipe[$IeCC]['id']}' )
								AND statut='F'
    GROUP BY match_event_id ") or die(mysql_error());

		$cumulPoints = 0;

		while ($rangeeMatch = mysql_fetch_array($resultMatch)) {
			if ($rangeeMatch['date'] >= $premierMatch && $rangeeMatch['date'] <= $dernierMatch) {
				$monMatch = "";
				$butsPer = 0;
				$pointsPer = 0;
				$resultPer = mysql_query("
		SELECT * 
		FROM TableEvenement0
		    WHERE (code=11 OR code =10 OR code =0 OR code =4)
		    AND match_event_id = '{$rangeeMatch['match_event_id']}'
							ORDER BY chrono ASC, code DESC, souscode DESC
		 ") or die(mysql_error());
				$butsPer = 0;
				$butsMatch = 0;
				$pointsPer = 0;
				$cptPenMin = 0;
				$cptPenMaj = 0;

				while ($rangeePer = mysql_fetch_array($resultPer)) {
					//if (strcmp($monMatch, $rangeePer['match_event_id']) != 0) {
					//	$monMatch = $rangeePer['match_event_id'];

					//}
					switch($rangeePer['code']) {
						case 0 :
							if ($rangeePer['equipe_event_id'] == $equipe[$IeCC]['id']) {
								$butsPer++;
							} else {
								$butsPer--;
							}
							break;
						case 11 :
							if ($rangeePer['souscode'] > 1) {
								$butsMatch += $butsPer;
								if ($butsPer == 0) {
									$pointsPer++;
								}
								if ($butsPer > 0) {
									$pointsPer++;
									$pointsPer++;
								}
							}
							$butsPer = 0;
							break;
						case 10 :
							if ($rangeePer['souscode'] == 10) {
								$butsMatch += $butsPer;

								if ($butsPer == 0) {
									$pointsPer++;
								}
								if ($butsPer > 0) {
									$pointsPer++;
									$pointsPer++;
								}
								///// Enregistrement au cumul.
								// Victoire
								if ($butsMatch > 0) {
									$cumulPoints = $cumulPoints + 3;
								} else if ($butsMatch == 0) {
									$cumulPoints = $cumulPoints + 1;
								}

								/// Periodes
								$cumulPoints = $cumulPoints + $pointsPer;
								/// Penalités
								if ($cptPenMin <= 4) {$cumulPoints = $cumulPoints + 3;
								}
								if ($cptPenMin == 5) {$cumulPoints = $cumulPoints + 2;
								}
								if ($cptPenMin == 6) {$cumulPoints = $cumulPoints + 1;
								}
								if ($cptPenMaj <= 1) {$cumulPoints = $cumulPoints + 3;
								}
								if ($cptPenMaj == 2) {$cumulPoints = $cumulPoints + 1;
								}

							}
							break;
						case 4 :
							if ($rangeePer['souscode'] == 2 || $rangeePer['souscode'] == 3 || $rangeePer['souscode'] == 4 || $rangeePer['souscode'] == 11 || $rangeePer['souscode'] == 12 || $rangeePer['souscode'] == 13 || $rangeePer['souscode'] == 14 || $rangeePer['souscode'] == 15 || $rangeePer['souscode'] == 16 || $rangeePer['souscode'] == 17 || $rangeePer['souscode'] == 18 || $rangeePer['souscode'] == 19 || $rangeePer['souscode'] == 20 || $rangeePer['souscode'] == 21 || $rangeePer['souscode'] == 22) {
								$cptPenMin++;

							}
							if ($rangeePer['souscode'] == 33 || $rangeePer['souscode'] == 34 || $rangeePer['souscode'] == 35) {
								$cptPenMaj++;

							}

							break;
					}

				}

			}

		}
		$equipe[$IeCC]['points'] = $cumulPoints;

		unset($resultMatch);
		unset($rangeeMatch);

		$resultMatch = mysql_query("
SELECT * 
FROM TableMatch 
INNER JOIN(
  SELECT match_event_id,code,souscode
    FROM TableEvenement0 
    WHERE code=11 OR code =10
    ORDER BY match_event_id, code DESC, souscode DESC
) AS s1 
	ON (TableMatch.matchIdRef=s1.match_event_id)
							WHERE eq_dom = '{$equipe[$IeCC]['id']}' 
								AND statut='F'
    GROUP BY match_event_id ") or die(mysql_error());

		while ($rangeeMatch = mysql_fetch_array($resultMatch)) {
			if ($rangeeMatch['date'] >= $premierMatch && $rangeeMatch['date'] <= $dernierMatch) {

				if ($rangeeMatch['score_dom'] > $rangeeMatch['score_vis']) {$equipe[$IeCC]['vicDom']++;
					//	$equipe[$IeCC]['points'] += 3;
				}
				if ($rangeeMatch['score_dom'] < $rangeeMatch['score_vis']) {
					if ($rangeeMatch['souscode'] > 10) {
						$equipe[$IeCC]['defPDom']++;
					} else {
						$equipe[$IeCC]['defDom']++;
					}
				}
				if ($rangeeMatch['score_dom'] == $rangeeMatch['score_vis']) {$equipe[$IeCC]['nulDom']++;
					//	$equipe[$IeCC]['points'] += 1;
				}

				$equipe[$IeCC]['bp'] += $rangeeMatch['score_dom'];
				$equipe[$IeCC]['bc'] += $rangeeMatch['score_vis'];
			}

		}

		unset($resultMatch);
		unset($rangeeMatch);

		$resultMatch = mysql_query("
SELECT * 
FROM TableMatch 
INNER JOIN(
  SELECT match_event_id,code,souscode
    FROM TableEvenement0 
    WHERE code=11 OR code =10
    ORDER BY match_event_id, code DESC, souscode DESC
) AS s1 
	ON (TableMatch.matchIdRef=s1.match_event_id)
							WHERE eq_vis = '{$equipe[$IeCC]['id']}' 
								AND statut='F'
    GROUP BY match_event_id ") or die(mysql_error());

		while ($rangeeMatch = mysql_fetch_array($resultMatch)) {
			if ($rangeeMatch['date'] >= $premierMatch && $rangeeMatch['date'] <= $dernierMatch) {

				if ($rangeeMatch['score_dom'] < $rangeeMatch['score_vis']) {$equipe[$IeCC]['vicVis']++;
					//$equipe[$IeCC]['points'] += 3;
				}
				if ($rangeeMatch['score_dom'] > $rangeeMatch['score_vis']) {
					if ($rangeeMatch['souscode'] > 10) {
						$equipe[$IeCC]['defPVis']++;
					} else {
						$equipe[$IeCC]['defVis']++;
					}
				}
				if ($rangeeMatch['score_dom'] == $rangeeMatch['score_vis']) {$equipe[$IeCC]['nulVis']++;
					//$equipe[$IeCC]['points'] += 1;
				}
				$equipe[$IeCC]['bc'] += $rangeeMatch['score_dom'];
				$equipe[$IeCC]['bp'] += $rangeeMatch['score_vis'];
			}
		}

		$IeCC++;

	}// fin while equipes

	$I0 = 0;

	//$JSONstring .="\"equipes\": ".json_encode($equipe)."}";

	//echo json_encode($Sommaire);

	$retCC['saisons'][$Is]['equipe'] = $equipe;

	$Is++;
}// fin while saison

//		header("HTTP/1.1 200 OK");
?>
