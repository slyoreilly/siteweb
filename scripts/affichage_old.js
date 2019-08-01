/**
 * @author Kimsyl
 */

// this deletes the cookie when called
function deleteCookie(name, path, domain) {

}

function getCookie(c_name) {
	var i, x, y, ARRcookies = document.cookie.split(";");
	for ( i = 0; i < ARRcookies.length; i++) {
		x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
		y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
		x = x.replace(/^\s+|\s+$/g, "");
		if (x == c_name) {
			return unescape(y);
		}
	}
}

function gereLangue() {
	langue = (getCookie("langue") == undefined) ? "fr" : getCookie("langue");
	document.write('<script type="text/javascript" charset="utf-8" src="/scripts/texte_' + langue + '.js"></script>');
	document.write('<script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.10.2.min.js"></script>');
	document.write('<link rel="stylesheet" href="/scripts/font-awesome-4.7.0/css/font-awesome.min.css">');
	document.write('<link rel="stylesheet" href="/style/general.css" type="text/css">');

	//	langue = (getCookie("langue")==undefined)?"fr":getCookie("langue");

	//	mS = document.createElement('SCRIPT');
	//	mS.src="/scripts/texte_"+langue+".js";
	//	mS.type="text/javascript";
	//	mS.charset="utf-8";
	//	document.getElementsByTagName('HEAD')[0].appendChild(mS);
	//	bidon =document.getElementsByTagName('HEAD')[0].innerHTML;
	//alert("sandwich");
}

function gereLangue_mob() {
	langue = (getCookie("langue") == undefined) ? "fr" : getCookie("langue");
	document.write('<script type="text/javascript" charset="utf-8" src="/scripts/texte_' + langue + '.js"></script>');
	document.write('<script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.10.2.min.js"></script>');

}

