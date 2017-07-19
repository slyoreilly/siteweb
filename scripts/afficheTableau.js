/**
 * @author Sylvain O'Reilly, 16 juin 2012
 */

/////////////////////////////////////////////////////////
//
//	Fin de afficheSommairePunitions

function afficheSommairePunitions(rJSON) {
	var lesTitres = new Array();
	lesTitres[0] = window.tl_match_Equipe;
	lesTitres[1] = window.tl_match_Joueur;
	lesTitres[2] = window.tl_match_Motif;
	lesTitres[3] = window.tl_match_Chrono;
	div1 = document.getElementById('tabPun');
	//	div1.id = "divSommairePunitions";
	//	div1.className = "centrale";
	tb = document.createElement('TBODY');
	tb.id = "bodyTable";
	//lecorps = document.getElementById('divCentrale');
	table1 = document.createElement('TABLE');
	table1.id = "tableSommairePunitions";
	table1.className = "c_tableau";
	tr1 = document.createElement('TR');
	tr1.id = "date2";
	td1 = document.createElement('TD');
	td1.className = "titreTableau";
	td1.colSpan = lesTitres.length;
	td1.innerHTML = window.tl_match_titreTabPun;
	tr2 = document.createElement('TR');
	tr2.id = "rangeeTitreSommairePunitions";
				tr2.className="rangeeTitreSommaire";
	for (var i = 0; i < lesTitres.length; i++) {
		tdi = document.createElement('TD');
		tdi.className = "rangeeTitre";
		tdi.innerHTML = lesTitres[i];
		tr2.appendChild(tdi);
	}

	tr1.appendChild(td1);
	tb.appendChild(tr1);
	tb.appendChild(tr2);
	table1.appendChild(tb);
	div1.appendChild(table1);
	//	lecorps.appendChild(div1);

	/////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////

	try
	{

		maPer = new Array();
		//rJSON.periodes.shift();

		if (rJSON.periodes.length > 0)
			maPer = rJSON.periodes[0];
		else {

			maPer.chrono = rJSON.buts[0].chrono;
		}
		var Iper = 0;
		for (var J = 0; J < rJSON.punitions.length; J = J + 1) {

			try {
				if (rJSON.periodes.length > 0) {
					if (parseInt(rJSON.periodes[Iper].chrono) < parseInt(rJSON.punitions[J].chrono)) {
						//					maPer = rJSON.periodes.shift();
						maPer = rJSON.periodes[Iper];
						Iper++;
//						rangeePer = $('#rangeeTitreSommairePunitions').parent().append($('<TR></TR>').attr('id', 'tr_' + J));
						rangeePer = document.createElement('TR');
						rangeePer.setAttribute('id','tr_' + J);
						amettrePer = document.getElementById('rangeeTitreSommairePunitions');
						amettrePer.parentNode.appendChild(rangeePer);
						cPer = document.createElement('TD');
						cPer.setAttribute("colSpan", "4")
						cPer.setAttribute("class", "rangeeTitre")
						tPer = document.createTextNode(window.tl_match_periode + " " + maPer.numero);
						cPer.appendChild(tPer);
						rangeePer.appendChild(cPer);
					}
				}
			} catch(err) {
				alert(err);
			}

			//	if(!statsJSON.joueurs[J].nom.isNull&&statsJSON.joueurs[J].nom!='Anonyme')
//rangee = $('#rangeeTitreSommairePunitions').parent().append($('<TR></TR>').attr('id', 'tr_' + J));
									rangee = document.createElement('TR');
						rangee.setAttribute('id','tr_' + J);
			amettre = document.getElementById('rangeeTitreSommairePunitions');
			amettre.parentNode.appendChild(rangee);
			minutes = Math.floor((parseInt(rJSON.punitions[J].chrono) - parseInt(maPer.chrono)) / 60000);
			//	alert("minutes"+minutes+" ");
			dizSecondes = Math.floor(((parseInt(rJSON.punitions[J].chrono) - parseInt(maPer.chrono)) - minutes * 60000) / 10000);
			secondes = Math.floor(((parseInt(rJSON.punitions[J].chrono) - parseInt(maPer.chrono)) - minutes * 60000 - dizSecondes * 10000) / 1000);
			//	alert("secondes"+secondes+" ");
			strChrono = minutes + ":" + dizSecondes + secondes;

						mSrc = (rJSON.eqDom == rJSON.buts[J].equipe) ? '/admin/afficheImage.php?ficId=' + infoMatch.equipeFicIdDom : '/admin/afficheImage.php?ficId=' + infoMatch.equipeFicIdVis;

			var lesCols = new Array();
			try {
				lesCols[0] = '<img src=\"' + mSrc + '\" alt=\"' + rJSON.punitions[J].equipe + '\" width=\"32\" height=\"32\"\\/\>';
			} catch(err) {
				lesCols[0] = rJSON.punitions[J].equipe;
			}

			lesCols[1] = rJSON.punitions[J].joueur;
			lesCols[2] = rJSON.punitions[J].motif;
			lesCols[3] = strChrono;

			var lesLiens = new Array();
			lesLiens[0] = null;
			lesLiens[1] = '/zstats/statsjoueur.html?joueurId=' + rJSON.punitions[J].joueurId;
			lesLiens[2] = null;
			lesLiens[3] = null;

			for ( K = 0; K < lesCols.length; K++) {
				cellule = document.createElement('TD');
				if (lesCols[K] != undefined) {
					if (lesLiens[K] != null) {
						lien = document.createElement('A');
						lien.innerHTML = lesCols[K];
						lien.href = lesLiens[K];
						cellule.appendChild(lien);
					} else {
						ptexte = document.createElement('P');
						ptexte.innerHTML = lesCols[K];
						//					texte = document.createTextNode(lesCols[K]);
						cellule.appendChild(ptexte);
					}
				}
											if (J % 2 == 0) {
								rangee.className += ' lignePaire';
							} else {
								rangee.className += ' ligneImpaire';
							}
							//J % 2 == 0 ? rangee.className = 'lignePaire' : rangee.className = 'ligneImpaire';
							//$('#tr_' + J).append($(cellule));

//				J % 2 == 0 ? cellule.className = 'lignePaire' : cellule.className = 'ligneImpaire';
				rangee.appendChild(cellule);
			}

		}// fin du for des punitions

		if (rJSON.punitions.length == 0) {
			rangee = document.createElement('TR');
			amettre = document.getElementById('rangeeTitreSommairePunitions');
			amettre.parentNode.appendChild(rangee);
			cellule = document.createElement('TD');
			cellule.colSpan = amettre.childNodes.length;
			rangee.appendChild(cellule);
			cellule.innerHTML = "Aucune punition";
			cellule.style.textAlign = "center";
		}

	} catch(err) {
alert(err);
	}
	//////////////////////////////////////////////////////////

	//////////////////////////////////////////////////////////

}

/////////////////////////////////////////////////////////
//
// Fin sommairePunitions

