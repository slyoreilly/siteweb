	function getJSONdeLigueID(LigueID,saisonId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/Ligues2JSON.php?LigueID='+LigueID+'&saisonId='+saisonId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getInfoMatch(matchId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/infoMatch2JSON.php?matchId='+matchId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	
	return requete_ajax.responseText;
	return "1";
	}
	function getJSONdeEquipeID(equipeId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/profilEquipe2JSON.php?equipeId='+equipeId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getJSONdeEquipeID2(nomEquipe, ligueId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/profilEquipe2JSON.php?nomEquipe='+nomEquipe+'&ligueId='+ligueId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getJSONJoueursEquipes(equipeId, ligueId)  //  Fonction similaire utilisée dans zeroconfig.html
	{
	var requete_ajax = new XMLHttpRequest();
					var params = "ligueId=" + ligueId + "&equipeId=" + equipeId;
				//		alert(params);
			var url = "/stats2/joueursEquipeLigue2JSON.php";
			requete_ajax.open('POST', url, false);

				requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				requete_ajax.setRequestHeader("Content-length", params.length);
				requete_ajax.setRequestHeader("Connection", "close");

	requete_ajax.send(params);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	
	function getLigueDeEquipeID(equipeId)
	{
	var requete_ajax = new XMLHttpRequest();
//	requete_ajax.open('GET','/stats2/listeLigues2JSON.php?'+LigueID,false);
	requete_ajax.open('GET','/stats2/Ligues2JSON.php?equipeId='+equipeId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}

	function getJSONabonnementDeLigueID(LigueID)
	{
	var requete_ajax = new XMLHttpRequest();
//	requete_ajax.open('GET','/stats2/listeLigues2JSON.php?'+LigueID,false);
	requete_ajax.open('GET','/stats2/abonnements2JSON.php?ligueId='+LigueID,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
function getProfilUserJson(joueurId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/profilDeJoueurId.php?joueurId='+joueurId,false);
	requete_ajax.send(null);
	//alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
function getProfilDeJoueurId(joueurId)
	{
	var requete_ajax = new XMLHttpRequest();
		params='joueurId='+joueurId;
	requete_ajax.open('POST','/stats2/profilDeJoueurId.php',false);
	
					requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				requete_ajax.setRequestHeader("Content-length", params.length);
				requete_ajax.setRequestHeader("Connection", "close");

	requete_ajax.send(params);
	//alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}

function getJSONdeMatchID(matchID)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/sommaire2JSON.php?matchId='+matchID,false);
	requete_ajax.send(null);
	//alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getStatsJSON(equipe,matchId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/stats2JSON.php?equipe='+equipe+'&matchId='+matchId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getStatsEquipeJSON(equipeId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/equipe2JSON.php?equipeId='+equipeId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getStatsLigueJSON(ligueId,saisonId) /// Était utilisée dans fusionne.html. Fonction similaire dans meneurs.html.
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('POST','/stats2/joueursDansLigue2JSON.php?ligueId='+ligueId+'&saisonId='+saisonId,false);
	requete_ajax.send(null);
	//alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}

	function getMeneursUnitesSpeciales(ligueId,saisonId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/meneursUnitesSpeciales.php?ligueId='+ligueId+'&saisonId='+saisonId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getMeneursDN(ligueId,saisonId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/meneursDN.php?ligueId='+ligueId+'&saisonId='+saisonId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}


	function getStatsGardiens(saisonId,ligueId,equipeId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/gardiens2JSON.php?ligueId='+ligueId+'&saisonId='+saisonId+'&equipeId='+equipeId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}

function getStatsJoueurJSON(joueurId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/statsJoueur2JSON.php?joueurId='+joueurId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	


	function getPermissions(ligueId,userId)
	{
	var requete_ajax = new XMLHttpRequest();
//	alert('yo');
//	requete_ajax.open('GET','/stats2/listeLigues2JSON.php?'+LigueID,false);
	requete_ajax.open('GET','/stats2/getPermissions.php?ligueId='+ligueId+'&userId='+userId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}


	function getClassementJSON(LigueID,saisonId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/classement2JSON.php?ligueId='+LigueID+'&saisonId='+saisonId,false);
	requete_ajax.send(null);
	//alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getUnitesSpeciales(LigueID,saisonId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/unitesSpeciales2JSON.php?ligueId='+LigueID+'&saisonId='+saisonId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}


	function getMatchsdeLigueID(LigueID)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats2/listeMatchs2JSON.php?ligueId='+LigueID,false);
//	requete_ajax.open('GET','/stats2/listeMatchs2JSON.php',false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}


	function getLigueDeUserId(userId)
	{
		var requete_ajax = new XMLHttpRequest();
		var url ="/stats2/user2Ligue.php";
		requete_ajax.open('GET', url+'?userId='+userId, false);
		requete_ajax.send(null);
//	alert("getLigueDeUserId: "+requete_ajax.responseText);
		return requete_ajax.responseText;
	}

	function getJSONdePerso(id)  // id est le username. 
	{
	var requete_ajax = new XMLHttpRequest();
				var url ="/stats2/perso2JSON.php";
		var params = "id="+id;
		requete_ajax.open("POST", url, false);

		//Send the proper header information along with the request
		requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		requete_ajax.setRequestHeader("Content-length", params.length);
		requete_ajax.setRequestHeader("Connection", "close");


			requete_ajax.send(params);
//	alert("ResponseText de getJSONdePerso: "+requete_ajax.responseText);
//	alert("params: "+params);
	return requete_ajax.responseText;
	}
	

	function setMatchsAVoir(match)
	{
	var requete_ajax = new XMLHttpRequest();
//	window.location = "statsmatch.html?match="+match;
	requete_ajax.open('GET','statsmatch.html?match='+match,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	document.body.innerHTML=requete_ajax.responseText;
	return requete_ajax.responseText;
	}


	

function postwith (to,p) {
//  alert(to);
  var myForm = document.createElement("form");
  myForm.method="post" ;
  myForm.action = to ;
  for (var k in p) {
    var myInput = document.createElement("input") ;
    myInput.setAttribute("name", k) ;
    myInput.setAttribute("value", p[k]);
    myInput.setAttribute("type", "hidden");
    myForm.appendChild(myInput) ;
  }
  document.body.appendChild(myForm) ;
  myForm.submit() ;
  document.body.removeChild(myForm) ;
  
}

function parseMatchID(ID){
var monMatch = ID.split("_");
return monMatch;
} 

////////////////////////////////////////////////////////////////
//
//  COOKIES!!!!
//
////////////////////////////////////////////////////////////////
function setCookie(c_name,value,exdays)
{
var exdate=new Date();
exdate.setDate(exdate.getDate() + exdays);
var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
document.cookie=c_name + "=" + c_value+'; path=/';
}

function getCookie(c_name)
{
var i,x,y,ARRcookies=document.cookie.split(";");
for (i=0;i<ARRcookies.length;i++)
  {
  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
  x=x.replace(/^\s+|\s+$/g,"");
  if (x==c_name)
    {
    return unescape(y);
    }
  }
  return null;
}/*
  * */


function checkCookie()
{
var username=getCookie("userId");
  if (username!=null && username!="")
  {
	var person={id:username};
postwith("pageperso.php",person);
  }
else
  {
  }
}

// this deletes the cookie when called
function deleteCookie( name, path, domain ) {
try{
if ( getCookie( name ) ) 
	{strDel=name + "=" + ( ( path ) ? ";path=" + path : "") + ( ( domain ) ? ";domain=" + domain : "" ) + ";expires=Thu, 01-Jan-1970 00:00:01 GMT";
		document.cookie = strDel;
	alert(strDel);}
}

catch(err){}
}
function SimpleDeleteCookie( name) {
//	alert(name);
	path="/";
	domain="";
//	deleteCookie(name, "/", "");
if ( getCookie( name ) ) 
	{strDel=name + "=" + ( ( path ) ? ";path=" + path : "") + ( ( domain ) ? ";domain=" + domain : "" ) + ";expires=Thu, 01-Jan-1970 00:00:01 GMT";
		document.cookie = strDel;}
//	 alert("Après del");
//document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}



	
function getValue(varname)
{
  var url = window.location.href;
  var qparts = url.split("?");
  if (qparts.length <= 1)
  {
    return "";
  }
  var query = qparts[1];
  var vars = query.split("&");
  var value = "";
  for (i=0;i<vars.length;i++)
  {
    var parts = vars[i].split("=");
    if (parts[0] == varname)
    {
      value = parts[1];
      break;
    }
  }
  value = decodeURIComponent(value);
  //value = unescape(value);
  value.replace(/\+/g," ");

  return value;
}

	var triPar = function(field, reverse, primer){

   var key = function (x) {return primer ? primer(x[field]) : x[field]};

   return function (a,b) {
       var A = key(a), B = key(b);
       return ((A < B) ? -1 :(A > B) ? +1 : 0) * [-1,1][+!!reverse];                  
   }
}


formatDate = function(date) {
 var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
 var jours = ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
 var mois = ["janvier", "février", "mars", "avril", "mai", 
   "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"];
 var months = ["January", "February", "March", "April", "May", 
   "June", "July", "August", "September", "October", "November", "December"];
  var pad = function(str) { str = String(str); return (str.length < 2) ? "0" + str : str; }

 var meridian = (parseInt(date.getHours() / 12) == 1) ? 'PM' : 'AM';
 var hours = date.getHours() > 12 ? date.getHours() - 12 : date.getHours();
 return jours[date.getDay()] + ', le ' + date.getDate() + ' ' + mois[date.getMonth()] + ' ' 
     + date.getFullYear() + ' ' + hours + ':' + pad(date.getMinutes()) + ':' 
     + pad(date.getSeconds()) + ' ' + meridian;
}
function str2humain(longueDate) {
 var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
 var jours = ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
 var mois = ["","janvier", "février", "mars", "avril", "mai", 
   "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"];
 var months = ["January", "February", "March", "April", "May", 
   "June", "July", "August", "September", "October", "November", "December"];
  var pad = function(str) { str = String(str); return (str.length < 2) ? "0" + str : str; }

var date=longueDate.split(' ')[0];
 var mMois=parseInt(date.split('-')[1]);
 return parseInt(date.split('-')[2]) + ' ' + mois[mMois] +  ' ' + date.split('-')[0];
}


function trouveIndiceJson(arr,clef,valeur) {
	i=0;
	res=-1;
//	aRetour= "";
	while(i<arr.length&&res==-1)
    {eq=arr[i];
    	res = (eq[clef] == valeur) ? i : -1 ;
    	i=i+1;
    }return  res;
}


function videNoeud(noeud) {
try{
var cell = document.getElementById(noeud);

if ( cell.hasChildNodes() )
{
    while ( cell.childNodes.length >= 1 )
    {
        cell.removeChild( cell.firstChild );       
    } 
}
}
catch(err){}


}

function detruitNoeud(noeud) {
try{
var cell = document.getElementById(noeud);
	videNoeud(noeud);
cell.parentNode.removeChild(cell);
}
catch(err){}


}
//check if the last node is an element node
function get_lastchild(n)
{
x=n.lastChild;
while (x.nodeType!=1)
  {
  x=x.previousSibling;
  }
return x;
}

function strcmp(a, b)
{   
    return (a<b?-1:(a>b?1:0));  
}

function verifiePermission() {
			userId = null;
			if(getCookie('userId') != null) {
				userId = getCookie('userId');
			} else {
				if(getValue('userId') == "") {
				}//Code 10: origine de statistique
				else {
					userId = getValue('userId');
				}
			}
			if(getCookie('ligueId') != null) {
				ligueId = getCookie('ligueId');
			} else {
				if(getValue('ligueId') == "") {
					ligueId=null;
				}//Code 10: origine de statistique
				else {
					ligueId = getValue('ligueId');
				}
			}




		//	var strAbon = getJSONabonnementDeLigueID(ligueId);
		//	alert(strAbon);
		//	var aJSON = eval('(' + strAbon + ')');
		var ap =getPermissions(ligueId,userId);
		
		//alert(ap);
			return ap;		
	}


			function afficheNonPermis() {
		
						divC = document.getElementById('mbody')
						pTexte = document.createElement('P');
						pTexte.style.marginTop="100px";
						pTexte.innerHTML = "Vous n'avez pas les privilèges suffisants pour effectuer quelconque opération."
						divC.appendChild(pTexte);
					}
					
					function cuisinier(ingredients)
					{
						for(var a=0;a<ingredients.length;a++)
						{
							if(ingredients[a]=="ligueId")
								{
//									alert(window.ligueId);
									
								ligueId = null;
								if(getValue('ligueId')!="")
									{ligueId=getValue('ligueId');
									setCookie('ligueId', ligueId, 120);}
									
									
				 				else 
				 					{	
				 						if(getCookie('ligueId')!=null)
											{ligueId=getCookie('ligueId');}
										else{
											forceSelectionLigue();
											/*
											moncode  = (getValue("code")=="" )? 50: getValue("code");  // 50 frame, window.close() 
																	construitDialogue();
											dial =document.getElementById('divDialogue');
											monIF=document.createElement('IFRAME');
											dial.appendChild(monIF);
											monIF.src="/listeligues.html"+"?code="+moncode+'&m=1';*/

//				 							window.location.href = "/listeligues.html"+"?code="+moncode;
				 							} //Code 10: origine de statistique // Code 11: origine de zone admin
								}
								}
								
							if(ingredients[a]=="m")
								m = getValue('m');
							if(ingredients[a]=="userId")
								{
								userId = null;
								if(getCookie('userId') != null) {
									userId = getCookie('userId');
									} else {
										if(getValue('userId') == "") {
										}//Code 10: origine de statistique
										else {
											userId = getValue('userId');
											}
									}
			//						alert(ligueId+" "+userId);
								if(typeof ligueId === 'undefined')
									{Perm=1000000;}
								else
									{Perm = getPermissions(ligueId, userId);}
									
								}
						
						}
					}
					
					function developper(pageId){
						
						if(window.menus[pageId].ingredients!=undefined)
							{cuisinier(window.menus[pageId].ingredients);}
						if(window.m!=1)
						{
							try{
							gestionBackEnd();
							peupleDivHaut();
							peupleDivBas();
							//genereLiensContext(pageId);
							barreTitreContexte(pageId);
							if(window.innerWidth>=640)
									{genereBoites();}
								}
								catch(err){};
						}
						else
						{
							detruitNoeud('divGauche');
							detruitNoeud('divDroite');
							document.getElementById('divCentrale').style.width="100%";
						}
						
					}
					function gestionBackEnd(){
						var d = new Date();

						var month = d.getMonth()+1;		
						var day = d.getDate();

						var output = d.getFullYear() + '-' + (month<10 ? '0' : '') + month + '-' + (day<10 ? '0' : '') + day;
						mFakeId =   getCookie("fakeId")==null ? "":getCookie("fakeId");
						mLigueId =   getCookie("ligueId")==null ? "":getCookie("ligueId");
						
						$.post( "/scriptsphp/enregistreVisite.php", { userId: getCookie("userId"),  fakeId:mFakeId, ligueId:mLigueId , location:window.location.href, referrer:document.referrer,date:output  })
						 .done(function( data ) {
							setCookie('fakeId',data,3000);
						});
					}	
					
									

function construitDialogue (){
		monOverlay=document.createElement('DIV');
		monOverlay.id="ecranSombre";
		document.getElementsByTagName('BODY')[0].appendChild(monOverlay);
		monDial=document.createElement('DIV');
		monDial.id="divDialogue";
		monDial.className="divDialogue visible";
		dFerme=document.createElement('DIV');
		dFerme.id="divFerme";
		tFerme=document.createElement('DIV');
		tFerme.id="texteFerme";
		tFerme.innerHTML=window.tl_bouton_Fermer;
		bFerme=document.createElement('DIV');
		bFerme.id="btnFermeDial";
		bFerme.className="btnFerme";
		bFerme.innerHTML="[X]";
		bFerme.onclick=function(){detruitNoeud('divDialogue'); detruitNoeud('ecranSombre');}
		monDial.appendChild(dFerme);
			dFerme.appendChild(tFerme);
			dFerme.appendChild(bFerme);

		document.getElementById('divCentrale').appendChild(monDial);
		
		 window.onkeyup = function (event) {
  			if (event.keyCode == 27) {
   			detruitDialogue();
  		}		
 		}
	
	
	
}
function detruitDialogue (){
detruitNoeud('divDialogue'); detruitNoeud('ecranSombre');
	 window.onkeyup = function (event) {
  			if (event.keyCode == 27) {
  		}		
 		}


}


function forceSelectionLigue(){
	
	
				function surSelection(ligueId) {
				return function() {
					setCookie('ligueId', ligueId, 120);
					window.location.reload(true);
				}
				//						setCookie('ligueId',ligueId,120);
				//window.location.href='statistiques.html?ligueId='+ligueId;
			}

	
	construitDialogue ();
	
	 window.onkeyup = function (event) {
  			if (event.keyCode == 27) {
					window.location.href=document.referrer;
  		}		
 		}
 		
 		document.getElementById('texteFerme').innerHTML='Retour';

		document.getElementById('btnFermeDial').innerHTML="[&#8629]";

 				document.getElementById('btnFermeDial').onclick=function(){window.location.href=document.referrer;}


	titreChoix=document.createElement('H1');
			titreChoix.innerHTML="Vous devez choisir une ligue avant d'aller plus loin.";
			document.getElementById('divDialogue').appendChild(titreChoix);
			/*			titreChoix=document.createElement('H1');
						titreChoix=document.createElement('H1');
			titreChoix.innerHTML="Vous devez choisir une ligue avant d'aller plus loin.";*/


			mJSLigue = {};
			mJSLigue.id = "tableauLigues";
			mJSLigue.parentId = "divDialogue";
			mJSLigue.titre = "Liste des ligues";
			mJSLigue.rangeeTitre = new Array();
			mJSLigue.rangeeTitre[0] = "Nom de la ligue";
			mJSLigue.rangeeTitre[1] = "Emplacement";
			mJSLigue.rangeeTitre[2] = "Horaire";
			
			genTableau(mJSLigue);
			document.getElementById(mJSLigue.id+'_tbody').childNodes[2].id=mJSLigue.id+"_rangeeTitre";
			
	var parajax=0;
	
	if(getCookie("userId")!=null)
	{
	$.ajax({
		url:"/stats2/abonnements2JSON.php",
		data:{userId:getCookie("userId")}
		
	}).done(function(msg) {
		liguesAbon=jQuery.parseJSON(msg);
		parajax++;
		if(parajax>=2){
			rempliLigues();
		}
		
	})
	}
	else{
		liguesAbon=jQuery.parseJSON("[]");
		parajax++;
		if(parajax>=2){
			rempliLigues();
		}
	}
	
			$.ajax({
		url:"/stats2/Ligues2JSON.php"
		
			}).done(function(msg) {
				parajax++;
				try {
			
				rJSON=jQuery.parseJSON(msg);
				} catch(err) {
				alert('Le serveur éprouve quelques problêmes. Merci de réessayer plus tard.');
				}
		if(parajax>=2){
			rempliLigues();
		}
				
				
				
		})
		
		function rempliLigues(){
			

			try {

				mCode = getValue('code');
				var affiche =true;
				for ( var J = 0; J <= window.rJSON.Ligues.length; J++) {
//					alert(window.rJSON.Ligues[J].cleValeur);
					affiche=true;
					
					try {
					
					if(window.rJSON.Ligues[J].cleValeur==null)
					{affiche=true;}
					else{
									try {

						if(window.rJSON.Ligues[J].cleValeur.statut=="efface")
						{affiche=false;}
						if(window.rJSON.Ligues[J].cleValeur.statut=="secret"){
							if(window.liguesAbon.indexOf(window.rJSON.Ligues[J].ligueId)<0)
							{affiche=false;}
						}
									} catch(err) {
									}

						}	
							
					if(affiche==true)
					{
					rangee = document.createElement('TR');
					document.getElementById(window.mJSLigue.id+"_tbody").appendChild(rangee);

					celluleEq = document.createElement('TD');
					lien = document.createElement('A');
					lien.innerHTML = window.rJSON.Ligues[J].nomLigue;
					var ligueId = window.rJSON.Ligues[J].ligueId;

					switch(mCode) {
						case '10':
							//lien.href = menus[3].lien+"?ligueId=" + ligueId;
							//"javascript:void(0)";//'statistiques.html?ligueId='+rJSON.Ligues[J].ligueId);
							lien.onclick = surSelection(ligueId);
							lien.style.cursor="pointer";
							// setCookie
							//window.location.href='statistiques.html?ligueId='+ligueId;
							break;
						case '11':
							//lien.href = "/zadmin/zoneadmin.html?ligueId=" + ligueId;
							lien.onclick = surSelection(ligueId);
							lien.style.cursor="pointer";
							//window.a2.className = "";
							//window.a5.className = "actif_b";
							break;

						default:
							//lien.href = "/listematchs.html?ligueId=" + ligueId;
							lien.onclick = surSelection(ligueId);
							lien.style.cursor="pointer";
							break;
					}
					
					celluleEq.className = (J % 2 == 0) ? "lignePaire" : "ligneImpaire";
					celluleEq.appendChild(lien);
					rangee.appendChild(celluleEq);


					
					cellulePas1 = document.createElement('TD');
					textePas1 = document.createTextNode(rJSON.Ligues[J].lieu);
					cellulePas1.className = (J % 2 == 0) ? "lignePaire" : "ligneImpaire";

					cellulePas1.appendChild(textePas1);
					rangee.appendChild(cellulePas1);
					cellulePas2 = document.createElement('TD');
					textePas2 = document.createTextNode(rJSON.Ligues[J].horaire);

					cellulePas2.className = (J % 2 == 0) ? "lignePaire" : "ligneImpaire";

					cellulePas2.appendChild(textePas2);
					rangee.appendChild(cellulePas2);
					}//fin du if efface;
								} catch(err) {
									}

				}//Fin du for

				//	document.body.rangee.cellule.innerHTML ='yo';
			} catch(err) {
			}
			
		}
	
}

//////////////// Fonction en construction, jamais appellée...

function inscription(){    
		objForm=[
	{bloc:{id:"contUser",className:"ligneBloc",text:{id:"fgUsager",className:"formGauche", innerHTML:"Nom d'usager:"},input:{classDiv:"formDroite",inType:"text",inName:"usager",inId:"iUser"} }},
	{bloc:{id:"contPrenom",className:"ligneBloc",text:{id:"fgPrenom",className:"formGauche", innerHTML:"Prénom :"},input:{classDiv:"formDroite",inType:"text",inName:"prenom",inId:"iPrenom"} }},
	{bloc:{id:"contNom",className:"ligneBloc",text:{id:"fgNom",className:"formGauche", innerHTML:"Nom :"},input:{classDiv:"formDroite",inType:"text",inName:"nom",inId:"iNom"} }},
	{bloc:{id:"contPass1",className:"ligneBloc",text:{id:"fgPass",className:"formGauche", innerHTML:"Mot de passe :"},input:{classDiv:"formDroite",inType:"text",inName:"pass",inId:"iPass"} }},
	{bloc:{id:"contPass",className:"ligneBloc",text:{id:"fgConf",className:"formGauche", innerHTML:"Confirmer le mot de passe :"},input:{classDiv:"formDroite",inType:"text",inName:"pass2",inId:"iConf"} }},
	{bloc:{id:"contCP",className:"ligneBloc",text:{id:"fgCP",className:"formGauche", innerHTML:"Code Postal :"},input:{classDiv:"formDroite",inType:"text",inName:"codePostal",inId:"iCP"} }},
	{bloc:{id:"contCourriel",className:"ligneBloc",text:{id:"fgCourriel",className:"formGauche", innerHTML:"Courriel :"},input:{classDiv:"formDroite",inType:"text",inName:"courriel",inId:"iCourriel"} }},
	];
	
	
		construitDialogue();
							dial =document.getElementById('divDialogue');
							formulaire=document.createElement('DIV');
							formulaire.id='divFormulaire';
							dial.appendChild(formulaire);
/*						monIF=document.createElement('IFRAME');
						dial.appendChild(monIF);
						monIF.src="/zuser/login.html?m=1";*/
	 window.onkeyup = function (event) {
  			if (event.keyCode == 27) {
//					window.location.href=document.referrer;
		detruitDialogue();
  		}		
 		}
 		
// 		document.getElementById('texteFerme').innerHTML='Retour';

//		document.getElementById('btnFermeDial').innerHTML="[&#8629]";

 				document.getElementById('btnFermeDial').onclick=function(){
 					//window.location.href=document.referrer;
 					detruitDialogue();
 					}
		/////	 Section specifique
		
		titreConn=document.createElement('h1');
		titreConn.innerHTML=window.tl_connect_long;
		formulaire.appendChild(titreConn);
		divST=document.createElement('div');
		divST.id='divSousTitre';
		formulaire.appendChild(divST);
		sousTitre=document.createElement('h2');
		sousTitre.innerHTML=window.tl_connect_pasInsc;
		divST.appendChild(sousTitre);

lh=document.createElement('div');
		lh.id='ligneH';
		formulaire.appendChild(lh);
		
		dLI=document.createElement('div');
		dLI.id="divLesInputs";
		formulaire.appendChild(dLI);


	for(var a=0;a<objForm.length;a++)
	{
		dBloc=document.createElement('DIV');
		dBloc.id=objForm[a].bloc.id;
		dBloc.className=objForm[a].bloc.className;
	
		
		dTexte=document.createElement('DIV');
		dTexte.id=objForm[a].bloc.text.id;
		dTexte.className=objForm[a].bloc.text.className;
		dTexte.innerHTML=objForm[a].bloc.text.innerHTML;

		dIn=document.createElement('DIV');
		dIn.className=objForm[a].bloc.input.classDiv;

		inp=document.createElement('INPUT');
		inp.id=objForm[a].bloc.input.inId;
		inp.type=objForm[a].bloc.input.inType;
		inp.name=objForm[a].bloc.input.inName;
		
		dLI.appendChild(dBloc);
		dBloc.appendChild(dTexte);
		dBloc.appendChild(dIn);
		dIn.appendChild(inp);

	}
	
	
}

function connexion(){
	construitDialogue();
							dial =document.getElementById('divDialogue');
							formulaire=document.createElement('DIV');
							formulaire.id='divFormulaire';
							dial.appendChild(formulaire);
/*						monIF=document.createElement('IFRAME');
						dial.appendChild(monIF);
						monIF.src="/zuser/login.html?m=1";*/
	 window.onkeyup = function (event) {
  			if (event.keyCode == 27) {
//					window.location.href=document.referrer;
		detruitDialogue();
  		}		
 		}
 		
// 		document.getElementById('texteFerme').innerHTML='Retour';

//		document.getElementById('btnFermeDial').innerHTML="[&#8629]";

 				document.getElementById('btnFermeDial').onclick=function(){
 					//window.location.href=document.referrer;
 					detruitDialogue();
 					}
		/////	 Section specifique
		
		titreConn=document.createElement('h1');
		titreConn.innerHTML=window.tl_connect_long;
		formulaire.appendChild(titreConn);
		divST=document.createElement('div');
		divST.id='divSousTitre';
		formulaire.appendChild(divST);
		sousTitre=document.createElement('h2');
		sousTitre.innerHTML=window.tl_connect_pasInsc;
		divST.appendChild(sousTitre);

		lienInsc=document.createElement('A');
		lienInsc.id="lienInscription";
		lienInsc.href="/zuser/inscription.html";
		lienInsc.innerHTML=window.tl_general_signin;

		divST.appendChild(lienInsc);

		lh=document.createElement('div');
		lh.id='ligneH';
		formulaire.appendChild(lh);
		
		dLI=document.createElement('div');
		dLI.id="divLesInputs";
		formulaire.appendChild(dLI);

		maForme=document.createElement('FORM');
		maForme.name="login";
		maForme.id="formLogin";
		
		
		d1=document.createElement('div');
		d1.id='tNomUsager';
		d1.className='formGauche';
		d1.innerHTML=window.tl_login_nomUsager;

		d2=document.createElement('div');
		d2.className='formDroite';
		in1=document.createElement('INPUT');
		in1.type='text';
		in1.name='id';
		in1.id='userId';
		d2.appendChild(in1);

		d3=document.createElement('div');
		d3.id='tMotPasse';
		d3.className='formGauche';
		d3.innerHTML=window.tl_login_motPasse;

		d4=document.createElement('div');
		d4.className='formDroite';
		in2=document.createElement('INPUT');
		in2.type='password';
		in2.name='pass';

		p1=document.createElement('P');
		p1.id='texteErreur';
		
		d5=document.createElement('div');
		d5.className='divLigneInput cliquable';
		d5.innerHTML=window.tl_mdpOublie;
		d5.id="dMDPOublie";
		d5.onclick=function (){
			
			var requete_ajax = new XMLHttpRequest();

			var url = "/scriptsphp/rappelleMotDePasse.php";

			var params = "recepteur=" + document.getElementById('userId').value;
			//		alert(params);
			requete_ajax.open('POST', url, true);

			//Send the proper header information along with the request
			requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			requete_ajax.setRequestHeader("Content-length", params.length);
			requete_ajax.setRequestHeader("Connection", "close");

			//		alert('yo');
			requete_ajax.onreadystatechange = function() {//Call a function when the state changes.
				if (requete_ajax.readyState == 4 && requete_ajax.status == 200) {
					if(requete_ajax.responseText==1)
						{document.getElementById('dMDPOublie').innerHTML=window.tl_repPosMdpOublie;}
					if(requete_ajax.responseText==0)
						{document.getElementById('dMDPOublie').innerHTML=window.tl_repNegMdpOublie;}

					
					//document.getElementById('corpsSujet_3').appendChild(mDiv);
					//										alert(document.getElementById("tabGesSai").id+"4");

				}

			}
			requete_ajax.send(params);

		}
		
		dBtnL=document.createElement('DIV');
		dBtnL.id="divBtnLogin";
		
		in3=document.createElement('INPUT');
		in3.type='BUTTON';
		in3.id='btnLogin';
		in3.value=window.tl_bouton_login;

		in3.onclick=function(){traiteForme(document.getElementById('formLogin'));}
		
		ligneIn1=document.createElement('div');
		ligneIn1.className="divLigneInput";
		ligneIn2=document.createElement('div');
		ligneIn2.className="divLigneInput";

		
		dLI.appendChild(maForme);
		maForme.appendChild(ligneIn1);
		maForme.appendChild(ligneIn2);

		ligneIn1.appendChild(d1);
		ligneIn1.appendChild(d2);		
			d2.appendChild(in1);
		ligneIn2.appendChild(d3);		
		ligneIn2.appendChild(d4);		
			d4.appendChild(in2);
	
		maForme.appendChild(p1);
		maForme.appendChild(d5);
		dBtnL.appendChild(in3);	
		formulaire.appendChild(dBtnL);	
	

		function traiteForme(matchID) {
			uneString = verifId(matchID);
			var rJSON = eval('(' + uneString + ')');

			if (rJSON.idCheck == "true") {

				amettre2 = document.getElementById('lienInscription');
				tErr = document.getElementById('texteErreur');
				tErr.innerHTML = tl_login_idValide;
				tErr.style.color="#8DBB42";
				setCookie("userId", rJSON.id, 120);
				var person = {
					id : rJSON.id,
					userId:rJSON.userId
				};
				//postwith("pageperso.php",person);
				postwith("/zuser/monprofil.html", person);

			} else {
				amettre2 = document.getElementById('lienInscription');
				document.getElementById('texteErreur').innerHTML = window.tl_login_idInvalide;
			}

		}
				function verifId(monId) {

			var requete_ajax = new XMLHttpRequest();

			var url = "/admin/verif.php";
			var params = "id=" + monId.id.value + "&password=" + monId.pass.value;
			requete_ajax.open("POST", url, false);

			//Send the proper header information along with the request
			requete_ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			requete_ajax.setRequestHeader("Content-length", params.length);
			requete_ajax.setRequestHeader("Connection", "close");

			requete_ajax.onreadystatechange = function() {//Call a function when the state changes.
				if (requete_ajax.readyState == 4 && requete_ajax.status == 200) {

				}
			}
			requete_ajax.send(params);
			return requete_ajax.responseText;

			//	alert(requete_ajax.responseText);

		}




	
}

function detruitDivGauche(){
		detruitNoeud('divGauche');
			document.getElementById('divCentrale').style.width="76%";
			document.getElementById('divCentrale').style.marginLeft="2%";
			document.getElementById('divCentrale').style.marginRight="2%";
			
					document.getElementById('divCentrale').style.left="0";

}

function detruitDivGaucheDroite(){
		detruitNoeud('divGauche');
		document.getElementById('divDroite').style.display="none";
			document.getElementById('divCentrale').style.width="96%";
			document.getElementById('divCentrale').style.marginLeft="2%";
			document.getElementById('divCentrale').style.marginRight="2%";
			
					document.getElementById('divCentrale').style.left="0";

}

function UrlExists(url)
{
    var http = new XMLHttpRequest();
    http.open('HEAD', url, true);
    http.send();
		http.onreadystatechange = (function(url) {//Call a function when the state changes.
			
			return function(){
			if (http.readyState == 4 &&http.status==404) {
				liens =document.getElementsByTagName('A');
				for(b=0;b<liens.length;b++)
				{
					if(liens[b].id==url)
					{liens[b].innerHTML="";}
					
				}
			}
		}		//
		})(url);   
}
Number.prototype.pad = function(size) {
      var s = String(this);
      while (s.length < (size || 2)) {s = "0" + s;}
      return s;
    }

	