function peupleDivHaut() {

	usager1 = getCookie("userId");

	topDiv = document.getElementById('divHaut');
	if (topDiv.childNodes.length > 1) {

		divTitre = document.getElementById('divTitreDoc');
		titre1 = document.getElementById('imgLogo');
		divLogoJS = document.getElementById('divLogo');
	} else {
		divTitre = document.createElement('DIV');
		divTitre.id = 'divTitreDoc';
		titre1 = document.createElement('IMG');
		titre1.id = 'imgLogo';
		h1Titre = document.createElement('H1');
		h1Titre.innerHTML = "SYNCSTATS";
		divLogoJS = document.createElement('DIV');
		divLogoJS.id = "divLogo";
		lienTitre = document.createElement('A');
		lienTitre.href = "/";
		lienTitre.appendChild(h1Titre);
		divLogoJS.appendChild(lienTitre);
		divLogoJS.appendChild(titre1);
		titre1.src = "/images/logoSeul.png";

		divTitre.appendChild(divLogoJS);
		//divTitre.appendChild(divLangueJS);
		topDiv.appendChild(divTitre);
		//		alert(".");

	}
	/*
	 divLangueJS = $('<div/>').attr("id","divLangue").click(function(){
	 if (window.langue == "fr") {
	 $(this).innerHTML = "En";
	 setCookie("langue", "en", 3000);
	 window.location.reload(true);
	 }
	 if (window.langue == "en") {
	 $(this).innerHTML = "Fr";
	 setCookie("langue", "fr", 3000);
	 window.location.reload(true);
	 }

	 });*/

	divLangueSel = $('<select/>').attr("id", "divLangue").append($('<option/>').text("EN").attr('value', 'en'), $('<option/>').text("FR").attr('value', 'fr')).change(function() {
		if ($(this).val() == 'en' && window.langue == "fr") {
			setCookie("langue", "en", 3000);
			window.location.reload(true);
			// Do something for option "b"
		} else if ($(this).val() == 'fr' && window.langue == "en") {
			setCookie("langue", "fr", 3000);
			window.location.reload(true);
		}
	});

	/*
	 if (window.langue == "en") {
	 divLangueJS.innerHTML = "FR </br> EN";
	 } else {
	 divLangueJS.innerHTML = "EN </br> FR";
	 }*/

	divContact = document.createElement('DIV');
	divContact.innerHTML = "Contact: <a href=\"mailto:info&#64syncstats.com\" class=\"lien\">info" + "@" + "syncstats.com</a>";

	if (usager1 === undefined || usager1 === null) {

		toLog = document.createElement('H2');
		// toLog.style.width='20%';
		toLog.id = 'toLog';
		//	toLog.setAttribute('width','90%')

		toLog.style.textAlign = 'right';
		//	toLog.setAttribute('style','text-align:right');
		//	titre.setAttribute('align','middle');

		lien = document.createElement('A');
		lien.innerHTML = window.tl_general_signin;
		lien.className = "cliquable";
		lien.href = '/zuser/inscription.html';
		//		lien.onclick=function(){inscription();}
		toLog.appendChild(lien);

		var newText = document.createTextNode(' | ');

		toLog.appendChild(newText);

		lien2 = document.createElement('A');
		lien2.innerHTML = window.tl_general_login;
		lien2.style.cursor = "pointer";
		/*lien2.onclick = function() {
		construitDialogue();
		dial =document.getElementById('divDialogue');
		monIF=document.createElement('IFRAME');
		dial.appendChild(monIF);
		monIF.src=menus[8].lien+"?m=1";
		}
		*/
		//lien2.href = menus[8].lien;
		lien2.onclick = function() {
			connexion();
		};
		toLog.appendChild(lien2);
		affUserJS = document.createElement('div');
		affUserJS.id = "affUser";
		affUserJS.textAlign = "right";
		affUserJS.className = "optionHautDroit";
		affUserJS.appendChild(toLog);
		toLog.style.padding = "0 2px";
		//divLangueJS.style.marginLeft = "10px";
		//divLangueJS.style.paddingLeft = "10px";
		//divLangueJS.style.borderLeft = "solid 1px #bbb";

		$(toLog).append($(divLangueSel));

		divTitre.appendChild(affUserJS);

		$(toLog).prepend($('<div id="divSearch"></div>').append($('<img></img>').attr("src", "/images/icones/delete.png").attr("id", "iconeDelete").css({
			"width" : "24px",
			"height" : "24px"
		}).hide(), $('<input></input>').attr("type", "text").attr("id", "editSearch"), $('<img></img>').attr("src", "/images/icones/search.png").attr("id", "iconeSearch")));

		//////////////////////////////////////////
		////  Boîte de recherche

		$(function() {
			$('#editSearch').hide();
			$('#iconeSearch').hover(function() {
				$('#editSearch').show(400);
				$('#iconeDelete').show(400);
			});
		});

		$('#iconeDelete').click(function() {
			$('#editSearch').hide();
			$(this).hide();
			detruitNoeud('boiteResRecherche');
		});

		$('#editSearch').keyup(function(event) {

			$.ajax({
				url : "/scriptsphp/recherche.php",
				type : "POST",
				data : {
					searchString : $('#editSearch').val()
				}
			}).done(function(msg) {
				detruitNoeud('boiteResRecherche');
				$('<div id="boiteResRecherche"></div>').appendTo('#divSearch').css("position", "relative").css("text-align", "left");
				res = jQuery.parseJSON(msg);
				if (res.ligue.length > 0) {
					$("#boiteResRecherche").append($('<h3></h3>').text("Ligues"));
				}
				for (var a = 0; a < res.ligue.length; a++) {
					$("#boiteResRecherche").append($('<div></div>').text(res.ligue[a].nom).hover(function() {
						$(this).css("background", "#AAAAAA")
					}, function() {
						$(this).css("background", "#FFFFFF")
					}).click({
						id : res.ligue[a].id
					}, function(event) {
						document.location.href = "/zstats/accueilligue.html?ligueId=" + event.data.id;
					}));
				}
				if (res.joueur.length > 0) {
					$("#boiteResRecherche").append($('<h3></h3>').text("Joueurs"));
				}
				for (var a = 0; a < res.joueur.length; a++) {
					$("#boiteResRecherche").append($('<div></div>').text(res.joueur[a].nom).hover(function() {
						$(this).css("background", "#AAAAAA")
					}, function() {
						$(this).css("background", "#FFFFFF")
					}).click({
						id : res.joueur[a].id
					}, function(event) {
						document.location.href = "/zstats/statsjoueur.html?joueurId=" + event.data.id;
					}));
				}
			});
		});

		//		divTitre.appendChild(mIF3);

	} else {
		$('#divTitreDoc').append($('<div></div>').attr('id', 'divBoiteID').addClass("optionHautDroit").append($('<ul></ul>').attr('id', 'menuBoiteID')));
		

		$('<i class="fa fa-bars iconeFa"><i>').attr("id", "iconeBars").appendTo($('#divTitreDoc')).on("click", function() {
			$('#menuDivHaut').toggle();
		});

		$('<i class="fa fa-user iconeFa"><i>').attr("id", "iconeUser").appendTo($('#divTitreDoc')).on("click", function() {
			$('#divBoiteID').toggle();
		});
		var strUser = getJSONdePerso(usager1);
		//alert(strUser);
		usager = eval('(' + strUser + ')');

		///////////////////////////////////////////
		////  Courriel

		lienCourrielJS = document.createElement('A');
		lienCourrielJS.href = "/zadmin/messages.html";
		lienCourrielJS.id = "lienCourriel";
		imgCourrielJS = document.createElement('IMG');
		imgCourrielJS.id = "imgCourriel";
		imgCourrielJS.style.width = "32px";
		imgCourrielJS.style.height = "32px";
		imgCourrielJS.src = "/images/icones/email.png";
		lienCourrielJS.appendChild(imgCourrielJS);

		//		divTitre.appendChild(mIF3);
		$('#menuBoiteID').append($('<li></li>').append($('<div id="divSearch"></div>').append($('<img></img>').attr("src", "/images/icones/delete.png").attr("id", "iconeDelete").css({
			"width" : "24px",
			"height" : "24px"
		}).hide(), $('<input></input>').attr("type", "text").attr("id", "editSearch"), $('<img></img>').attr("src", "/images/icones/search.png").attr("id", "iconeSearch"))));
		$('#menuBoiteID').append($('<li></li>').append($(lienCourrielJS)), $('<li></li>').append(
			$('<DIV></DIV>').attr('id','affUser').html(window.tl_general_welcome + '<b>' + usager1 + '</b>' + '! </br>').append(
			$('<a></a>').text(window.tl_general_logout).on("click",function(){
			SimpleDeleteCookie("ligueId");
			SimpleDeleteCookie("userId");
			window.location.href = "/";
			})
		)
			));

		try {
			photoPortraitJS = document.createElement('IMG');
			//logoLigue.setAttribute('src','images/ligues/logo/'+getValue('ligueId')+'.jpg');
			photoPortraitJS.src = "/admin/afficheImage.php?ficId=" + usager.ficIdPortrait;
			photoPortraitJS.className = "tresPetitLogo";
			photoPortraitJS.id = "photoConnexion";

			$(photoPortraitJS).click(function() {
				document.location.href = "/zuser/monprofil.html";
			}).css("cursor", "pointer");
			$('#menuBoiteID').append($('<li></li>').append($(photoPortraitJS)));
		} catch(err) {
		}

		//////////////////////////////////////////
		////  Boîte de recherche

		$(function() {
			$('#editSearch').hide();
			$('#iconeSearch').hover(function() {
				$('#editSearch').show(400);
				$('#iconeDelete').show(400);
			});
		});

		$('#iconeDelete').click(function() {
			$('#editSearch').hide();
			$(this).hide();
			detruitNoeud('boiteResRecherche');
		});

		$('#editSearch').keyup(function(event) {

			$.ajax({
				url : "/scriptsphp/recherche.php",
				type : "POST",
				data : {
					searchString : $('#editSearch').val()
				}
			}).done(function(msg) {
				detruitNoeud('boiteResRecherche');
				$('<div id="boiteResRecherche"></div>').appendTo('#divSearch').css("position", "relative").css("text-align", "left");
				res = jQuery.parseJSON(msg);
				if (res.ligue.length > 0) {
					$("#boiteResRecherche").append($('<h3></h3>').text("Ligues"));
				}
				for (var a = 0; a < res.ligue.length; a++) {
					$("#boiteResRecherche").append($('<div></div>').text(res.ligue[a].nom).hover(function() {
						$(this).css("background", "#AAAAAA");
					}, function() {
						$(this).css("background", "#FFFFFF");
					}).click({
						id : res.ligue[a].id
					}, function(event) {
						document.location.href = "/zstats/accueilligue.html?ligueId=" + event.data.id;
					}));
				}
				if (res.joueur.length > 0) {
					$("#boiteResRecherche").append($('<h3></h3>').text("Joueurs"));
				}
				for (var a = 0; a < res.joueur.length; a++) {
					$("#boiteResRecherche").append($('<div></div>').text(res.joueur[a].nom).hover(function() {
						$(this).css("background", "#AAAAAA");
					}, function() {
						$(this).css("background", "#FFFFFF");
					}).click({
						id : res.joueur[a].id
					}, function(event) {
						document.location.href = "/zstats/statsjoueur.html?joueurId=" + event.data.id;
					}));
				}

			});

		});

		//				mLi=document.createElement('LI');
		//				menuBoiteIDJS.appendChild(mLi);
		$('#menuBoiteID').append($('<li></li>').append($(divLangueSel)));

	}// fin du else

	genereMenu_dev();
	//	barreTitreContexte();
}