function afficheResultats(matchsDsSaison) {
	try {

		//alert(uneString);

		tb = document.createElement('TBODY');
		tb.id = "bodyTable";
		div1 = document.getElementById('divCentrale');
		table1 = document.createElement('TABLE');
		table1.id = "tableResultats";
		table1.className = "c_tableau";
		tr1 = document.createElement('TR');
		tr1.id = "date2";
		td1 = document.createElement('TD');
		td1.className = "titreTableau";
		td1.colSpan = "6";
		td1.innerHTML = window.tl_resultats_matchDispo;
		tr2 = document.createElement('TR');
		tr2.id = "rangeeTitreMatchs";
		var titreClassement = new Array();
		titreClassement[0] = window.tl_resultats_Date;
		titreClassement[1] = window.tl_resultats_Domicile;
		//	titreClassement[2] = window.tl_resultats_ButsDom;
		//	titreClassement[3] =  window.tl_resultats_ButsVis;
		titreClassement[2] = window.tl_resultats_Visiteur;
		titreClassement[3] = "Actions";

		for ( i = 0; i < titreClassement.length; i++) {
			tdi = document.createElement('TD');
			tdi.className = "rangeeTitre";
			tdi.innerHTML = titreClassement[i];
			tr2.appendChild(tdi);
			if (i == 1 || i == 2) {
				tdi.colSpan = 2;
				tdi.style.textAlign = "center";
			}
		}

		tr1.appendChild(td1);
		tb.appendChild(tr1);
		tb.appendChild(tr2);
		table1.appendChild(tb);
		div1.appendChild(table1);
		/*
		 rJSON.matchs.sort(function(a, b) {
		 var d1 = new Date(a.date);
		 var d2 = new Date(b.date);
		 return (d2 - d1)
		 });*/
		for ( J = 0; J <= matchsDsSaison.length; J = J + 1) {
			rangee = document.createElement('TR');
			amettre = document.getElementById('rangeeTitreMatchs');
			amettre.parentNode.appendChild(rangee);

			/////////////////////////////////////////////
			//			var strInfo = getInfoMatch(rJSON.matchs[J].matchID);
			//			alert(strInfo);
			//			var infoMatch = eval('(' + strInfo + ')');

			//	logoEquipe = document.getElementById('logoEquipeDom');
			//	logoEquipe.setAttribute('src','admin/afficheImage.php?ficId='+infoMatch.equipeFicIdDom);

			//	logoEquipe2 = document.getElementById('logoEquipeVis');
			//	logoEquipe2.setAttribute('src','admin/afficheImage.php?ficId='+infoMatch.equipeFicIdVis);

			////////////////////////////////////////////

			celluleNom = document.createElement('TD');
			texteNom = document.createTextNode(matchsDsSaison[J].date.split(" ")[0]);
			celluleNom.appendChild(texteNom);
			rangee.appendChild(celluleNom);

			cellEqDom = document.createElement('TD')
			cellEqDom.style.textAlign = "right";
			tmpImg = document.createElement('IMG');
			tmpImg.src = "/admin/afficheImage.php?ficId=" + matchsDsSaison[J].ficIdDom;
			tmpImg.alt = matchsDsSaison[J].eqDom;
			tmpImg.style.width = "32px";
			tmpImg.style.height = "32px";
			cellEqDom.appendChild(tmpImg);
			rangee.appendChild(cellEqDom);

			celluleScoreDom = document.createElement('TD');
			celluleScoreDom.style.textAlign = "center";
			texteScoreDom = document.createTextNode(matchsDsSaison[J].equipeScoreDom);
			celluleScoreDom.style.fontSize = "18px";
			celluleScoreDom.appendChild(texteScoreDom);
			rangee.appendChild(celluleScoreDom);

			celluleScoreVis = document.createElement('TD');
			celluleScoreVis.style.textAlign = "center";
			texteScoreVis = document.createTextNode(matchsDsSaison[J].equipeScoreVis);
			celluleScoreVis.style.fontSize = "18px";
			celluleScoreVis.appendChild(texteScoreVis);
			rangee.appendChild(celluleScoreVis);

			cellEqVis = document.createElement('TD')
			cellEqVis.style.textAlign = "left";
			tmpImg = document.createElement('IMG');
			tmpImg.src = "/admin/afficheImage.php?ficId=" + matchsDsSaison[J].ficIdVis;
			tmpImg.alt = matchsDsSaison[J].eqVis;
			tmpImg.style.width = "32px";
			tmpImg.style.height = "32px";
			cellEqVis.appendChild(tmpImg);
			rangee.appendChild(cellEqVis);

			celluleEq = document.createElement('TD');
			lien = document.createElement('A');
			lien.innerHTML = window.tl_resultats_Sommaire;
			lien.setAttribute('href', 'match.html?match=' + escape(matchsDsSaison[J].matchID) + '&m=' + window.m);

			tiret = document.createTextNode(" | ");

			lien2 = document.createElement('A');
			lien2.innerHTML = "Rapport";
			lien2.setAttribute('href', 'rapportmatch.html?match=' + escape(matchsDsSaison[J].matchID));

			if (J % 2 == 0)
				rangee.setAttribute('class', 'lignePaire');
			else
				rangee.setAttribute('class', 'ligneImpaire');

			celluleEq.appendChild(lien);
			celluleEq.appendChild(tiret);
			celluleEq.appendChild(lien2);
			rangee.appendChild(celluleEq);

		}

		//	document.body.rangee.cellule.innerHTML ='yo';
	} catch(err) {
	}

}

function afficheClassement(ligueId, saisonId) {
	var string2 = getClassementJSON(ligueId, saisonId);
	//	if(verifiePermission()<9)
	//		alert(string2);
	var cJSON = eval('(' + string2 + ')');

	var titreClassement = new Array();
	titreClassement[0] = window.tl_match_Equipe;
	titreClassement[1] = window.tl_class_Vic;
	titreClassement[2] = window.tl_class_Def;
	titreClassement[3] = window.tl_class_Nul;
	titreClassement[4] = window.tl_class_Points;
	titreClassement[5] = window.tl_class_BP;
	titreClassement[6] = window.tl_class_BC;
	div1 = document.getElementById('divCentrale');
	table1 = document.createElement('TABLE');
	table1.id = "tableClassement";
	table1.className = "c_tableau";
	tb = document.createElement('TBODY');
	tb.id = "bodyTable";
	tr1 = document.createElement('TR');
	tr1.id = "date2";
	td1 = document.createElement('TD');
	td1.className = "titreTableau";
	td1.colSpan = titreClassement.length;
	td1.innerHTML = window.tl_class_Classement;
	tr2 = document.createElement('TR');
	tr2.id = "rangeeTitreClassement";

	for ( i = 0; i < titreClassement.length; i++) {
		tdi = document.createElement('TD');
		tdi.className = "rangeeTitre";
		tdi.innerHTML = titreClassement[i];
		tr2.appendChild(tdi);
	}

	tr1.appendChild(td1);
	tb.appendChild(tr1);
	tb.appendChild(tr2);
	table1.appendChild(tb);
	div1.appendChild(table1);

	/////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////

	try
	{
		cJSON.equipes.sort(function(a, b) {
			return (parseInt(2 * b.vicDom) + 2 * parseInt(b.vicVis) + parseInt(b.nulDom) + parseInt(b.nulVis)) - (parseInt(2 * a.vicDom) + 2 * parseInt(a.vicVis) + parseInt(a.nulDom) + parseInt(a.nulVis));
		});
		for ( J = 0; J < cJSON.equipes.length; J++) {
			//	if(!statsJSON.joueurs[J].nom.isNull&&statsJSON.joueurs[J].nom!='Anonyme')
			rangee = document.createElement('TR');
			amettre = document.getElementById('rangeeTitreClassement');
			amettre.parentNode.appendChild(rangee);

			var lesCols = new Array();
			lesCols[0] = cJSON.equipes[J].nom;
			lesCols[1] = parseInt(cJSON.equipes[J].vicDom) + parseInt(cJSON.equipes[J].vicVis);
			lesCols[2] = parseInt(cJSON.equipes[J].defDom) + parseInt(cJSON.equipes[J].defVis);
			lesCols[3] = parseInt(cJSON.equipes[J].nulDom) + parseInt(cJSON.equipes[J].nulVis);
			lesCols[4] = parseInt(2 * cJSON.equipes[J].vicDom) + 2 * parseInt(cJSON.equipes[J].vicVis) + parseInt(cJSON.equipes[J].nulDom) + parseInt(cJSON.equipes[J].nulVis);
			lesCols[5] = cJSON.equipes[J].bp;
			lesCols[6] = cJSON.equipes[J].bc;

			var lesLiens = new Array();
			lesLiens[0] = '/zstats/statsequipe.html?equipeId=' + cJSON.equipes[J].id + '&m=' + window.m;
			lesLiens[1] = null;
			lesLiens[2] = null;
			lesLiens[3] = null;
			lesLiens[4] = null;
			lesLiens[5] = null;
			lesLiens[6] = null;

			for ( K = 0; K < lesCols.length; K++) {
				cellule = document.createElement('TD');
				if (lesLiens[K] != null) {
					lien = document.createElement('A');
					lien.innerHTML = lesCols[K];
					lien.href = lesLiens[K];
					cellule.appendChild(lien);
				} else {
					texte = document.createTextNode(lesCols[K]);
					cellule.appendChild(texte);
				}
				J % 2 == 0 ? cellule.className = 'lignePaire' : cellule.className = 'ligneImpaire';
				rangee.appendChild(cellule);
			}

		}

		//	document.body.rangee.cellule.innerHTML ='yo';
	} catch(err) {
	}
	//////////////////////////////////////////////////////////

	//////////////////////////////////////////////////////////

}

