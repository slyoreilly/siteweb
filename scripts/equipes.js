/**
 * @author Sylvain O'Reilly, 13 novembre 2012
 */

			function editEquipe(pos) {
				mesDivs = document.getElementsByTagName('DIV')
				varPos = pos;
				varDivEqJ = 0;
				varDivOptJ = 0;
				varDivNomJ = 0;
				varVieuNom = "";
				varVieuImg = "";
				varVieuLE = "";
				maCouleur = window.lJSON.equipes[pos].logo;
				varK = 0;
				varKLE = 0;
				for ( J = 0; J < mesDivs.length; J++) {
					if (mesDivs[J].id.split("_")[1] == pos && mesDivs[J].id.split("_")[0] == "divEquipe") {
						mesDivs[J].style.borderWidth = "2px";
						mesDivs[J].style.borderStyle = "solid";
						mesDivs[J].style.borderColor = "#CCC";
						varDivEqJ = J;
					}
					if (mesDivs[J].id.split("_")[1] == pos && mesDivs[J].id.split("_")[0] == "opt") {
						strSave = "<img id=\"imgSave\" src=\"\/images\/icones\/save.png\" onclick=btnOkModifieEquipe() width=\"32\" height=\"32\" alt=\"Save\"\>";
						strCancel = "<img id=\"imgCancel\" src=\"\/images\/icones\/delete.png\" onclick=annuleModif(); width=\"32\" height=\"32\" alt=\"Cancel\"\>";
						varDivOptJ = J;
						varVieuImg = mesDivs[J].innerHTML;

						mesDivs[J].innerHTML += strSave + strCancel;
					}
					if (mesDivs[J].id.split("_")[1] == pos && mesDivs[J].id.split("_")[0] == "nom") {
						varVieuNom = mesDivs[J].innerHTML;
						inNom = document.createElement('INPUT');
						inNom.type = "TEXT";
						inNom.id = "inNom";
						inNom.value = mesDivs[J].innerHTML;
						mesDivs[J].innerHTML = "";
						mesDivs[J].appendChild(inNom);
						varDivNomJ = J;
					}

				}
				mesImg = document.getElementsByTagName('IMG');

				for ( K = 0; K < mesImg.length; K++) {
					if (mesImg[K].id.split("_")[1] == pos && mesImg[K].id.split("_")[0] == "couleur") {
						window.varK = K;
						divParentCouleur = document.createElement('DIV');
						divParentCouleur.id = "divParentCouleur";
						divCouleur = document.createElement('DIV');
						divCouleur.id = "divCouleur";
						divCouleur.innerHTML = "Couleur de l'équipe: ";
						divCouleur.className = "formGauche";
						divInCouleur = document.createElement('DIV');
						divInCouleur.className = "formDroite";
						inCouleur = document.createElement('SELECT');
						inCouleur.id = "inCouleur";
						inCouleur.onchange = function() {
							strTmp = '/images/icones/' + window.mCouleurs[window.inCouleur.selectedIndex] + '.bmp';
							window.mesImg[window.varK].src = strTmp;
						}
						inCouleur.name = "selCouleur";
						mCouleurs = ["blanc", "vert", "bleu", "rouge", "orange", "mauve"];
						a = 0;
						while (a < mCouleurs.length) {
							opt = document.createElement('OPTION');
							opt.innerHTML = mCouleurs[a];
							opt.value = mCouleurs[a];
							opt.id = mCouleurs[a];
							if (maCouleur == mCouleurs[a])
								opt.selected = "selected";
							inCouleur.appendChild(opt);
							a++;
						}

						divParentCouleur.appendChild(divCouleur);
						divInCouleur.appendChild(inCouleur);
						divParentCouleur.appendChild(divInCouleur);
						mesImg[K].parentNode.insertBefore(divParentCouleur, mesImg[K]);

					}

					if (mesImg[K].id.split("_")[1] == pos && mesImg[K].id.split("_")[0] == "logoEquipe") {
						varVieuLE = mesImg[K].src;
						varKLE = K;
						divParentImg = document.createElement('DIV');
						divParentImg.id = "divParentImg";
						divImg = document.createElement('DIV');
						divImg.id = "divImg";
						divImg.innerHTML = window.tl_equipes_ficImage;
						divImg.className = "formGauche";
						divInImg = document.createElement('DIV');
						divInImg.className = "formDroite";

						inImgLE = document.createElement('INPUT');
						inImgLE.name = "userfile";
						inImgLE.type = "file";
						inImgLE.onchange = function(){if(this.value) document.getElementById('idBtnLogo').style.display='inline'};
						inImgLE.id = "idLogo";

						maForme = document.createElement('FORM');
						maForme.name = "nomFormeEdit";
						maForme.id = "idFormeEdit";
						maForme.action = "/admin/enregistreFichier.php";
						maForme.enctype = "multipart/form-data";
						maForme.method = "POST";

						inHiMFS = document.createElement('INPUT');
						inHiMFS.type = 'hidden';
						inHiMFS.id = 'maxFileSize';
						inHiMFS.name = 'MAX_FILE_SIZE';
						inHiMFS.value = '2000000';

						inHiCon = document.createElement('INPUT');
						inHiCon.type = 'hidden';
						inHiCon.id = 'idContexte';
						inHiCon.name = 'contexte';
						inHiCon.value = 'equipe';

						btnImgLE = document.createElement('INPUT');
						btnImgLE.name = "nomBtnLogo";
						btnImgLE.type = "BUTTON";
						btnImgLE.id = "idBtnLogo";
						btnImgLE.style.display='none';
						btnImgLE.value = window.tl_bouton_save;
						btnImgLE.onclick = function() {
							televerseFic();
						}

						maForme.appendChild(inHiMFS);
						maForme.appendChild(inImgLE);
						maForme.appendChild(btnImgLE);

						divParentImg.appendChild(divImg);
						divInImg.appendChild(maForme);
						divParentImg.appendChild(divInImg);
						mesImg[K].parentNode.insertBefore(divParentImg, mesImg[K]);
					}

				}

			}

			function annuleModif() {
				document.getElementById('couleur_' + window.varPos).src = '/images/icones/' + window.maCouleur + '.bmp';
				window.mesDivs[window.varDivEqJ].style.borderWidth = "0px";
				window.mesDivs[varDivOptJ].innerHTML = window.varVieuImg;
				window.mesDivs[varDivNomJ].innerHTML = window.varVieuNom;
				document.getElementById('logoEquipe_' + window.varPos).src = window.varVieuLE;
				//window.mesDivs[varDivNomJ].removeChild(window.inNom);
				detruitNoeud("divParentCouleur");
				detruitNoeud("divParentImg");
				detruitNoeud("inNom");
			}

			function afficheEquipes(str,divParent) {
				//return function(){
				lJSON = eval('(' + str + ')');
				//alert(lJSON.equipes.length);
				
							lJSON.Ligue.saisons.sort(function(a, b) {
								d1Cal = a.pm.split(" ")[0];
								d1Hor = a.pm.split(" ")[1];

								var d1 = new Date(d1Cal.split("-")[0], d1Cal.split("-")[1], d1Cal.split("-")[2], d1Hor.split(":")[0], d1Hor.split(":")[1], d1Hor.split(":")[2]);
								d2Cal = b.pm.split(" ")[0];
								d2Hor = b.pm.split(" ")[1];

								var d2 = new Date(d2Cal.split("-")[0], d2Cal.split("-")[1], d2Cal.split("-")[2], d2Hor.split(":")[0], d2Hor.split(":")[1], d2Hor.split(":")[2]);
								return (d1 - d2)
							});

				premDiv=document.createElement('DIV');
				premDiv.id='contEquipes';
									document.getElementById(divParent).appendChild(premDiv);

				for ( J = 0; J < lJSON.equipes.length; J = J + 1) {
					maDiv = document.createElement('DIV');
					maDiv.className = "divEquipe";
					maDiv.id = "divEquipe" + "_" + J;
					maDiv.style.padding = "5px";
					maDiv.style.margin = "10px";
					logoEquipe = document.createElement('IMG');
					logoEquipe.className = 'petitLogo';
					logoEquipe.id = "logoEquipe" + "_" + J;
					logoEquipe.src = '/admin/afficheImage.php?ficId=' + lJSON.equipes[J].ficId;

					var found = $.inArray(lJSON.equipes[J].equipeId, lJSON.Ligue.saisons[0].equipes) > -1; 
					if(!found){
						$(maDiv).hide();
					}
					else{maDiv.className="divEquipe montreTjrs"}
					
					
					couleur = document.createElement('IMG');
					couleur.src = '/images/icones/' + lJSON.equipes[J].logo + '.bmp';
					couleur.width = "64";
					couleur.height = "64";
					couleur.id = "couleur" + "_" + J;
//					alert(window.Perm);
					strImg = "<img src=\"\/images\/icones\/edit.png\" onclick=editEquipe(" + J + "); width=\"32\" height=\"32\" alt=\"Edit\"\>";
					strImg2 = "<img src=\"\/images\/icones\/view.png\" onclick=window.location.href='\/zstats\/statsequipe.html?equipeId=" + lJSON.equipes[J].equipeId + "'; width=\"32\" height=\"32\" alt=\"Voir\"\>";

					nom = document.createElement('DIV');
					nom.style.margin="10px";
					//ptexte = document.createElement('P');
					nom.innerHTML = lJSON.equipes[J].nom;
					nom.id = "nom_" + J;
					//cellule.appendChild(ptexte);

					opt = document.createElement('DIV');
					opt.id = "opt_" + J;
					opt.innerHTML = (window.Perm < 19) ? strImg + strImg2 : strImg2;

					maDiv.appendChild(opt);
					maDiv.appendChild(nom);
					maDiv.appendChild(couleur);
					maDiv.appendChild(logoEquipe);

					premDiv.appendChild(maDiv);
					
					strAffEqu="Afficher toutes les équipes";
					//	}
				}//fin du for
				$("<p id='affPlusEq' class='lien'></p>").text(strAffEqu).prependTo(premDiv);
				
				
				$("#affPlusEq").click(function(event){
					$(".divEquipe").fadeToggle(0);	
					iH=event.target.innerHTML;
					if(iH==strAffEqu)
					{event.target.innerHTML="Afficher moins d'équipes";}
					else
					{event.target.innerHTML=strAffEqu;}
					$(".montreTjrs").show();	
					
					
					});
					
				

				
			}


			function btnOkModifieEquipe() {
				//return function() {
				var requete_ajax = new XMLHttpRequest();

				var url = "/admin/modifierequipe.php";

				mFicId = (window.mesImg[window.varKLE].src == window.varVieuLE) ? window.varVieuLE.split('=')[1] : window.nouveauFicId;
				var params = "code=" + 10 + "&ligueId=" + window.ligueId + "&nom=" + window.inNom.value + 
				"&logo=" + window.mCouleurs[window.inCouleur.selectedIndex] +
				 "&ficId=" + mFicId + "&equipeId=" + window.lJSON.equipes[(window.varPos)].equipeId;


						window.mesDivs[window.varDivEqJ].style.borderWidth = "0px";
						window.mesDivs[varDivOptJ].innerHTML = window.varVieuImg;
//						window.mesDivs[varDivOptJ].innerHTML = window.varVieuImg;
						window.mesDivs[varDivNomJ].innerHTML = window.inNom.value;

						detruitNoeud("divParentCouleur");
						detruitNoeud("divParentImg");
						detruitNoeud("inNom");

				//alert(params);
				requete_ajax.open('POST', url, true);

				//Send the proper header information along with the request
				requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				requete_ajax.setRequestHeader("Content-length", params.length);
				requete_ajax.setRequestHeader("Connection", "close");

				//		alert('yo');
				requete_ajax.onreadystatechange = function() {//Call a function when the state changes.
					if (requete_ajax.readyState == 4 && requete_ajax.status == 200) {

					//alert(requete_ajax.responseText);
					}
					//else
					//alert("Problème de communcation, "+requete_ajax.readyState+", "+requete_ajax.status == 200);
				}
				requete_ajax.send(params);
				//		alert(requete_ajax.responseText);
				//requete_ajax.send(null);
				//	return requete_ajax.responseText;

				//}
			}



			function getInfoEquipesLigue(ligueId) {
				//	return function(){
				var requete_ajax = new XMLHttpRequest();

				var url = "/stats2/infoLigue2JSON.php";
				//	alert(url);
				var params = "ligueId=" + ligueId;
				//alert(params);
				requete_ajax.open('POST', url, true);

				requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				requete_ajax.setRequestHeader("Content-length", params.length);
				requete_ajax.setRequestHeader("Connection", "close");

				requete_ajax.onreadystatechange = function() {//Call a function when the state changes.
					if (requete_ajax.readyState == 4 && requete_ajax.status == 200) {
						//alert(requete_ajax.responseText);
						//			window.location.reload();
						afficheEquipes(requete_ajax.responseText,'divCentrale');
						return requete_ajax.responseText;
					}
					//		else{alert("Petit problème de développement");}
				}
				requete_ajax.send(params);
				//alert(requete_ajax.responseText);
				var pourMAJ = new Array();

				//	}
			}

			function televerseFic() {
				//	return function(){
				var requete_ajax = new XMLHttpRequest();

				var url = "/admin/enregistreFichier.php";
				//	alert(url);
				var formData = new FormData(document.getElementById('idFormeEdit'));
				/*				var params = {contexte: equipe,
				 form : window.maForme}
				 alert(params);*/
				requete_ajax.open('POST', url, true);

				//				requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				//				requete_ajax.setRequestHeader("Content-length", params.length);
				//				requete_ajax.setRequestHeader("Connection", "close");

				requete_ajax.onreadystatechange = function() {//Call a function when the state changes.
					if (requete_ajax.readyState == 4 && requete_ajax.status == 200) {
						//						alert(requete_ajax.responseText);
						//window.mesImg[window.varKLE].src = '/admin/afficheImage.php?ficId=' + requete_ajax.responseText;
						window.mesImg[window.varKLE].src = '/admin/afficheImage.php?ficId=' + requete_ajax.responseText;
						nouveauFicId = requete_ajax.responseText;
						//			window.location.reload();
						return requete_ajax.responseText;
					}
					//		else{alert("Petit problème de développement");}
				}
				requete_ajax.send(formData);
				//	}
			}

			function creerEquipe() {

				maForme = document.createElement('FORM');
				maForme.name = "myForm";
				maForme.id = "maForme"
				maForme.action = "/admin/modifierequipe.php";
				maForme.enctype = "multipart/form-data";
				maForme.method = "POST";
				mH2 = document.createElement('H2');
				mH2.id = "titreH2";
				mH2.innerHTML = "Créer une équipe";
				inHiEqId = document.createElement('INPUT');
				inHiEqId.type = 'hidden';
				inHiEqId.id = 'equipeId';
				inHiEqId.name = 'equipeId';
				inHiEqId.value = '';

				inHiMFS = document.createElement('INPUT');
				inHiMFS.type = 'hidden';
				inHiMFS.id = 'maxFileSize';
				inHiMFS.name = 'MAX_FILE_SIZE';
				inHiMFS.value = '2000000';

				brNom = document.createElement('BR');
				brNom.style.clear = "both";

				divParentImg = document.createElement('DIV');
				divImg = document.createElement('DIV');
				divImg.id = "divImg";
				divImg.innerHTML = "Fichier d'image ";
				divImg.className = "formGauche";
				divInImg = document.createElement('DIV');
				divInImg.className = "formDroite";
				inImg = document.createElement('INPUT');
				inImg.id = "userfile";
				inImg.type = "file";
				inImg.name = "userfile";

				divParentImg.appendChild(divImg);
				divInImg.appendChild(inImg);
				divParentImg.appendChild(divInImg);
				divParentImg.appendChild(brNom);

				maForme.appendChild(mH2);
				maForme.appendChild(inHiEqId);
				maForme.appendChild(inHiMFS);
				//				maForme.appendChild(inImg);

				divParentNom = document.createElement('DIV');
				divNom = document.createElement('DIV');
				divNom.id = "divNom";
				divNom.innerHTML = window.tl_statseq_nomEq;
				divNom.className = "formGauche";
				divInNom = document.createElement('DIV');
				divInNom.className = "formDroite";
				inNom = document.createElement('INPUT');
				inNom.id = "inNom";
				inNom.type = "text";
				inNom.name = "nom";

				if (getValue("code") == 1) {
					nom = document.getElementById('nom');
					nom.value = "";
				}

				divParentNom.appendChild(divNom);
				divInNom.appendChild(inNom);
				divParentNom.appendChild(divInNom);
				divParentNom.appendChild(brNom);

				divParentCouleur = document.createElement('DIV');
				divCouleur = document.createElement('DIV');
				divCouleur.id = "divCouleur";
				divCouleur.innerHTML = window.tl_equipes_couleur;
				divCouleur.className = "formGauche";
				divInCouleur = document.createElement('DIV');
				divInCouleur.className = "formDroite";
				inCouleur = document.createElement('SELECT');
				inCouleur.id = "inCouleur";
				inCouleur.name = "selCouleur";

				var mCouleurs = ["blanc", "vert", "bleu", "rouge", "orange", "mauve"];
				a = 0;
				while (a < mCouleurs.length) {
					opt = document.createElement('OPTION');
					opt.innerHTML = mCouleurs[a];
					opt.value = mCouleurs[a];
					opt.id = mCouleurs[a];
					inCouleur.appendChild(opt);
					a++;
				}

				brNom = document.createElement('BR');
				brNom.style.clear = "both";

				divParentCouleur.appendChild(divCouleur);
				divInCouleur.appendChild(inCouleur);
				divParentCouleur.appendChild(divInCouleur);
				divParentCouleur.appendChild(brNom);

				maForme.insertBefore(divParentImg, mH2.nextSibling);
				maForme.insertBefore(divParentCouleur, mH2.nextSibling);
				maForme.insertBefore(divParentNom, mH2.nextSibling);

				inOk = document.createElement('INPUT');
				inOk.id = "inOk";
				inOk.type = "BUTTON";
				inOk.name = "nInOk";
				inOk.value = window.tl_bouton_save;
				inOk.onclick = function() {
					btnOkCreeEquipe();
				}

				maForme.insertBefore(inOk, mH2.nextSibling);
				document.getElementById('divCentrale').appendChild(maForme);
			}

			function btnOkCreeEquipe() {
				//return function() {
				var requete_ajax = new XMLHttpRequest();

				var url = "/admin/modifierequipe.php";

				//	alert(url);
				var params = "code=" + getValue("code") + "&ligueId=" + getValue("ligueId") + "&nom=" + window.inNom.value + "&logo=" + window.inCouleur.options[inCouleur.selectedIndex].value;
				//alert(params);
				requete_ajax.open('POST', url, true);

				//Send the proper header information along with the request
				requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				requete_ajax.setRequestHeader("Content-length", params.length);
				requete_ajax.setRequestHeader("Connection", "close");

				//		alert('yo');
				requete_ajax.onreadystatechange = function() {//Call a function when the state changes.
					if (requete_ajax.readyState == 4 && requete_ajax.status == 200) {
						//			alert(requete_ajax.responseText);
						//window.location.href = '/index.html';
					}
					//else
					//alert("Problème de communcation, "+requete_ajax.readyState+", "+requete_ajax.status == 200);
				}
				requete_ajax.send(params);
				//		alert(requete_ajax.responseText);
				//requete_ajax.send(null);
				//	return requete_ajax.responseText;

				//}
			}