function barreTitreContexte(i_menu) {
	maBarre = $('<DIV></DIV>').attr('id', 'barreTitreContexte');
	//maBarre.id="barreTitreContexte";
	//	x=document.getElementById('divGauche').parentNode;
	$('#divHaut').after($(maBarre));
	//alert($("#divHaut").innerHTML);
	//	x.insertBefore(maBarre,document.getElementById('divGauche'));
	afficheLigueId();
	if (window.Perm < 10) {//alert(window.matchMedia());
	}
	if (document.documentElement.clientWidth < 500) {
		detruitNoeud("ligueBTC");

		selChLigue = $('<SELECT></SELECT>').addClass("bouton_2").val("window.ligueId").text("...  >").on("change", function() {

			setCookie('ligueId', this.options[this.selectedIndex].value, 120);
			window.location.reload(true);
		});
//console.log("1");
		var uneString = getJSONdeLigueID(null, null);
		try {
			rJSON = eval('(' + uneString + ')');
//console.log("2");

		} catch(err) {
			alert('Le serveur éprouve quelques problêmes. Merci de réessayer plus tard.');
		}
		//  document.getElementById('format-date').innerHTML = formatDate(new Date());
//console.log("3");

		try {

			var affiche = true;
			for (var J = 0; J <= window.rJSON.Ligues.length; J++) {
				//					alert(window.rJSON.Ligues[J].cleValeur);
				affiche = true;
		//		console.log(JSON.stringify(window.rJSON.Ligues[J]));
				if (window.rJSON.Ligues[J].cleValeur == null) {
					affiche = true;
				} else {
					if (window.rJSON.Ligues[J].cleValeur.statut == "efface") {
						affiche = false;
					}

				}
	//			console.log(JSON.stringify(window.rJSON.Ligues[J].cleValeur));

				if (affiche == true) {
					opt = $('<option></option>').text(window.rJSON.Ligues[J].nomLigue).val(window.rJSON.Ligues[J].ligueId).addClass("lien");
					$(selChLigue).append($(opt));
					console.log($(opt).val());
				//		console.log(window.ligueId == $(opt).val());
					if (window.ligueId == $(opt).val()) {
						$(selChLigue).val($(opt).val());
					console.log($(opt).val());
					}
//				console.log(window.rJSON.Ligues[J].nomLigue);

					
				}//fin du if efface;
			}//Fin du for

			//	document.body.rangee.cellule.innerHTML ='yo';
		} catch(err) {

		}

		$("#divLigue").append($(selChLigue));
	} else {

		boutChLigue = $('<div></div>').addClass("bouton_2").text("Changer de ligue  >").on("click", function() {
			/*window.location.href = "/listeligues.html" + "?code=10";*/
			forceSelectionLigue();
		});
		$("#divLigue").append($(boutChLigue));
	}

	barreNavJS = $('<DIV></DIV>').attr('id', 'barreNav');
	//maBarre.id="barreTitreContexte";
	//	x=document.getElementById('divGauche').parentNode;
	$('#barreTitreContexte').after($(barreNavJS));

	//barreNavJS=document.createElement('DIV');
	//barreNavJS.id="barreNav";
	//x=document.getElementById('divGauche').parentNode;
	//x.insertBefore(barreNavJS,document.getElementById('divGauche'));

	selSaisonJS = document.createElement('div');
	selSaisonJS.id = 'divSelSaison';

	genereLiensContext(i_menu);
	barreNavJS.firstChild.appendChild(selSaisonJS);

	var frac = 100 / ($(barreNavJS).first().children().length + 1);
	for (var a = 0; a < $(barreNavJS).first().children().length; a++) {//barreNavJS.firstChild.childNodes[a].style.width=frac+'%';
		//barreNavJS.firstChild.childNodes[a].style.width=10+'%';
		/*alert(barreNavJS.firstChild.childNodes[a].style.width);*/
	}
	selSaisonJS.style.width = 30 + '%';
	selSaisonJS.style.maxWidth = 30 + '%';
}

function peupleDivBas() {

	divBasJS = document.getElementById('divBas');
	if (divBasJS == null) {
		divBasJS = document.createElement('DIV');
		divBasJS.id = 'divBas';
		document.getElementsByTagName('body')[0].appendChild(divBasJS);
	}

	menuAproposJS = document.createElement('div');
	menuAproposJS.id = 'menuApropos';
	tApropos = document.createElement('A');
	tContactez = document.createElement('A');
	tApropos.innerHTML = "Documentation";
	tApropos.href = "/zdoc/demarrer.html";
	tContactez.innerHTML = "Contactez-nous";
	tContactez.href = "mailto:info" + "@" + "syncstats.com";
	menuAproposJS.appendChild(tApropos);
	menuAproposJS.appendChild(document.createTextNode('|'));
	menuAproposJS.appendChild(tContactez);
	divBasJS.appendChild(menuAproposJS);

	menuSuivezJS = document.createElement('div');
	menuSuivezJS.id = 'menuSuivez';
	tSuivez = document.createElement('A');
	tSuivez.innerHTML = "Suivez-nous sur FB!";
	tSuivez.href = "https://www.facebook.com/pages/SyncStats/236314676470964";
	menuSuivezJS.appendChild(tSuivez);
	divBasJS.appendChild(menuSuivezJS);

	mIF2 = document.createElement('DIV');
	mIF2.id = "fb-root";

	mIF3 = document.createElement('DIV');
	mIF3.setAttribute('class', "fb-like");
	mIF3.setAttribute('data-href', "http://www.syncstats.com");
	mIF3.setAttribute('data-send', 'true');
	mIF3.setAttribute('data-layout', 'button_count');
	mIF3.setAttribute('data-width', '55');
	mIF3.setAttribute('data-show-faces', 'false');
	mIF3.setAttribute('style', "position:absolute;top:30px;right:120px;width:60px;");

	menuSuivezJS.appendChild(mIF3);
	//	document.getElementsByTagName('body')[0].insertBefore(mIF2, document.getElementsByTagName('body')[0].firstChild);

	menuDiscJS = document.createElement('div');
	menuDiscJS.id = 'menuDisc';
	imgDiscJS = document.createElement('IMG');
	imgDiscJS.id = "imgDisc";
	imgDiscJS.src = "/images/logoFondNoir.png";
	menuDiscJS.appendChild(imgDiscJS);
	if (document.documentElement.clientWidth >= 500) {
		$('<script async></script>').attr("src", "//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js").appendTo(divBasJS);
		$('<ins></ins>').attr("class", "adsbygoogle").attr("style", "display:inline-block;width:728px;height:90px").attr("data-ad-client", "ca-pub-2263794877114969").attr("data-ad-slot", "8589452732").appendTo(divBasJS);
		( adsbygoogle = window.adsbygoogle || []).push({});
	}

	divBasJS.appendChild(menuDiscJS);

	$(divBasJS).append($('<a></a>').attr('href', '#').click(function() {
		window.open('https://www.sitelock.com/verify.php?site=syncstats.com', 'SiteLock', 'width=600,height=600,left=160,top=170');
	}).append($('<img></img>').addClass("img-responsive").attr('title', 'SiteLock').attr('src', '//shield.sitelock.com/shield/syncstats.com')));

}

/*
 function genereMenu(position) {
 divMenu = document.createElement('DIV');
 m2body = document.getElementsByTagName("body")[0];
 //	m2body = document.getElementById("mbody");
 m2body.appendChild(divMenu);
 divMenu.style.width = '100%';
 divMenu.style.height = '30px';
 divMenu.style.cssFloat = 'left';
 divMenu.id = 'divMenu';
 divMenu.style.font = 'small-caps bold small/24px "Times New Roman", serif';
 divMenu.style.letterSpacing = '1px';
 divMenu.style.textAlign = 'center';
 /*	divMenu.style.border='1px solid';*/
/*	divMenu.style.mozBorderRadius = '15px';
 divMenu.style.borderRadius = '15px';

 menu2_b = document.createElement('UL');
 divMenu.appendChild(menu2_b);
 menu2_b.id = 'menu2_b';

 li1 = document.createElement('LI');
 menu2_b.appendChild(li1);
 a1 = document.createElement('A');
 li1.appendChild(a1);
 a1.setAttribute('href', '/index.html');
 a1.innerHTML = 'Page d\'accueil';

 li4 = document.createElement('LI');
 menu2_b.appendChild(li4);
 a4 = document.createElement('A');
 li4.appendChild(a4);
 a4.setAttribute('href', '/login.html');
 a4.innerHTML = 'Mon profil';

 li2 = document.createElement('LI');
 menu2_b.appendChild(li2);
 a2 = document.createElement('A');
 li2.appendChild(a2);
 //	a2.setAttribute('href','/listeligues.html');
 a2.href = '/statistiques.html';
 a2.innerHTML = 'Statistiques';

 li3 = document.createElement('LI');
 menu2_b.appendChild(li3);
 a3 = document.createElement('A');
 li3.appendChild(a3);
 a3.setAttribute('href', '/faq.html');
 a3.innerHTML = 'FAQ';

 li5 = document.createElement('LI');
 menu2_b.appendChild(li5);
 a5 = document.createElement('A');
 li5.appendChild(a5);
 a5.setAttribute('href', '/zoneadmin.html');
 a5.innerHTML = 'Zone Admin';

 switch(position%10) {
 case 1:
 a1.className = 'actif_b';
 break;
 case 2:
 a2.className = 'actif_b';

 break;
 case 3:
 a3.className = 'actif_b';
 break;
 case 4:
 a4.className = 'actif_b';
 break;
 case 5:
 a5.className = 'actif_b';
 break;

 default:
 break;
 }
 divDate = document.createElement('DIV');
 menu2_b.appendChild(divDate);
 divDate.innerHTML = formatDate(new Date());
 divDate.className = 'date';
 }*/