/*
function afficheMeneurs(statsJSON, gJSON) {
div1 = document.createElement('DIV');
div1.id = "divClassement";
div1.className = "centrale";
lecorps = document.getElementById('mbody');
table1 = document.createElement('TABLE');
table1.id = "tableEquipe";
table1.className = "c_tableau";
tr1 = document.createElement('TR');
tr1.id = "date2";
td1 = document.createElement('TD');
td1.className = "titreTableau";
td1.colSpan = "8";
td1.innerHTML = "Marqueurs";
tr2 = document.createElement('TR');
tr2.id = "rangeeTitreEquipe";
var titreClassement = new Array();
titreClassement[0] = "Rang";
titreClassement[1] = "Numéro";
titreClassement[2] = "Joueur";
titreClassement[3] = "Équipe";
titreClassement[4] = "Parties jouées";
titreClassement[5] = "Buts";
titreClassement[6] = "Passes";
titreClassement[7] = "Points";

for ( i = 0; i < titreClassement.length; i++) {
tdi = document.createElement('TD');
tdi.className = "rangeeTitre";
tdi.innerHTML = titreClassement[i];
tr2.appendChild(tdi);
}

tr1.appendChild(td1);
table1.appendChild(tr1);
table1.appendChild(tr2);
div1.appendChild(table1);
lecorps.appendChild(div1);

////////////  Gardiens

div1 = document.createElement('DIV');
div1.id = "divClassement";
div1.className = "centrale";
lecorps = document.getElementById('mbody');
table1 = document.createElement('TABLE');
table1.id = "tableauGardiens";
table1.className = "c_tableau";
tr1 = document.createElement('TR');
tr1.id = "date2";
td1 = document.createElement('TD');
td1.className = "titreTableau";
td1.colSpan = "8";
td1.innerHTML = "Gardiens";
tr2 = document.createElement('TR');
tr2.id = "rangeeTitreGardiens";
var titreClassement = new Array();
titreClassement[0] = "Rang";
titreClassement[1] = "Gardiens";
titreClassement[2] = "Parties Jouées";
titreClassement[3] = "Victoires";
titreClassement[4] = "Défaites";
titreClassement[5] = "Nulles";
titreClassement[6] = "Buts alloués";
titreClassement[7] = "Moy. buts alloués";

for ( i = 0; i < titreClassement.length; i++) {
tdi = document.createElement('TD');
tdi.className = "rangeeTitre";
tdi.innerHTML = titreClassement[i];
tr2.appendChild(tdi);
}

tr1.appendChild(td1);
table1.appendChild(tr1);
table1.appendChild(tr2);
div1.appendChild(table1);
lecorps.appendChild(div1);

try {
//statsJSON.joueurs.triPar("nbButs",1,1);
statsJSON.joueurs.sort(function(a, b) {
A = (parseInt(b.nbButs) + parseInt(b.nbPasses)) - (parseInt(a.nbButs) + parseInt(a.nbPasses));
B = A ? A : (parseInt(b.nbButs) - parseInt(a.nbButs));
return B
});
for ( J = 0; J < statsJSON.joueurs.length; J = J + 1) {
//	if(!statsJSON.joueurs[J].nom.isNull&&statsJSON.joueurs[J].nom!='Anonyme')
if (!statsJSON.joueurs[J].nom.isNull) {
rangee = document.createElement('TR');
amettre = document.getElementById('rangeeTitreEquipe');
amettre.parentNode.appendChild(rangee);

var lesCols = new Array();
lesCols[0] = parseInt(J + 1);
lesCols[1] = statsJSON.joueurs[J].nomEquipe == null ? " " : statsJSON.joueurs[J].numero;
lesCols[2] = statsJSON.joueurs[J].nom;
lesCols[3] = statsJSON.joueurs[J].nomEquipe == null ? " " : statsJSON.joueurs[J].nomEquipe;
lesCols[4] = statsJSON.joueurs[J].pj;
lesCols[5] = statsJSON.joueurs[J].nbButs;
lesCols[6] = statsJSON.joueurs[J].nbPasses;
lesCols[7] = parseInt(statsJSON.joueurs[J].nbButs) + parseInt(statsJSON.joueurs[J].nbPasses);

for ( K = 0; K < lesCols.length; K++) {
cellule = document.createElement('TD');
texte = document.createTextNode(lesCols[K]);
cellule.appendChild(texte);
J % 2 == 0 ? cellule.setAttribute('class', 'lignePaire') : cellule.setAttribute('class', 'ligneImpaire');
rangee.appendChild(cellule);
}
}

}

//	document.body.rangee.cellule.innerHTML ='yo';
} catch(err) {
}

//////////////////////////////////////////////////////
//	Tableau gardien
//////////////////////////////////////////////////////

try
{

gJSON.gardiens.sort(function(a, b) {
buts1 = parseInt(a.nbButs);
match1 = parseInt(a.victoires) + parseInt(a.defaites) + parseInt(a.nulles);
m1 = buts1 / match1;
moy1 = Math.round(m1 * 1000) / 1000;
buts2 = parseInt(b.nbButs);
match2 = parseInt(b.victoires) + parseInt(b.defaites) + parseInt(b.nulles);
m2 = buts2 / match2;
moy2 = Math.round(m2 * 1000) / 1000;
A = moy1 - moy2;
B = A ? A : (parseInt(b.victoires) - parseInt(a.victoires));
return B
});
for ( J = 0; J < gJSON.gardiens.length; J = J + 1) {
//	if(!statsJSON.joueurs[J].nom.isNull&&statsJSON.joueurs[J].nom!='Anonyme')
if (gJSON.gardiens[J].nom != null) {
//		alert("dans le if:" + gJSON.gardiens[J].nom);
rangee = document.createElement('TR');
amettre = document.getElementById('rangeeTitreGardiens');
amettre.parentNode.appendChild(rangee);

var buts = parseInt(gJSON.gardiens[J].nbButs);
var match = parseInt(gJSON.gardiens[J].victoires) + parseInt(gJSON.gardiens[J].defaites) + parseInt(gJSON.gardiens[J].nulles);

var moy = buts / match;
celluleMoy = document.createElement('TD');

var lesCols = new Array();
lesCols[0] = parseInt(J + 1);
lesCols[1] = gJSON.gardiens[J].nom;
lesCols[2] = parseInt(gJSON.gardiens[J].victoires) + parseInt(gJSON.gardiens[J].defaites) + parseInt(gJSON.gardiens[J].nulles);
lesCols[3] = gJSON.gardiens[J].victoires;
lesCols[4] = gJSON.gardiens[J].defaites;
lesCols[5] = gJSON.gardiens[J].nulles;
lesCols[6] = gJSON.gardiens[J].nbButs;
lesCols[7] = Math.round(moy * 1000) / 1000;

for ( K = 0; K < lesCols.length; K++) {
cellule = document.createElement('TD');
texte = document.createTextNode(lesCols[K]);
cellule.appendChild(texte);
J % 2 == 0 ? cellule.setAttribute('class', 'lignePaire') : cellule.setAttribute('class', 'ligneImpaire');
rangee.appendChild(cellule);
}

}

}

//	document.body.rangee.cellule.innerHTML ='yo';
} catch(err) {
}

}
*/
/////////////////////////////////////////////////////////////////////////////////////