function trouveIndiceMenu(menu_id) {
	var j = 0
	while (j < menus.length) {
		if (menus[j].id == menu_id)
			return j;
		j++;
	}
	return -1;
}

function genereMenu_dev() {
	divHautJS = document.getElementById("divTitreDoc");

	if (document.getElementById("menuDivHaut") == null) {
		divMenuHautJS = document.createElement("DIV");
		divMenuHautJS.id = "menuDivHaut";
		divHautJS.appendChild(divMenuHautJS);
		//	m2body = document.getElementById("mbody");
		//	divMenuHautJS.appendChild(divMenu);
		//	divMenuHautJS.insertBefore(divMenu, divMenuHautJS.firstChild.nextSibling);

		menu_n1 = document.createElement('UL');
		divMenuHautJS.appendChild(menu_n1);
		menu_n1.id = 'menu';
/*
		for (var k = 0; k < hierarchie.length; k++) {
			i_n1 = document.createElement('LI');
			menu_n1.appendChild(i_n1);
			i_n1.id = menus[hierarchie[k][0]].id;
			a_n1 = document.createElement('A');
			a_n1.href = menus[hierarchie[k][0]].lien;
			if (window.langue == "fr")
				a_n1.innerHTML = menus[hierarchie[k][0]].iH_fr;
			else
				a_n1.innerHTML = menus[hierarchie[k][0]].iH_en;

			i_n1.appendChild(a_n1);
			if (hierarchie[k].length > 1) {
				ul_n2 = document.createElement('UL');
				i_n1.appendChild(ul_n2);
				for (var l = 1; l < hierarchie[k].length; l++) {
					i_n2 = document.createElement('LI');
					ul_n2.appendChild(i_n2);
					i_n2.id = menus[hierarchie[k][l]].id;
					a_n2 = document.createElement('A');
					a_n2.href = menus[hierarchie[k][l]].lien;
					i_n2.appendChild(a_n2);
					if (window.langue == "fr")
						a_n2.innerHTML = menus[hierarchie[k][l]].iH_fr;
					else
						a_n2.innerHTML = menus[hierarchie[k][l]].iH_en;

				}

			}

		}// Fin du for hierarchie */
	}
}

function getLienPere(i_menu) {
	i1 = -1;
	i2 = -1;
	for ( a = 0; a < hierarchie.length; a++) {
		for ( b = 0; b < hierarchie[a].length; b++) {
			if (hierarchie[a][b] == i_menu) {
				i1 = a;
				i2 = b;
				return hierarchie[a][0];
			}
		}
	}
	return 0;
}

function getLiensFreres(i_menu) {
	i1 = -1;
	i2 = -1;
	ret = new Array();
	for ( a = 0; a < hierarchie.length; a++) {
		for ( b = 0; b < hierarchie[a].length; b++) {
			if (hierarchie[a][b] == i_menu) {
				i1 = a;
				i2 = b;
				try {
					for ( c = 0; c < hierarchie[a].length; c++) {
						ret[c] = hierarchie[a][c];
					}
				} catch(err) {
					ret = [];
				}
				return ret;
			}
		}
	}
	if (menus[i_menu].liensContexte != undefined) {
		return menus[i_menu].liensContexte;
	}
	return [];
}

function genereLiensContext(i_menu) {
	barreNavJS = document.getElementById("barreNav");
	/*
	 titreLC = document.createElement('H1');
	 if (window.langue == "fr")
	 titreLC.innerHTML = menus[getLienPere(i_menu)].iH_fr;
	 else
	 titreLC.innerHTML = menus[getLienPere(i_menu)].iH_en;
	 divGaucheJS.appendChild(titreLC);
	 */
	liens = getLiensFreres(i_menu);
	listeLien = document.createElement('UL');

	for ( a = 1; a < liens.length; a++) {
		li_lc = document.createElement('LI');
		a_lc = document.createElement('A');
		if (window.langue == "fr")
			a_lc.innerHTML = menus[liens[a]].iH_fr;
		else
			a_lc.innerHTML = menus[liens[a]].iH_en;

		a_lc.href = menus[liens[a]].lien;
		if (liens[a] == i_menu) {
			a_lc.className = "actif";
			//			li_lc.parentNode.className="actif";

			if (window.langue == "fr")
				a_lc.innerHTML = menus[liens[a]].iH_fr;
			else
				a_lc.innerHTML = menus[liens[a]].iH_en;
		}
		li_lc.appendChild(a_lc);
		listeLien.appendChild(li_lc);
	}

	if (barreNavJS != null) {
		barreNavJS.appendChild(listeLien);
	}

	//strLien=window.location.href.split('?')[0].split('/')[3];//prévenir loop sur listeligue
	//alert(strLien);
	//if(strLien!="listeligues.html")
	//	afficheLigueId();
	//	genLiensAmis(i_menu);

}

function genLiensAmis(i_menu) {
	divGaucheJS = document.getElementById("divGauche");

	if (menus[i_menu].liensAmis != undefined) {

		titreLC = document.createElement('H1');
		titreLC.innerHTML = window.tl_general_liensUtiles;
		divGaucheJS.appendChild(titreLC);
		listeLien = document.createElement('UL');

		for ( a = 0; a < menus[i_menu].liensAmis.length; a++) {
			li_lc = document.createElement('LI');
			a_lc = document.createElement('A');
			if (window.langue == "fr")
				a_lc.innerHTML = menus[menus[i_menu].liensAmis[a]].iH_fr;
			else
				a_lc.innerHTML = menus[menus[i_menu].liensAmis[a]].iH_en;
			a_lc.href = menus[menus[i_menu].liensAmis[a]].lien;
			li_lc.appendChild(a_lc);
			listeLien.appendChild(li_lc);
		}
		divGaucheJS.appendChild(listeLien);
	}
	//strLien=window.location.href.split('?')[0].split('/')[3];//prévenir loop sur listeligue
	//alert(strLien);
	//if(strLien!="listeligues.html")

}

function genTableau(jsObject) {
	mParent = document.getElementById(jsObject.parentId);
	tab = document.createElement('TABLE');
	tab.id = jsObject.id;
	tab.border = "0";
	tab.style.borderWidth = "0";
	tab.className = "c_tableau";
	mParent.appendChild(tab);
	tbody = document.createElement('TBODY');
	tbody.id = jsObject.id + "_tbody";
	tab.appendChild(tbody);
	rTitre = document.createElement('TR');
	rTitre.className = "titreTableau";
	tdTitre = document.createElement('TD');
	tdTitre.className = "titreTableau";
	tdTitre.innerHTML = jsObject.titre;
	tdTitre.id = jsObject.id + "_tdTitre";
	tdTitre.colSpan = "100";
	rTitre.appendChild(tdTitre);
	tbody.appendChild(rTitre);

	rOpt = document.createElement('TR');
	//	rOpt.className = "optionTableau";
	tdOpt = document.createElement('TD')
	tdOpt.id = jsObject.id + "_tdOpt";
	//	tdOpt.className = "optionTableau";
	tdOpt.colSpan = "100";
	divOpt = document.createElement('DIV');
	divOpt.className = "optionTableau";
	divOpt.id = jsObject.id + "_divOpt";
	tdOpt.appendChild(divOpt);
	rOpt.appendChild(tdOpt);
	tbody.appendChild(rOpt);

	rangTitre = document.createElement('TR');
	rangTitre.id = "rangeeTitre";
	rangTitre.className = "rangeeTitre";
	tbody.appendChild(rangTitre);
	for ( a = 0; a < jsObject.rangeeTitre.length; a++) {
		tdi = document.createElement('TD');
		tdi.innerHTML = jsObject.rangeeTitre[a];
		rangTitre.appendChild(tdi);
		tdi.className = "rangeeTitre";
		tdi.id = "cellTitre_" + a;
	}

}

function genBoiteContexte(id) {
	mParent = document.getElementById('divDroite');
	div1 = document.createElement('DIV');
	div1.className = 'divBoiteContexte';
	div1.id = 'dBC_' + id;
	mParent.appendChild(div1);
}

function genereBoites() {
	//	(ordre de boite, boiteId sur BD).
	//for(a=0;a<4;a++)
	genBoiteContexte(1);
	genBoiteContexte(2);
	genBoiteContexte(3);
	genBoiteContexte(4);
	genBoiteContexte(5);
	if (getCookie('ligueId') == 86)
		faireBoite(1, 10);
	else
		faireBoite(1, 11);
	//	genBoiteContexte(3);
	//	genBoiteContexte(4);
	//genBoiteContexte(5);
	//genBoiteContexte(6);

	//faireBoite(1, 3);
	//	faireBoite(2, 2);
	//if(window.ligueId==86){
	//	faireBoite(1, 10);
	//}
	//else{
	//	faireBoite(1, 9);
	//}
	faireBoite(2, 7);
	/*	if(getCookie('ligueId')!=null)
	 faireBoite(5,6);
	 else
	 faireBoite(5,1);
	 if(getCookie('userId')==null)
	 faireBoite(1,7);
	 else
	 faireBoite(1,3);
	 faireBoite(4, 5);*/
	faireBoite(3, 5);
	faireBoite(4, 1);
	faireBoite(5, 6);
	//faireBoiteTest(1);
	$('<script async></script>').attr("src", "//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js").appendTo('#divDroite');
	$('<ins></ins>').attr("class", "adsbygoogle").attr("style", "display:inline-block;width:300px;height:250px").attr("data-ad-client", "ca-pub-2263794877114969").attr("data-ad-slot", "9575808332").appendTo('#divDroite');
	( adsbygoogle = window.adsbygoogle || []).push({});

}

/*
function faireBoiteTest(a) {

var requete_ajax = new XMLHttpRequest();

var url = "/scriptsphp/infoMatch2JSON.php";

var strMatch = "match=" + 0;
var strLigue = "ligueId=" + window.ligueId;
params = strMatch + '&' + strLigue;
url = url + "?" + params;

//		alert(url+'?'+param);
//requete_ajax.send(null);
//	return requete_ajax.responseText;

requete_ajax.open('POST', url, true);
//				requete_ajax.send(null);

requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
requete_ajax.setRequestHeader("Content-length", params.length);
requete_ajax.setRequestHeader("Connection", "close");

requete_ajax.send(params);

requete_ajax.onreadystatechange = (function(a) {//Call a function when the state changes.

return function(){
if (requete_ajax.readyState == 4 && requete_ajax.status == 200) {

infoMatch = eval('(' + requete_ajax.responseText + ')');

//
mParent = document.getElementById('dBC_' + a);
divLigne = document.createElement('DIV');
divLigne.id = "ligneCentrale2";
mParent.style.textAlign="center";
divBloc = document.createElement('DIV');
divBloc.style.display="block";
divBloc.style.textAlign="center";

divLigne.style.textAlign="center";
divLigne.style.margin="auto";
divLigne.style.display="inline";

pDernMatch = document.createElement('P');
pDernMatch.innerHTML=infoMatch.ligueNom;
pDernMatch.style.textAlign="center";
pDernMatch.style.display="block";
pDateMatch = document.createElement('P');
pDateMatch.innerHTML=infoMatch.date;
pDateMatch.style.textAlign="center";
pDateMatch.style.display="block";

divLogoDom = document.createElement('DIV');
//divLogoDom.id = "logoDom";
//divLogoDom.className = "petitLogo";

divLogoVis = document.createElement('DIV');
//divLogoVis.id = "logoVis";
//divLogoVis.className = "petitLogo";
divLogoDom.style.display="inline";
divLogoVis.style.display="inline";

lienImgDomJS = document.createElement('A');
lienImgDomJS.id = "lienImgDom";
lienImgDomJS.href = "/zstats/statsequipe.html?equipeId=" + infoMatch.equipeIdDom+"&ligueId="+infoMatch.ligueId;
divImgDom = document.createElement('IMG');
//divImgDom.id = "logoEquipeDom";
//divImgDom.className = "petitLogo";

divImgDom.style.height="48px";
divImgDom.style.width="auto";
divImgDom.style.maxHeight="48px";
divImgDom.style.maxWidth="48px";
lienImgVisJS = document.createElement('A');
lienImgVisJS.id = "lienImgVis";
lienImgVisJS.href = "/zstats/statsequipe.html?equipeId=" + infoMatch.equipeIdVis+"&ligueId="+infoMatch.ligueId;
divImgVis = document.createElement('IMG');
//divImgVis.id = "logoEquipeVis";
//divImgVis.className = "petitLogo";

divImgVis.style.height="48px";
divImgVis.style.width="auto";
divImgVis.style.maxHeight="48px";
divImgVis.style.maxWidth="48px";
divScore = document.createElement('DIV');
divScore.style.display="inline";
divScore.style.margin="auto";
divPDom = document.createElement('P');
divPDom.id = "tScoreDom";
divPDom.style.fontSize="large";
divPDom.style.fontWeight="bold";
//divPDom.className = "grosTexte";
divPDom.style.display="inline";
divPVis = document.createElement('P');
divPVis.id = "tScoreVis";
//divPVis.className = "grosTexte";
divPVis.style.display="inline";
divPVis.style.fontSize="large";
divPVis.style.fontWeight="bold";
pDash = document.createElement('P');
//pDash.className = "grosTexte";
pDash.innerHTML = "-";
pDash.style.display="inline";
pDash.style.fontSize="large";
pDash.style.fontWeight="bold";
mParent.appendChild(pDernMatch);
mParent.appendChild(pDateMatch);
mParent.appendChild(divBloc);

divLogoDom.appendChild(lienImgDomJS);
lienImgDomJS.appendChild(divImgDom);
divLigne.appendChild(divLogoDom);

divScore.appendChild(divPDom);
divScore.appendChild(pDash);
divScore.appendChild(divPVis);

divLigne.appendChild(divScore);
divLogoVis.appendChild(lienImgVisJS);
lienImgVisJS.appendChild(divImgVis);
divLigne.appendChild(divLogoVis);
//	lecorps = document.getElementById('mbody');
divImgDom.src = '/admin/afficheImage.php?ficId=' + infoMatch.equipeFicIdDom;
divImgVis.src = '/admin/afficheImage.php?ficId=' + infoMatch.equipeFicIdVis;

divPDom.innerHTML = infoMatch.equipeScoreDom;
divPVis.innerHTML = infoMatch.equipeScoreVis;
//	lecorps.appendChild(divLigne);
divBloc.appendChild(divLigne);
mParent.onclick=(function(match){return function(){window.location.href="/zstats/match.html?match="+match;};})(infoMatch.matchId);
}
};
})(a);
}*/
/*

function faireBoite(a) {
mParent = document.getElementById('dBC_' + a);
div1 = document.createElement('H2');
div1.innerHTML = "Essayer sans frais SyncStats. Remplissez notre formulaire et on ira à votre prochain match!";
div1.style.textAlign = "left";
div1.style.cssFloat = "right";
div1.style.width = "70%";
div1.style.zIndex="10";

titre1 = document.createElement('IMG');
titre1.className = 'imgLogoSS';
divLogoJS = document.createElement('DIV');
divLogoJS.className="logoSS";
//							div1.style.position="absolute";
//							divLogoJS.style.position="absolute";

//							div1.style.top="-100px";
divLogoJS.appendChild(titre1);
titre1.src="/images/logoSeul.png";
divLogoJS.style.zIndex="1";
divLogoJS.style.cssFloat="left";
divLogoJS.style.width="30%";
divLogoJS.style.margin="0";
divLogoJS.style.marginTop="10px";
titre1.style.zIndex="1";
divLogoJS.style.height="auto";
mParent.appendChild(divLogoJS);

mParent.appendChild(div1);
mParent.style.cursor="pointer";
mParent.onclick=function(){window.location.href="http://www.doodle.com/kq58ask849bgki3w"};
}

/*
function faireBoiteMerci(a) {
mParent = document.getElementById('dBC_' + a);
div1 = document.createElement('H2');
div1.innerHTML = "À tous les gens qui nous suivent";
div1.style.textAlign = "center";
div1.style.cssFloat = "none";
div2 = document.createElement('H1');
div2.innerHTML = "Merci";
div2.style.textAlign = "center";
div2.style.cssFloat = "none";
mParent.appendChild(div1);
mParent.appendChild(div2);
a1 = document.createElement('P');
a1.innerHTML = "Votre confiance, vos commentaires, ";
mParent.appendChild(a1);
a1.style.textAlign = "center";
}*/