function afficheStatsDom(statsJoueurs, code) {

	strParent = "divCentrale";
	switch(code) {
		case 1:
			strParent = "tabStatsDom";
			strTitre = window.tl_resultats_Domicile;
			break;
		case 2:
			strParent = "tabStatsDom";
			strTitre = window.tl_resultats_Visiteur;
			break;
		case 3:
			strParent = "divStats";
			strTitre =window.tl_stats_Statistiques;
			break;
		default:
			break;
	}

	/*
	 div1 = document.createElement('DIV');
	 div1.id = "divStatsDom";
	 div1.className = "centrale";
	 lecorps = document.getElementsByTagName('BODY')[0];*/
	div1 = document.getElementById(strParent);
	//	alert(div1.tagName);
	table1 = document.createElement('TABLE');
	table1.id = "tableStatsDom_" + code;
	table1.className = "c_tableau";
	tb = document.createElement('TBODY');
	tb.id = "bodyTable_" + code;
	tr1 = document.createElement('TR');
	tr1.id = "nomEquipe_" + code;
	tr1.style.marginTop="20px";
	td1 = document.createElement('TD');
	td1.className = "titreTableau";
	td1.colSpan = "8";
	td1.innerHTML = strTitre;
	tr2 = document.createElement('TR');
	tr2.id = "rangeeTitreHome_" + code;
	tr2.className="rangeeTitreSommaire";
	var lesTitres = new Array();
	lesTitres[0] = window.tl_match_Joueur;
	lesTitres[1] = window.tl_stats_Buts;
	lesTitres[2] = window.tl_stats_Passes;
	lesTitres[3] = window.tl_class_Points;

	for ( i = 0; i < lesTitres.length; i++) {
		tdi = document.createElement('TD');
		tdi.className = "rangeeTitre";
		tdi.innerHTML = lesTitres[i];
		tr2.appendChild(tdi);
	}

	tr1.appendChild(td1);
	tb.appendChild(tr1);
	tb.appendChild(tr2);
	table1.appendChild(tb);
	div1.appendChild(table1);
	//	lecorps.appendChild(div1);

	try {
		//statsJoueurs.joueurs.triPar("nbButs",1,1);
		statsJoueurs.joueurs.sort(function(a, b) {
			A = (parseInt(b.nbButs) + parseInt(b.nbPasses)) - (parseInt(a.nbButs) + parseInt(a.nbPasses));
			B = A ? A : (parseInt(b.nbButs) - parseInt(a.nbButs));
			return B
		});
	} catch(err) {
		alert('try sort fail...' + statsJoueurs.joueurs.length);
	}
	try {

		for ( J = 0; J < statsJoueurs.joueurs.length; J = J + 1) {
			//	if(!statsJoueurs.joueurs[J].nom.isNull&&statsJoueurs.joueurs[J].nom!='Anonyme')
			if (!statsJoueurs.joueurs[J].nom.isNull) {
				rangee = document.createElement('TR');
				amettre = document.getElementById('rangeeTitreHome_' + code);
				amettre.parentNode.appendChild(rangee);

				var lesCols = new Array();
				lesCols[0] = statsJoueurs.joueurs[J].nom;
				lesCols[1] = statsJoueurs.joueurs[J].nbButs;
				lesCols[2] = statsJoueurs.joueurs[J].nbPasses;
				lesCols[3] = parseInt(statsJoueurs.joueurs[J].nbButs) + parseInt(statsJoueurs.joueurs[J].nbPasses);

				var lesLiens = new Array();
				lesLiens[0] = '/zstats/statsjoueur.html?joueurId=' + statsJoueurs.joueurs[J].joueurId;
				lesLiens[1] = null;
				lesLiens[2] = null;
				lesLiens[3] = null;
				for ( K = 0; K < lesCols.length; K++) {
					cellule = document.createElement('TD');
					if (lesCols[K] != undefined) {
						if (lesLiens[K] != null) {
							lien = document.createElement('A');
							lien.innerHTML = lesCols[K];
							lien.href = lesLiens[K];
							cellule.appendChild(lien);
						} else {
							texte = document.createTextNode(lesCols[K]);
							cellule.appendChild(texte);
						}
					}
					rangee.appendChild(cellule);
				}
				J % 2 == 0 ? rangee.className = 'lignePaire' : rangee.className = 'ligneImpaire';
			}

		}

		//	document.body.rangee.cellule.innerHTML ='yo';
	} catch(err) {
		alert('try display fail...');
	}

}

function afficheStatsEquipe(statsJSON) {
	div1 = document.createElement('DIV');
	div1.id = "divClassement";
	div1.name = "divClassement";
	div1.className = "centrale";
	lecorps = document.getElementById('mbody');
	/*	lecorps.appendChild(divG);*/
	lecorps.appendChild(div1);
	/*	lecorps.appendChild(divD);*/
	construitMP(statsJSON);
	togouleNom = 1;
	togouleButs = 1;
	togoulePasses = 1;
	togoulePoints = 1;

	function classeNom(statsJSON) {
		return function() {
			statsJSON.joueurs.sort(function(a, b) {
				return ((a.nom == b.nom) ? 0 : ((a.nom.toLowerCase() > b.nom.toLowerCase()) ? togouleNom : -togouleNom ));
			});
			togouleNom = -1 * togouleNom;
			document.getElementById('rangeeTitre_2').className = togouleNom == 1 ? 'btnClasse haut' : 'btnClasse bas';
			videNoeud(window.strParent);
			construitMP(statsJSON);
		}
	};

	function classeButs(statsJSON) {
		return function() {
			statsJSON.joueurs.sort(function(a, b) {
				return (togouleButs * (parseInt(b.nbButs) - parseInt(a.nbButs)));
			});
			togouleButs = -1 * togouleButs;
			document.getElementById('rangeeTitre_5').className = togouleButs == 1 ? 'btnClasse haut' : 'btnClasse bas';
			videNoeud(window.strParent);
			construitMP(statsJSON);
		}
	};

	function classePasses(statsJSON) {
		return function() {
			statsJSON.joueurs.sort(function(a, b) {
				return (togoulePasses * (parseInt(b.nbPasses) - parseInt(a.nbPasses)));
			});
			togoulePasses = -1 * togoulePasses;
			document.getElementById('rangeeTitre_6').className = togoulePasses == 1 ? 'btnClasse haut' : 'btnClasse bas';
			videNoeud(window.strParent);
			construitMP(statsJSON);
		}
	};

	function classePoints(statsJSON) {
		return function() {
			statsJSON.joueurs.sort(function(a, b) {
				return (togoulePoints * (parseInt(b.nbPasses) + parseInt(b.nbButs) - parseInt(a.nbPasses) - parseInt(a.nbButs)));
			});
			togoulePoints = -1 * togoulePoints;
			document.getElementById('rangeeTitre_7').className = togoulePoints == 1 ? 'btnClasse haut' : 'btnClasse bas';
			videNoeud(window.strParent);
			construitMP(statsJSON);
		}
	};

	function construitMP(statsJSON) {
		div1 = document.getElementById(window.strParent);
		table1 = document.createElement('TABLE');
		table1.id = "tableEquipe";
		table1.className = "c_tableau";
		tb = document.createElement('TBODY');
		tb.id = "bodyTable";
		tr1 = document.createElement('TR');
		tr1.id = "date2";
		td1 = document.createElement('TD');
		td1.className = "titreTableau";
		td1.colSpan = "8";
		td1.innerHTML = window.tl_meneurs_Meneurs;
		tr2 = document.createElement('TR');
		tr2.name = "rangeeTitreEquipe";
		tr2.id = "rangeeTitreEquipe";
		var titreClassement = new Array();
		titreClassement[0] = window.tl_meneurs_Rang;
		titreClassement[1] = window.tl_meneurs_Numero;
		titreClassement[2] = window.tl_match_Joueur;
		titreClassement[3] = window.tl_match_Equipe;
		titreClassement[4] = window.tl_meneurs_parties;
		titreClassement[5] = window.tl_stats_Buts;
		titreClassement[6] = window.tl_stats_Passes;
		titreClassement[7] = window.tl_class_Points;

		var titreLiens = new Array();
		titreLiens[0] = null;
		titreLiens[1] = null;
		titreLiens[2] = classeNom(statsJSON);
		titreLiens[3] = null;
		titreLiens[4] = null;
		titreLiens[5] = classeButs(statsJSON);
		titreLiens[6] = classePasses(statsJSON);
		titreLiens[7] = classePoints(statsJSON);

		for ( i = 0; i < titreClassement.length; i++) {
			tdi = document.createElement('TD');
			tdi.className = "rangeeTitre";
			if (titreLiens[i] != null) {
				lien = document.createElement('INPUT');
				//		lien.href = 'javascript:void(0)';
				lien.type = 'button';
				lien.id = 'rangeeTitre_' + i;
				lien.onclick = titreLiens[i];
				lien.value = titreClassement[i];
				tdi.appendChild(lien);
			} else {
				tdi.innerHTML = titreClassement[i];
			}
			tr2.appendChild(tdi);
		}

		tr1.appendChild(td1);
		tb.appendChild(tr1);
		tb.appendChild(tr2);
		table1.appendChild(tb);
		div1.appendChild(table1);

		try {

			//statsJSON.joueurs.triPar("nbButs",1,1);
			for ( J = 0; J < statsJSON.joueurs.length; J = J + 1) {
				//	if(!statsJSON.joueurs[J].nom.isNull&&statsJSON.joueurs[J].nom!='Anonyme')
				if (!statsJSON.joueurs[J].nom.isNull) {
					rangee = document.createElement('TR');
					amettre = document.getElementById('rangeeTitreEquipe');
					amettre.parentNode.appendChild(rangee);
					//			alert('1');

					var lesCols = new Array();
					lesCols[0] = parseInt(J + 1);
					lesCols[1] = statsJSON.joueurs[J].nomEquipe == null ? " " : statsJSON.joueurs[J].numero;
					lesCols[2] = statsJSON.joueurs[J].nom;
					lesCols[3] = statsJSON.joueurs[J].nomEquipe == null ? " " : statsJSON.joueurs[J].nomEquipe;
					lesCols[4] = statsJSON.joueurs[J].pj;
					lesCols[5] = statsJSON.joueurs[J].nbButs;
					lesCols[6] = statsJSON.joueurs[J].nbPasses;
					lesCols[7] = parseInt(statsJSON.joueurs[J].nbButs) + parseInt(statsJSON.joueurs[J].nbPasses);

					var lesLiens = new Array();
					lesLiens[0] = null;
					lesLiens[1] = null;
					lesLiens[2] = '/zstats/statsjoueur.html?joueurId=' + statsJSON.joueurs[J].id;
					lesLiens[3] = null;
					//'statsequipe.html?equipeId=' + cJSON.equipes[J].id + '&m=' + window.m;
					lesLiens[4] = null;
					lesLiens[5] = null;
					lesLiens[6] = null;
					lesLiens[7] = null;

					for ( K = 0; K < lesCols.length; K++) {
						cellule = document.createElement('TD');
						if (lesLiens[K] != null) {
							lien = document.createElement('A');
							lien.innerHTML = lesCols[K];
							lien.href = lesLiens[K];
							cellule.appendChild(lien);
						} else {
							texte = document.createTextNode(lesCols[K]);
							cellule.appendChild(texte);
						}
						rangee.appendChild(cellule);
					}
					J % 2 == 0 ? rangee.className = 'lignePaire' : rangee.className = 'ligneImpaire';

				}

			}
		} catch(err) {
		}

	}

}