/*
function faireBoitePromo(a) {

mParent = document.getElementById('dBC_' + a);
divText = document.createElement('P');
divText.innerHTML = "Laisser-nous un message...";
div1 = document.createElement('INPUT');
div1.type="TEXT";
div1.id="boiteTexte";
div1.style.width="90%";
div1.style.height="70%";
div2 = document.createElement('INPUT');
div2.type="BUTTON";
div2.id="OkBoiteTexte";
div2.value=window.tl_bouton_OK;
div2.onclick=function(){envoieMessage();};
mParent.appendChild(divText);
mParent.appendChild(div1);
mParent.appendChild(div2);

function envoieMessage()
{

var requete_ajax = new XMLHttpRequest();

var url = "/scriptsphp/sendMessage.php";
var exp = getCookie("userId")==null?"Constantin":getCookie("userId");
var params = "expediteur="+exp+"&recepteur="+"Kim"+"&titre="+""+"&corps="+document.getElementById("boiteTexte").value;
requete_ajax.open("POST", url, true);

//Send the proper header information along with the request
requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
requete_ajax.setRequestHeader("Content-length", params.length);
requete_ajax.setRequestHeader("Connection", "close");

requete_ajax.onreadystatechange = function() {//Call a function when the state changes.
if(requete_ajax.readyState == 4 && requete_ajax.status == 200) {

alert(requete_ajax.responseText);
document.getElementById("boiteTexte").value="Merci!";
document.getElementById("boiteTexte").readonly=true;
document.getElementById("OkBoiteTexte").onclick=function(){};

}
}
requete_ajax.send(params);

}

*/
/*	div2 = document.createElement('H2');
div2.innerHTML="Jusqu'en 2013!";
div2.style.textAlign="right";
div2.style.cssFloat="none";*/
/*mParent.appendChild(divText);
mParent.appendChild(div1);

/*
mParent = document.getElementById('dBC_' + a);
divText = document.createElement('P');
divText.innerHTML = "Envie de webdiffuser vos match?";
div1 = document.createElement('H2');
div1.innerHTML = "<img src='/admin/afficheImage.php?ficId=74' style='height:90px' />"
div1.style.textAlign = "center";
div1.style.cssFloat = "none";
/*	div2 = document.createElement('H2');
div2.innerHTML="Jusqu'en 2013!";
div2.style.textAlign="right";
div2.style.cssFloat="none";*/
/*mParent.appendChild(divText);
mParent.appendChild(div1);
a1 = document.createElement('A');
a1.href = "http://www.facebook.com/pages/WebRadiosport/165025246927690";
//a1.style.color = "#8DBB22";
a1.innerHTML = "Venez nous voir sur FaceBook!";
a1.style.textAlign = "center";
mParent.appendChild(a1);*/
//}
/*
 function faireBoiteTest(a){
 mParent = document.getElementById('dBC_' + a);
 $(mParent).append($('<img/>').addClass('logoSS').css('height','50%').css('float','left').attr('src','/images/cellEtPucks.png'));
 div1 = document.createElement('H2');
 div1.innerHTML = window.tl_bc_votrePub;
 div1.style.textAlign = "center";
 div1.style.cssFloat = "none";
 div2 = document.createElement('H1');
 div2.innerHTML = window.tl_bc_ici;
 div2.style.textAlign = "center";
 div2.style.cssFloat = "none";
 mParent.appendChild(div1);
 mParent.appendChild(div2);
 a1 = document.createElement('A');
 a1.href = "mailto:info@syncstats.com?subject=Pub SyncStats";
 a1.style.color = "#8DBB22";
 a1.innerHTML = window.tl_general_contact;
 mParent.appendChild(a1);
 a1.style.textAlign = "center";

 }*/
/*
 function faireBoiteVideo(a) {
 mParent = document.getElementById('dBC_' + a);
 mObject = document.createElement('OBJECT');
 mObject.id = "player1";
 mObject.type = "application/x-shockwave-flash";
 mObject.data = "/videos/player_flv_maxi.swf";
 mObject.width = "280";
 mObject.height = "160";

 mNoScript = document.createElement('NOSCRIPT');
 mA = document.createElement('A');
 mA.href = "http://www.dvdvideosoft.com/products/dvd/Free-YouTube-to-MP3-Converter.htm";
 mA.innerHTML = "youtube to mp3 converter";
 mParam1 = document.createElement('PARAM');
 mParam1.name = "movie";
 mParam1.value = "/videos/player_flv_maxi.swf";
 mParam2 = document.createElement('PARAM');
 mParam2.name = "allowFullScreen";
 mParam2.value = "true";
 mParam3 = document.createElement('PARAM');
 mParam3.name = "FlashVars";
 mParam3.value = "configxml=/videos/demo_but_syncstats.xml";
 mNoScript.appendChild(mA);
 mObject.appendChild(mNoScript);
 mObject.appendChild(mParam1);
 mObject.appendChild(mParam2);
 mObject.appendChild(mParam3);
 mParent.appendChild(mObject);
 }
 */
function faireBoite(a, id) {
	var requete_ajax = new XMLHttpRequest();

	var url = "/scriptsphp/getBoiteContexte.php";
	var strBoiteId = "boiteId=" + id;

	params = strBoiteId;
	//= url+"?"+params;
	//alert(params);
	//requete_ajax.send(null);
	//	return requete_ajax.responseText;

	requete_ajax.open('POST', url, true);
	//				requete_ajax.send(null);

	requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	requete_ajax.setRequestHeader("Content-length", params.length);
	requete_ajax.setRequestHeader("Connection", "close");

	requete_ajax.onreadystatechange = function() {//Call a function when the state changes.
		if (requete_ajax.readyState == 4 && requete_ajax.status == 200) {
			//		if(verifiePermission())
			//			alert(requete_ajax.responseText);

			boite = eval('(' + requete_ajax.responseText + ')');
			eval(boite.corps);

		}
	}
	requete_ajax.send(params);
	//				alert(JSON.stringify(listeMatch));
	//	return vecMAV;
	//}

}

function genereSousMenu(position) {
	divMenu = document.createElement('DIV');
	m2body = document.getElementsByTagName("body")[0];
	m2body.appendChild(divMenu);
	//			divMenu.style.width = '100%';
	//			divMenu.style.cssFloat = 'left';
	divMenu.id = 'divMenu2';
	//divMenu.style.font='small-caps bold small/24px "Times New Roman", serif';
	//divMenu.style.letterSpacing='1px';
	//divMenu.style.textAlign='center';
	divMenu.style.width = '100%';
	divMenu.style.height = '30px';
	divMenu.style.cssFloat = 'left';
	divMenu.style.font = 'small-caps bold small/24px "Times New Roman", serif';
	divMenu.style.letterSpacing = '1px';
	divMenu.style.textAlign = 'center';
	/*	divMenu.style.border='1px solid';*/
	divMenu.style.mozBorderRadius = '15px';
	divMenu.style.borderRadius = '15px';
	menu2_b = document.createElement('UL');
	divMenu.appendChild(menu2_b);
	menu2_b.id = 'menu2_1_b';
	li1 = document.createElement('LI');
	menu2_b.appendChild(li1);
	b1 = document.createElement('A');
	li1.appendChild(b1);
	b1.onclick = function() {
		afficheSelection(1);
	};
	//b1.setAttribute('href', '/index.html');
	b1.innerHTML = 'Horaire';
	li2 = document.createElement('LI');
	menu2_b.appendChild(li2);
	b2 = document.createElement('A');
	li2.appendChild(b2);
	b2.onclick = function() {
		afficheSelection(2);
	};
	//b2.setAttribute('href', '/listeligues.html');
	b2.innerHTML = 'Résultats';
	li3 = document.createElement('LI');
	menu2_b.appendChild(li3);
	b3 = document.createElement('A');
	li3.appendChild(b3);
	b3.onclick = function() {
		afficheSelection(3);
	};
	//				b3.setAttribute('href', '/meneurs.html');
	b3.innerHTML = 'Meneurs';
	li4 = document.createElement('LI');
	menu2_b.appendChild(li4);
	b4 = document.createElement('A');
	li4.appendChild(b4);
	b4.innerHTML = 'Classement';
	b4.onclick = function() {
		afficheSelection(4);
	};
	li5 = document.createElement('LI');
	menu2_b.appendChild(li5);

	b5 = document.createElement('A');
	li5.appendChild(b5);
	b5.innerHTML = 'Changer de ligue';
	b5.onclick = function() {
		afficheSelection(5);
	};
	//				b4.setAttribute('href', '/classement.html');
	switch(position) {
	case 1:
		b1.className = 'actif_b';
		break;
	case 2:
		b2.className = 'actif_b';
		break;
	case 3:
		b3.className = 'actif_b';
		break;
	case 4:
		b4.className = 'actif_b';
		break;
	default:
	}// Fin Switch

}// Fin genereSousMenu

function afficheLigueId() {
	//	ligueId = null;
	if (getValue('ligueId') != "") {
		ligueId = getValue('ligueId');
	}
	if (getCookie('ligueId') != null) {
		ligueId = getCookie('ligueId');
	} else {
		ligueId = null
	};
	/*if (getValue('ligueId') == "" || window.ligueId == null || window.ligueId == undefined) {
	 window.location.href = "/listeligues.html" + "?code=10";
	 }//Code 10: origine de statistique
	 else {
	 ligueId = getValue('ligueId');
	 }
	 }*/

	divLigue = document.createElement('DIV');
	m2body = document.getElementById('barreTitreContexte');
	divLigue.id = 'divLigue';
	texte1 = document.createElement('H1');
	texte1.id = "ligueBTC";
	divLigue.appendChild(texte1);
	m2body.appendChild(divLigue);

	var requete_ajax = new XMLHttpRequest();

	var url = "/stats2/Ligues2JSON.php";
	//	alert(url);
	params = "";
	//alert(params);
	requete_ajax.open('POST', url, true);

	requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	requete_ajax.setRequestHeader("Content-length", params.length);
	requete_ajax.setRequestHeader("Connection", "close");

	requete_ajax.onreadystatechange = function() {//Call a function when the state changes.
		if (requete_ajax.readyState == 4 && requete_ajax.status == 200) {
			//					alert(requete_ajax.responseText);
			//			window.location.reload();
			try {
				var locJSON = eval('(' + requete_ajax.responseText + ')');
			} catch(err) {
				alert('Le serveur éprouve quelques problêmes. Merci de réessayer plus tard.');
			}

			maLigue = null;
			monNomLigue = null;
			for (var J = 0; J < locJSON.Ligues.length; J = J + 1) {
				if (parseInt(locJSON.Ligues[J].ligueId) == window.ligueId) {
					maLigue = locJSON.Ligues[J];

					monNomLigue = locJSON.Ligues[J].nomLigue;
					setMenusLigue(maLigue);

				}
			}
			if(document.getElementById('ligueBTC')!=null){
			document.getElementById('ligueBTC').innerHTML = maLigue == null ? "Ligue / Tournoi de Hockey" : monNomLigue;}

		}
		//		else{alert("Petit problème de développement");}
	};
	requete_ajax.send(null);

}// Fin genereNiveau2

function setMenusLigue(maLigue) {
	try {
		//alert("Hey");
		if (maLigue != undefined) {
			strartIMenu = window.liens.length;

			//alert(JSON.stringify(window.maLigue.cleValeur.siteWeb.menu));
			menuSiteWeb = Array();
			menuSiteWeb = maLigue.cleValeur.siteWeb.menu;
			//alert("Hi");
			for (var a = 0; a < menuSiteWeb.length; a++) {

				li_lc = document.createElement('LI');
				a_lc = document.createElement('A');

				a_lc.innerHTML = menuSiteWeb[a].iH;

				a_lc.href = menuSiteWeb[a].lien;
				/*if (strartIMenu+a == i_menu) {
				 a_lc.className="actif";
				 //	alert("Ho");
				 //			li_lc.parentNode.className="actif";
				 a_lc.innerHTML = menuSiteWeb[a].iH;
				 }*/
				window.liens[window.liens.length] = window.menus.length;
				window.menus[window.menus.length] = menuSiteWeb[a];

				//alert(JSON.stringify(window.liens)+" "+JSON.stringify(window.menus[window.liens[window.liens.length-1]]));
				$(listeLien).append($(li_lc).append($(a_lc)));
				if (((window.location.href.split("syncstats.com"/*"host"*/)[1]).split("?")[0]).localeCompare(menuSiteWeb[a].lien) == 0) {
					//alert($(li_lc).html());
					$('#barreNav li a').removeClass("actif");
					$(a_lc).addClass("actif");
				}
			}

		}

	} catch(err) {/*alert(err.message);*/
	}

}