function afficheMeneursPatineurs(statsJSON, code) {
	/*	divG = document.createElement('DIV');
	divG.id = "divGauche";
	//div1.className = "centrale";
	divD = document.createElement('DIV');
	divD.id = "divDroite";*/
	//	div1.className = "centrale";

	/*div1 = document.createElement('DIV');
	 div1.id = "divClassement";
	 div1.name = "divClassement";
	 div1.className = "centrale";*/
	strParent = "mbody";
	switch(code) {
		case 1:
			strParent = "tabMenPat";
			break;
		case 2:
			strParent = "tabMenAN";
			break;
		case 3:
			strParent = "tabMenDN";
			break;
	}
	div1 = document.getElementById(strParent);
	/*	lecorps.appendChild(divG);*/
	/*	lecorps.appendChild(divD);*/
	construitMP(statsJSON, code);

	togouleNom = 1;
	togouleButs = 1;
	togoulePasses = 1;
	togoulePoints = 1;

	function classeNom(statsJSON, code) {
		return function() {
			statsJSON.joueurs.sort(function(a, b) {
				if (b.nom == null)
					return -1;
				else if (a.nom == null)
					return 1;
				else
					return ((a.nom == b.nom) ? 0 : ((a.nom.toLowerCase() > b.nom.toLowerCase()) ? togouleNom : -togouleNom ));
			});
			togouleNom = -1 * togouleNom;
			//		videNoeud(strParent);
			construitMP(statsJSON, code);
			document.getElementById('btnClasse_2').className = (togouleNom == 1) ? "btnClasse haut" : "btnClasse bas";
			//		alert(document.getElementById('btnClasse_2').className);
			tbody = document.getElementById('meneurs_tbody_' + code);
			for ( a = 2; a < tbody.childNodes.length; a++) {
				tbody.childNodes[a].childNodes[2].className = "colChoisie";
			}
		}
	};

	function classeButs(statsJSON, code) {
		return function() {
			statsJSON.joueurs.sort(function(a, b) {
				return (togouleButs * (parseInt(b.nbButs) - parseInt(a.nbButs)));
			});
			togouleButs = -1 * togouleButs;
			//		videNoeud(strParent);
			construitMP(statsJSON, code);
			document.getElementById('btnClasse_5').className = togouleButs == 1 ? 'btnClasse haut' : 'btnClasse bas';
			tbody = document.getElementById('meneurs_tbody_' + code);
			for ( a = 2; a < tbody.childNodes.length; a++) {
				tbody.childNodes[a].childNodes[5].className = "colChoisie";
			}
		}
	};

	function classePasses(statsJSON, code) {
		return function() {
			statsJSON.joueurs.sort(function(a, b) {
				return (togoulePasses * (parseInt(b.nbPasses) - parseInt(a.nbPasses)));
			});
			togoulePasses = -1 * togoulePasses;
			//		videNoeud(strParent);
			construitMP(statsJSON, code);
			document.getElementById('btnClasse_6').className = togoulePasses == 1 ? 'btnClasse haut' : 'btnClasse bas';
			tbody = document.getElementById('meneurs_tbody_' + code);
			for ( a = 2; a < tbody.childNodes.length; a++) {
				tbody.childNodes[a].childNodes[6].className = "colChoisie";
			}
		}
	};

	function classePoints(statsJSON, code) {
		return function() {
			statsJSON.joueurs.sort(function(a, b) {
				return (togoulePoints * (parseInt(b.nbPasses) + parseInt(b.nbButs) - parseInt(a.nbPasses) - parseInt(a.nbButs)));
			});
			togoulePoints = -1 * togoulePoints;
			//	videNoeud(strParent);
			construitMP(statsJSON, code);
			document.getElementById('btnClasse_7').className = togoulePoints == 1 ? 'btnClasse haut' : 'btnClasse bas';
			tbody = document.getElementById('meneurs_tbody_' + code);
			for ( a = 2; a < tbody.childNodes.length; a++) {
				tbody.childNodes[a].childNodes[7].className = "colChoisie";
			}
		}
	};

	function construitMP(statsJSON, code) {
		switch(code) {
			case 1:
				strParent = "tabMenPat";
				break;
			case 2:
				strParent = "tabMenAN";
				break;
			case 3:
				strParent = "tabMenDN";
				break;
		}
		//	alert(JSON.stringify(statsJSON));

		detruitNoeud("tableMeneurs_" + code);
		div1 = document.getElementById(strParent);
		table1 = document.createElement('TABLE');
		table1.id = "tableMeneurs_" + code;
		table1.className = "c_tableau";
		tb = document.createElement('TBODY');
		tb.id = "meneurs_tbody_" + code;
		//		tb.id = "bodyTable";
		tr1 = document.createElement('TR');
		//		tr1.id = "date2";
		td1 = document.createElement('TD');
		td1.className = "titreTableau";
		td1.colSpan = "8";
		td1.innerHTML = window.tl_meneurs_Meneurs;
		tr2 = document.createElement('TR');
		tr2.name = "rangeeTitreEquipe";
		tr2.id = "rangeeTitre_" + code;
		tr2.style.backgroundColor = "#f5F5F5";
		var titreClassement = new Array();
		titreClassement[0] = window.tl_meneurs_Rang;
		titreClassement[1] = window.tl_meneurs_Numero;
		titreClassement[2] = window.tl_match_Joueur;
		titreClassement[3] = window.tl_match_Equipe;
		titreClassement[4] = window.tl_meneurs_parties;
		titreClassement[5] = window.tl_stats_Buts;
		titreClassement[6] = window.tl_stats_Passes;
		titreClassement[7] = window.tl_class_Points;

		var titreLiens = new Array();
		titreLiens[0] = null;
		titreLiens[1] = null;
		titreLiens[2] = classeNom(statsJSON, code);
		titreLiens[3] = null;
		titreLiens[4] = null;
		titreLiens[5] = classeButs(statsJSON, code);
		titreLiens[6] = classePasses(statsJSON, code);
		titreLiens[7] = classePoints(statsJSON, code);

		for ( i = 0; i < titreClassement.length; i++) {
			tdi = document.createElement('TD');
			tdi.className = "rangeeTitre";
			if (titreLiens[i] != null) {
				lien = document.createElement('P');
				//		lien.href = 'javascript:void(0)';
				//				lien.type = 'button';
				lien.id = 'btnClasse_' + i;
				lien.className = "btnClasse haut";
				lien.onclick = titreLiens[i];
				lien.innerHTML = titreClassement[i];
				tdi.appendChild(lien);
			} else {
				tdi.innerHTML = titreClassement[i];
			}
			tr2.appendChild(tdi);
		}

		tr1.appendChild(td1);
		//tb.appendChild(tr1);
		tb.appendChild(tr2);
		table1.appendChild(tb);
		div1.appendChild(table1);

		try {
			if (statsJSON.joueurs.length == 0) {
				rangee = document.createElement('TR');
				amettre = document.getElementById("rangeeTitre_" + code);
				amettre.parentNode.appendChild(rangee);
				td1 = document.createElement('TD');
				td1.className = "titreTableau";
				td1.colSpan = "8";
				td1.innerHTML = "Aucune donnée n'est disponible pour cette sélection.";
				rangee.appendChild(td1);
			}

			//statsJSON.joueurs.triPar("nbButs",1,1);
			for ( J = 0; J < statsJSON.joueurs.length; J = J + 1) {
				//	if(!statsJSON.joueurs[J].nom.isNull&&statsJSON.joueurs[J].nom!='Anonyme')
				if (statsJSON.joueurs[J].nom != null) {
					rangee = document.createElement('TR');
					amettre = document.getElementById("rangeeTitre_" + code);
					amettre.parentNode.appendChild(rangee);
					//			alert('1');

					var lesCols = new Array();
					lesCols[0] = parseInt(J + 1);
					lesCols[1] = statsJSON.joueurs[J].nomEquipe == null ? " " : statsJSON.joueurs[J].numero;
					lesCols[2] = statsJSON.joueurs[J].nom == null ? " " : statsJSON.joueurs[J].nom;
					lesCols[3] = statsJSON.joueurs[J].nomEquipe == null ? " " : statsJSON.joueurs[J].nomEquipe;
					lesCols[4] = statsJSON.joueurs[J].pj;
					lesCols[5] = statsJSON.joueurs[J].nbButs;
					lesCols[6] = statsJSON.joueurs[J].nbPasses;
					lesCols[7] = parseInt(statsJSON.joueurs[J].nbButs) + parseInt(statsJSON.joueurs[J].nbPasses);

					var lesLiens = new Array();
					lesLiens[0] = null;
					lesLiens[1] = null;
					lesLiens[2] = '/zstats/statsjoueur.html?joueurId=' + statsJSON.joueurs[J].id;
					lesLiens[3] = null;
					//'statsequipe.html?equipeId=' + cJSON.equipes[J].id + '&m=' + window.m;
					lesLiens[4] = null;
					lesLiens[5] = null;
					lesLiens[6] = null;
					lesLiens[7] = null;

					for ( K = 0; K < lesCols.length; K++) {
						cellule = document.createElement('TD');
						if (lesLiens[K] != null) {
							lien = document.createElement('A');
							lien.innerHTML = lesCols[K];
							lien.href = lesLiens[K];
							cellule.appendChild(lien);
						} else {
							texte = document.createTextNode(lesCols[K]);
							cellule.appendChild(texte);
						}
						rangee.appendChild(cellule);
					}
					J % 2 == 0 ? rangee.className = 'lignePaire' : rangee.className = 'ligneImpaire';
					var a = statsJSON.joueurs[J].id;
					rangee.onclick = (function(a) {
						return function() {
							construitDialogue();
							faireFiche(a);
						};
					})(statsJSON.joueurs[J].id);

				}

			}
		} catch(err) {
		}

	}

	//////////////////////////////////////////////////////
	//	Tableau gardien
	//////////////////////////////////////////////////////
	/*
	try {

	gJSON.gardiens.sort(function(a, b) {
	buts1 = parseInt(a.nbButs);
	match1 = parseInt(a.victoires) + parseInt(a.defaites) + parseInt(a.nulles);
	m1 = buts1 / match1;
	moy1 = Math.round(m1 * 1000) / 1000;
	buts2 = parseInt(b.nbButs);
	match2 = parseInt(b.victoires) + parseInt(b.defaites) + parseInt(b.nulles);
	m2 = buts2 / match2;
	moy2 = Math.round(m2 * 1000) / 1000;
	A = moy1 - moy2;
	B = A ? A : (parseInt(b.victoires) - parseInt(a.victoires));
	return B
	});

	} catch(err) {
	}
	*/
}

function afficheMeneursGardiens(gJSON) {
	////////////  Gardiens
	/*	div1 = document.createElement('DIV');
	div1.id = "divClassement";
	div1.className = "centrale";*/
	div1 = document.getElementById('tabMenGard');
	table1 = document.createElement('TABLE');
	table1.id = "tableauGardiens";
	table1.className = "c_tableau";
	tb = document.createElement('TBODY');
	tb.id = "bodyTable";
	tr2 = document.createElement('TR');
	tr2.id = "rangeeTitreGardiens";
	tr2.style.backgroundColor = "#f5f5f5";
	var titreClassement = new Array();
	titreClassement[0] = window.tl_meneurs_Rang;
	titreClassement[1] = window.tl_meneurs_Gardiens;
	titreClassement[2] = window.tl_meneurs_parties;
	titreClassement[3] = window.tl_class_Vic;
	titreClassement[4] = window.tl_class_Def;
	titreClassement[5] = window.tl_class_Nul;
	titreClassement[6] = window.tl_meneurs_BA;
	titreClassement[7] = window.tl_meneurs_MBA;

	for ( i = 0; i < titreClassement.length; i++) {
		tdi = document.createElement('TD');
		tdi.className = "rangeeTitre";
		tdi.innerHTML = titreClassement[i];
		tr2.appendChild(tdi);
	}

	tb.appendChild(tr2);
	table1.appendChild(tb);
	div1.appendChild(table1);
	//	lecorps.appendChild(div1);

	//////////////////////////////////////////////////////
	//	Tableau gardien
	//////////////////////////////////////////////////////

	try
	{

		gJSON.gardiens.sort(function(a, b) {
			buts1 = parseInt(a.nbButs);
			match1 = parseInt(a.victoires) + parseInt(a.defaites) + parseInt(a.nulles);
			m1 = buts1 / match1;
			moy1 = Math.round(m1 * 1000) / 1000;
			buts2 = parseInt(b.nbButs);
			match2 = parseInt(b.victoires) + parseInt(b.defaites) + parseInt(b.nulles);
			m2 = buts2 / match2;
			moy2 = Math.round(m2 * 1000) / 1000;
			A = moy1 - moy2;
			B = A ? A : (parseInt(b.victoires) - parseInt(a.victoires));
			return B
		});

		if (gJSON.gardiens.length == 0) {
			rangee = document.createElement('TR');
			amettre = document.getElementById("rangeeTitreGardiens");
			amettre.parentNode.appendChild(rangee);
			td1 = document.createElement('TD');
			td1.className = "titreTableau";
			td1.colSpan = "8";
			td1.innerHTML = tl_general_noData;
			rangee.appendChild(td1);
		}

		for ( J = 0; J < gJSON.gardiens.length; J = J + 1) {
			//	if(!statsJSON.joueurs[J].nom.isNull&&statsJSON.joueurs[J].nom!='Anonyme')
			if (gJSON.gardiens[J].nom != null) {
				//		alert("dans le if:" + gJSON.gardiens[J].nom);
				rangee = document.createElement('TR');
				amettre = document.getElementById('rangeeTitreGardiens');
				amettre.parentNode.appendChild(rangee);

				var buts = parseInt(gJSON.gardiens[J].nbButs);
				var match = parseInt(gJSON.gardiens[J].victoires) + parseInt(gJSON.gardiens[J].defaites) + parseInt(gJSON.gardiens[J].nulles);

				var moy = buts / match;
				celluleMoy = document.createElement('TD');

				var lesCols = new Array();
				lesCols[0] = parseInt(J + 1);
				lesCols[1] = gJSON.gardiens[J].nom;
				lesCols[2] = parseInt(gJSON.gardiens[J].victoires) + parseInt(gJSON.gardiens[J].defaites) + parseInt(gJSON.gardiens[J].nulles);
				lesCols[3] = gJSON.gardiens[J].victoires;
				lesCols[4] = gJSON.gardiens[J].defaites;
				lesCols[5] = gJSON.gardiens[J].nulles;
				lesCols[6] = gJSON.gardiens[J].nbButs;
				lesCols[7] = Math.round(moy * 1000) / 1000;

					var lesLiens = new Array();
					lesLiens[0] = null;
					lesLiens[1] = '/zstats/statsjoueur.html?joueurId=' + gJSON.gardiens[J].id;
					lesLiens[2] = null;
					lesLiens[3] = null;
					//'statsequipe.html?equipeId=' + cJSON.equipes[J].id + '&m=' + window.m;
					lesLiens[4] = null;
					lesLiens[5] = null;
					lesLiens[6] = null;
					lesLiens[7] = null;

					for ( K = 0; K < lesCols.length; K++) {
						cellule = document.createElement('TD');
						if (lesLiens[K] != null) {
							lien = document.createElement('A');
							lien.innerHTML = lesCols[K];
							lien.href = lesLiens[K];
							cellule.appendChild(lien);
						} else {
							texte = document.createTextNode(lesCols[K]);
							cellule.appendChild(texte);
						}
						rangee.appendChild(cellule);
					}
					J % 2 == 0 ? rangee.className = 'lignePaire' : rangee.className = 'ligneImpaire';

			}

		}

		//	document.body.rangee.cellule.innerHTML ='yo';
	} catch(err) {
	}

}