function afficheLigueIdAvecCode(code) {
	//	ligueId = null;
	if (getValue('ligueId') != "") {
		ligueId = getValue('ligueId');
	}
	if (getCookie('ligueId') != null) {
		ligueId = getCookie('ligueId');
	} else {
		if (getValue('ligueId') == "" || window.ligueId == null || window.ligueId == undefined) {
			window.location.href = "/listeligues.html" + "?code=" + code;
		}//Code 10: origine de statistique
		else {
			ligueId = getValue('ligueId');
		}
	}
	var uneString = getJSONdeLigueID();
	try {
		var rJSON = eval('(' + uneString + ')');
	} catch(err) {
		alert('Le serveur éprouve quelques problêmes. Merci de réessayer plus tard.');
	}

	maLigue = null;
	for ( J = 0; J < rJSON.Ligues.length; J = J + 1) {
		if (rJSON.Ligues[J].ligueId == ligueId) {
			maLigue = rJSON.Ligues[J].nomLigue;
		}
	}

	divLigue = document.createElement('DIV');
	m2body = document.getElementById('mbody');
	divLigue.style.width = '100%';
	divLigue.style.height = '50px';
	divLigue.style.cssFloat = 'left';
	divLigue.id = 'divLigue';
	divLigue.style.font = 'small-caps bold small/24px "Times New Roman", serif';
	divLigue.style.letterSpacing = '1px';
	divLigue.style.textAlign = 'left';
	divLigue.style.margin = '5px';

	texte1 = document.createElement('P');
	texte1.innerHTML = window.tl_general_ligueAct + ":   ";
	texte1.style.cssFloat = 'left';

	lien1 = document.createElement('P');
	lien1.innerHTML = maLigue + "   |  ";
	lien1.style.cssFloat = 'left';

	texte2 = document.createElement('P');
	texte2.innerHTML = "  ";
	texte2.style.cssFloat = 'left';

	lien2 = document.createElement('A');
	lien2.innerHTML = "  " + window.tl_general_ligueChange;
	lien2.href = "/listeligues.html" + "?code=" + code;
	lien2.style.cssFloat = 'left';
	lien2.style.marginLeft = '10px';

	divLigue.appendChild(texte1);
	texte2.appendChild(lien2);
	divLigue.appendChild(texte2);

	divLigue.appendChild(lien1);

	//				referenceNode=document.getElementById('divLigue');
	m2body.appendChild(divLigue);
	//				referenceNode.parentNode.insertBefore( divLigue, referenceNode.nextSibling );

	//				b4.setAttributetAttribute('href', '/classement.html');

}// Affiche Ligue Avec Code

function afficheNonPermis(divParent) {

	videNoeud(divParent);
	divC = document.getElementById(divParent);

	pTexte = document.createElement('P');
	pTexte.style.marginTop = "20px";
	pTexte.innerHTML = window.tl_general_nonPermis;
	divC.appendChild(pTexte);
}

function enveloppeTable(tableId, noPage, nbParPage) {
	maTable = document.getElementById(tableId);
	lesLignes = maTable.firstChild.childNodes;
	limMin = noPage * nbParPage;
	limMax = (noPage + 1) * nbParPage - 1;
	cpt = 0;
	for (var a = 0; a < lesLignes.length; a++) {
		if (lesLignes[a].nodeName == 'TR' && (lesLignes[a].className == 'lignePaire' || lesLignes[a].className == 'ligneImpaire' || lesLignes[a].className == 'ligneCache')) {
			if (cpt >= limMin && cpt <= limMax) {
				cpt % 2 == 0 ? lesLignes[a].className = 'lignePaire' : lesLignes[a].className = 'ligneImpaire';
			} else {
				lesLignes[a].className = "ligneCache";
			}
			cpt++;
		}

	}
	videNoeud(tableId + "_divOpt");
	divOpt = document.getElementById(tableId + "_divOpt");
	divNav = document.createElement('DIV');
	divNav.style.display = "block";
	divNav.style.position = 'relative';
	divNav.style.left = '45%';
	divNav.style.width = '100%';
	divNav.style.height = '15px';
	divOpt.appendChild(divNav);
	trans = new Array();
	trans[0] = tableId;
	trans[1] = noPage;
	trans[2] = nbParPage;

	aG = document.createElement('DIV');

	aG.innerHTML = '<< ';
	aG.className = 'lien versGauche';

	aG.onclick = (function(trans) {
		return function() {
			enveloppeTable(trans[0], trans[1] - 1, trans[2]);
		};
	})(trans);

	pOpt = document.createElement('H2');
	pOpt.innerHTML = (limMin + 1) + " à " + (limMax + 1);

	aD = document.createElement('DIV');
	aD.innerHTML = '>> ';
	aD.className = 'lien versDroite';
	aD.onclick = (function(trans) {
		return function() {
			enveloppeTable(trans[0], trans[1] + 1, trans[2]);
		};
	})(trans);

	if (noPage > 0) {
		divNav.appendChild(aG);
	}
	divNav.appendChild(pOpt);
	if ((noPage + 1) * nbParPage < cpt) {
		divNav.appendChild(aD);
	}

}

function enveloppeTableMois(tableId, annee, mois, indCol) {
	//	alert(tableId);
	mois = parseInt(mois);
	var moisDAnnee = new Array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
	maTable = document.getElementById(tableId);
	maTable.className = "tableEnveloppeMois";
	lesLignes = maTable.firstChild.childNodes;
	cpt = 0;
	for (var a = 0; a < lesLignes.length; a++) {
		if (lesLignes[a].nodeName == 'TR' && (lesLignes[a].className == 'lignePaire' || lesLignes[a].className == 'ligneImpaire' || lesLignes[a].className == 'ligneCache')) {
			try {
				//alert(lesLignes[a].childNodes[indCol].firstChild.innerHTML);
				if (lesLignes[a].childNodes[indCol].firstChild.innerHTML.split('-')[0] == annee && lesLignes[a].childNodes[indCol].firstChild.innerHTML.split('-')[1] == mois + 1) {
					cpt % 2 == 0 ? lesLignes[a].className = 'lignePaire' : lesLignes[a].className = 'ligneImpaire';
				} else {
					lesLignes[a].className = "ligneCache";
				}
			} catch(err) {/*alert("Erreur dans enveloppeTableMois:"+a+" "+indCol+" "+lesLignes[a].childNodes[indCol].innerHTML);*/
			}
			cpt++;
		}

	}
	videNoeud(tableId + "_divOpt");
	divOpt = document.getElementById(tableId + "_divOpt");
	divNav = document.createElement('DIV');
	divNav.className = "divNav";
	divOpt.appendChild(divNav);
	trans = new Array();
	trans[0] = tableId;
	trans[1] = annee;
	trans[2] = mois;
	trans[3] = indCol;

	aG = document.createElement('DIV');
	aG.innerHTML = '<<';
	aG.className = 'versGauche';

	aG.onclick = (function(trans) {
		return function() {
			enveloppeTableMois(trans[0], trans[2] == 0 ? trans[1] - 1 : trans[1], ((trans[1] * 12) + trans[2] - 1) % 12, trans[3]);
		};
	})(trans);

	pOpt = document.createElement('h2');
	pOpt.innerHTML = moisDAnnee[mois] + " " + annee;

	aD = document.createElement('DIV');
	aD.innerHTML = '>>';
	aD.className = 'versDroite';
	aD.onclick = (function(trans) {
		return function() {
			enveloppeTableMois(trans[0], trans[2] == 11 ? trans[1] + 1 : trans[1], ((trans[1] * 12) + trans[2] + 1) % 12, trans[3]);
		};
	})(trans);

	divNav.appendChild(aG);
	divNav.appendChild(pOpt);
	divNav.appendChild(aD);

}