function afficheResumeMatch(infoMatch) {

	div1 = document.getElementById("tabResume");
	//alert(div1.id);
	divLigne = document.createElement('DIV');
	divLigne.id = "ligneCentrale1";

	divLogoDom = document.createElement('DIV');
	divLogoDom.id = "logoDom";
	divLogoDom.className = "grosLogo";
	divLogoVis = document.createElement('DIV');
	divLogoVis.id = "logoVis";
	divLogoVis.className = "grosLogo";
	lienImgDomJS = document.createElement('A');
	lienImgDomJS.id = "lienImgDom";
	lienImgDomJS.href = "/zstats/statsequipe.html?equipeId=" + infoMatch.equipeIdDom + "&ligueId=" + infoMatch.ligueId;
	divImgDom = document.createElement('IMG');
	divImgDom.id = "logoEquipeDom";
	divImgDom.className = "grosLogo";
	lienImgVisJS = document.createElement('A');
	lienImgVisJS.id = "lienImgVis";
	lienImgVisJS.href = "/zstats/statsequipe.html?equipeId=" + infoMatch.equipeIdVis + "&ligueId=" + infoMatch.ligueId;
	divImgVis = document.createElement('IMG');
	divImgVis.id = "logoEquipeVis";
	divImgVis.className = "grosLogo";
	divPDom = document.createElement('P');
	divPDom.id = "tScoreDom";
	divPDom.className = "grosTexte";
	divPVis = document.createElement('P');
	divPVis.id = "tScoreVis";
	divPVis.className = "grosTexte";
	divScoreDom = document.createElement('DIV');
	divScoreDom.id = "scoreDom";
	divScoreDom.className = "grosTexte";
	divScoreVis = document.createElement('DIV');
	divScoreVis.id = "scoreVis";
	divScoreVis.className = "grosTexte";
	pDash = document.createElement('P');
	pDash.className = "grosTexte";
	pDash.innerHTML = "-";
	divDash = document.createElement('DIV');
	divDash.className = "grosTexte";

	divLogoDom.appendChild(lienImgDomJS);
	lienImgDomJS.appendChild(divImgDom);
	divLigne.appendChild(divLogoDom);
	divScoreDom.appendChild(divPDom);
	divLigne.appendChild(divScoreDom);
	divDash.appendChild(pDash);
	divLigne.appendChild(divDash);
	divScoreVis.appendChild(divPVis);
	divLigne.appendChild(divScoreVis);
	divLogoVis.appendChild(lienImgVisJS);
	lienImgVisJS.appendChild(divImgVis);
	divLigne.appendChild(divLogoVis);
	//	lecorps = document.getElementById('mbody');
	divImgDom.src = '/admin/afficheImage.php?ficId=' + infoMatch.equipeFicIdDom;
	divImgVis.src = '/admin/afficheImage.php?ficId=' + infoMatch.equipeFicIdVis;

	divPDom.innerHTML = infoMatch.equipeScoreDom;
	divPVis.innerHTML = infoMatch.equipeScoreVis;
	//	lecorps.appendChild(divLigne);
	div1.appendChild(divLigne);

}


function afficheFusillade(rJSON) {

	if (rJSON.Fusillade.length > 0) {
		div1 = document.getElementById('tabResume');
		tabFus = document.createElement('TABLE');
		tabFus.className = 'c_tableau';
		tabFus.id = 'tabFus';
		tBodyFus = document.createElement('TBODY');
		tBodyFus.id = 'tBodyFus';
		tdRTFus = document.createElement('TD');
		tdRTFus.id = 'tdTitreTableauFus';
		tdRTFus.className = 'titreTableau';
		tdRTFus.innerHTML = window.tl_match_Fusillade;
		tdRTFus.colSpan = 4;
		trRTFus = document.createElement('TR');
		trRTFus.id = 'trTitreTableauFus';
		trRTFus.className = 'titreTableau';
		trTitreFus = document.createElement('TR');
		trTitreFus.id = 'trTitreFus';
		trTitreFus.className = 'rangeeTitre';
		tdEqDomFus = document.createElement('TD');
		tdEqDomFus.id = 'tdEqDomFus';
		tdEqDomFus.innerHTML = rJSON.eqDom;
		tdEqDomFus.colSpan = 2;
		tdEqDomFus.style.width = "50%";
		tdEqVisFus = document.createElement('TD');
		tdEqVisFus.id = 'tdEqVisFus';
		tdEqVisFus.colSpan = 2;
		tdEqVisFus.style.width = "50%";
		tdEqVisFus.innerHTML = rJSON.eqVis;

		tabFus.appendChild(tBodyFus);
		trRTFus.appendChild(tdRTFus);
		tBodyFus.appendChild(trRTFus);
		tBodyFus.appendChild(trTitreFus);
		trTitreFus.appendChild(tdEqDomFus);
		trTitreFus.appendChild(tdEqVisFus);

		div1.appendChild(tabFus);
		nbLig = 0;
		nbDom = 0;
		nbVis = 0;
		var j = 0;
		while (j < rJSON.Fusillade.length) {

			if (rJSON.Fusillade[j].equipe == rJSON.eqDom) {
				if (nbDom >= nbLig) {
					trFus = document.createElement('TR');
					trFus.id = 'trFus_' + nbLig;
					trFus.className = 'lignePaire';

					tdFus = document.createElement('TD');
					tdFus.innerHTML = rJSON.Fusillade[j].nom;
					tdFus2 = document.createElement('TD');
					tdFus2.innerHTML = (rJSON.Fusillade[j].but) ? '\u2713' : 'X';
					(nbLig) % 2 == 0 ? tdFus.className = 'lignePaire' : tdFus.className = 'ligneImpaire';
					(nbLig) % 2 == 0 ? tdFus2.className = 'lignePaire' : tdFus2.className = 'ligneImpaire';

					trFus.appendChild(tdFus);
					trFus.appendChild(tdFus2);

					nbLig++;
					tBodyFus.appendChild(trFus);
				} else {
					trFus = document.getElementById('trFus_' + nbDom);

					tdFus = document.createElement('TD');
					tdFus.innerHTML = rJSON.Fusillade[j].nom;
					tdFus2 = document.createElement('TD');
					tdFus2.innerHTML = (rJSON.Fusillade[j].but) ? '\u2713' : 'X';
					(nbLig) % 2 == 0 ? tdFus.className = 'lignePaire' : tdFus.className = 'ligneImpaire';
					(nbLig) % 2 == 0 ? tdFus2.className = 'lignePaire' : tdFus2.className = 'ligneImpaire';

					trFus.appendChild(tdFus);
					trFus.appendChild(tdFus2);

				}
				nbDom++;
			}
			if (rJSON.Fusillade[j].equipe == rJSON.eqVis) {
				if (nbVis >= nbLig) {

					trFus = document.createElement('TR');
					trFus.id = 'trFus_' + nbLig;
					trFus.className = 'lignePaire';

					tdFus = document.createElement('TD');
					tdFus.innerHTML = rJSON.Fusillade[j].nom;
					tdFus2 = document.createElement('TD');
					tdFus2.innerHTML = (rJSON.Fusillade[j].but) ? "\u2713 " : 'X';
					(nbLig) % 2 == 0 ? tdFus.className = 'lignePaire' : tdFus.className = 'ligneImpaire';
					(nbLig) % 2 == 0 ? tdFus2.className = 'lignePaire' : tdFus2.className = 'ligneImpaire';

					trFus.appendChild(tdFus);
					trFus.appendChild(tdFus2);

					nbLig++;
					tBodyFus.appendChild(trFus);
				} else {
					trFus = document.getElementById('trFus_' + nbVis);
					tdFus = document.createElement('TD');
					tdFus.innerHTML = rJSON.Fusillade[j].nom;
					tdFus2 = document.createElement('TD');
					tdFus2.innerHTML = (rJSON.Fusillade[j].but) ? "\u2713 " : 'X';
					(nbLig) % 2 == 0 ? tdFus.className = 'lignePaire' : tdFus.className = 'ligneImpaire';
					(nbLig) % 2 == 0 ? tdFus2.className = 'lignePaire' : tdFus2.className = 'ligneImpaire';

					trFus.appendChild(tdFus);
					trFus.appendChild(tdFus2);

				}

				nbVis++;
			}
			j++;
		}
	}

}

//////////////////////////////////////////////////////
///
///			Stats en carrières
///
////////////////////////////////////////////////////////

function afficheStatsCarriere(statsJSONHome, code) {
	vPerm = verifiePermission();
	var lesTitres = new Array();
	lesTitres[0] = window.tl_stats_saisonDeb;
	lesTitres[1] = window.tl_stats_saisonFin;
	lesTitres[2] = window.tl_match_Equipe;
	lesTitres[3] = window.tl_meneurs_parties;
	lesTitres[4] = window.tl_stats_Buts;
	lesTitres[5] = window.tl_stats_Passes;
	lesTitres[6] = window.tl_class_Points;
	lesTitres[7] = window.tl_stats_MPM;

	switch(code) {
		case 1:
			div1 = document.getElementById('tabStats');

			break;
		case 2:
			div1 = document.getElementById('divDialogue');

			break;
		case 3:
			div1 = document.getElementById('onglet_0');

			break;
		case 4:
			div1 = document.getElementById('contStats');

			break;
		default:
			/*			div1 = document.createElement('DIV');
			 div1.id = "divStatsMatch";
			 div1.className = "centrale";
			 lecorps = document.getElementsByTagName("body")[0];
			 lecorps.appendChild(div1);*/
			div1 = document.getElementById('divCentrale');

			break;
	}
	/////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////

	try
	{
		for ( I = 0; I < statsJSONHome.Ligues.length; I = I + 1) {

			table1 = document.createElement('TABLE');
			//	table1.id = "tableSommaireButs";
			table1.className = "c_tableau";
			tb = document.createElement('TBODY');
			//tb.id = "bodyTable";
			tr1 = document.createElement('TR');
			tr1.className = "titreTableau";
			//	tr1.id = "date2";
			td1 = document.createElement('TD');
			td1.className = "titreTableau";
			td1.colSpan = lesTitres.length;
			td1.innerHTML = window.tl_stats_carriere + " - " + statsJSONHome.Ligues[I].nom;
			tr2 = document.createElement('TR');
							tr2.className="rangeeTitreSommaire";
			//	tr2.className = "rangeeTitreSC";

			for ( n = 0; n < lesTitres.length; n++) {
				tdi = document.createElement('TD');
				tdi.className = "rangeeTitre";
				tdi.innerHTML = lesTitres[n];
				tr2.appendChild(tdi);
			}
			tr2.className = "rangeeTitre";

			tr1.appendChild(td1);
			tb.appendChild(tr1);
			tb.appendChild(tr2);
			table1.appendChild(tb);
			div1.appendChild(table1);

			for ( J = 0; J < statsJSONHome.Ligues[I].saisons.length; J = J + 1) {
				//		if(!statsJSONHome.joueurs[J].nom.isNull&&statsJSONHome.joueurs[J].nom!='Anonyme')
				//		if(!statsJSONHome.joueurs[J].nom.isNull)
				for ( K = 0; K < statsJSONHome.Ligues[I].saisons[J].equipes.length; K = K + 1) {

					//	if(!statsJSON.joueurs[J].nom.isNull&&statsJSON.joueurs[J].nom!='Anonyme')
					rangee = document.createElement('TR');
					//amettre = document.getElementById('rangeeTitreSC');
					tr2.parentNode.appendChild(rangee);

					var lesCols = new Array();
					lesCols[0] = statsJSONHome.Ligues[I].saisons[J].pm.split(' ')[0];
					lesCols[1] = statsJSONHome.Ligues[I].saisons[J].dm.split(' ')[0];
					lesCols[2] = statsJSONHome.Ligues[I].saisons[J].equipes[K].nom;
					lesCols[3] = statsJSONHome.Ligues[I].saisons[J].equipes[K].pj;
					lesCols[4] = statsJSONHome.Ligues[I].saisons[J].equipes[K].buts;
					lesCols[5] = statsJSONHome.Ligues[I].saisons[J].equipes[K].passes;
					lesCols[6] = parseInt(statsJSONHome.Ligues[I].saisons[J].equipes[K].buts) + parseInt(statsJSONHome.Ligues[I].saisons[J].equipes[K].passes);
					lesCols[7] = Math.round((parseInt(statsJSONHome.Ligues[I].saisons[J].equipes[K].buts) + parseInt(statsJSONHome.Ligues[I].saisons[J].equipes[K].passes)) / parseInt(statsJSONHome.Ligues[I].saisons[J].equipes[K].pj) * 100) / 100;

					var lesLiens = new Array();
					lesLiens[0] = null;
					lesLiens[1] = null;
					lesLiens[2] = "/zstats/statsequipe.html?equipeId=" + statsJSONHome.Ligues[I].saisons[J].equipes[K].equipeId;
					lesLiens[3] = null;
					lesLiens[4] = null;
					lesLiens[5] = null;
					lesLiens[6] = null;
					lesLiens[7] = null;

					for ( M = 0; M < lesCols.length; M++) {
						cellule = document.createElement('TD');
						if (lesCols[M] != undefined) {
							if (lesLiens[M] != null) {
								lien = document.createElement('A');
								lien.innerHTML = lesCols[M];
								lien.href = lesLiens[M];
								cellule.appendChild(lien);
							} else {
								/*ptexte = document.createElement('P');
								ptexte.innerHTML = lesCols[M];
								cellule.appendChild(ptexte);*/
								//					texte = document.createTextNode(lesCols[K]);
								cellule.innerHTML = lesCols[M];
							}
						}
						//(K + J) % 2 == 0 ? cellule.className = 'lignePaire' : cellule.className = 'ligneImpaire';
						rangee.appendChild(cellule);
					}//Fin for écrire la tableau (M)
					(K + J) % 2 == 0 ? rangee.className = 'lignePaire' : rangee.className = 'ligneImpaire';
				}//Fin for équipes (K)
			}//FIN for saisons (J)
			//	document.body.rangee.cellule.innerHTML ='yo';
		}//Fin for Ligues (I)
	}// Fin try
	catch(err) {
	}
	//////////////////////////////////////////////////////////

	//////////////////////////////////////////////////////////

}

/////////////////////////////////////////////////////////
//
//	Fin de Stats en carrières

function afficheUnitesSpeciales() {
	videNoeud('unitesSpeciales');
	window.uJSON.sort(function(a, b) {
		d1 = b.bpan / b.occ;
		d2 = a.bpan / a.occ;
		return (d1 - d2);
	});
	//alert(JSON.stringify(window.uJSON));

	monEq = trouveIndiceJson(window.uJSON, 'equipeId', getValue('equipeId'));

	strAffUS = "";
	strAffUS += tl_stats_AvNum + ": " + parseInt(window.uJSON[monEq].bpan) + " " + window.tl_stats_en + " " + parseInt(window.uJSON[monEq].occ) + " <br /> ";
	strAffUS += window.tl_stats_Efficacite + ": " + parseInt(Math.round(100 * window.uJSON[monEq].bpan / window.uJSON[monEq].occ)) + "%";
	strAffUS += " <br /> ";
	rangAv = parseInt(monEq + 1);
	strAffUS += tl_meneurs_Rang + ": " + rangAv + " " + window.tl_stats_sur + " " + parseInt(window.uJSON.length) + " " + window.tl_stats_equipes;
	strAffUS += " <br /> ";
	strAffUS += " <br /> ";

	window.uJSON.sort(function(a, b) {
		d1 = b.bcdn / b.pun;
		d2 = a.bcdn / a.pun;
		return (d2 - d1);
	});

	monEq = trouveIndiceJson(window.uJSON, 'equipeId', getValue('equipeId'));

	strAffUS += tl_stats_DesavNum + ": " + parseInt(window.uJSON[monEq].bcdn) + " " + window.tl_stats_en + " " + parseInt(window.uJSON[monEq].pun) + " <br /> ";
	strAffUS += window.tl_stats_Efficacite + ": " + parseInt(Math.round(100 * (1 - window.uJSON[monEq].bcdn / window.uJSON[monEq].pun))) + "%";
	strAffUS += " <br /> ";
	rangDesAv = parseInt(monEq + 1);

	strAffUS += tl_meneurs_Rang + ": " + rangDesAv + " " + window.tl_stats_sur + " " + parseInt(window.uJSON.length) + " " + window.tl_stats_equipes;
	strAffUS += " <br /> ";
	strAffUS += " <br /> ";

	document.getElementById('unitesSpeciales').innerHTML = strAffUS;

}
