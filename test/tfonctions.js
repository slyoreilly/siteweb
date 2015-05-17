	function getJSONdeLigueID(LigueID)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats/Ligues2JSON.php?LigueID='+LigueID,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getInfoMatch(matchId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats/infoMatch2JSON.php?matchId='+matchId,false);
	requete_ajax.send(null);
	//alert(requete_ajax.responseText);
	
	return requete_ajax.responseText;
return "1";
	}
	function getJSONdeEquipeID(equipeId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats/profilEquipe2JSON.php?equipeId='+equipeId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getJSONdeEquipeID2(nomEquipe, ligueId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/stats/profilEquipe2JSON.php?nomEquipe='+nomEquipe+'&ligueId='+ligueId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	
	
	function getLigueDeEquipeID(equipeId)
	{
	var requete_ajax = new XMLHttpRequest();
//	requete_ajax.open('GET','stats/listeLigues2JSON.php?'+LigueID,false);
	requete_ajax.open('GET','/test/tLigues2JSON.php?equipeId='+equipeId,false);
	requete_ajax.send(null);
	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}

	function getJSONabonnementDeLigueID(LigueID)
	{
	var requete_ajax = new XMLHttpRequest();
//	requete_ajax.open('GET','stats/listeLigues2JSON.php?'+LigueID,false);
	requete_ajax.open('GET','/stats/abonnements2JSON.php?ligueId='+LigueID,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
function getProfilUserJson(joueurId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','stats/profilDeJoueurId.php?joueurId='+joueurId,false);
	requete_ajax.send(null);
	//alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
function getProfilDeJoueurId(joueurId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','stats/profilDeJoueurId.php?joueurId='+joueurId,false);
	requete_ajax.send(null);
	//alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}

function getJSONdeMatchID(matchID)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','stats/sommaire2JSON.php?matchId='+matchID,false);
	requete_ajax.send(null);
	//alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getStatsJSON(equipe,matchId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','stats/stats2JSON.php?equipe='+equipe+'&matchId='+matchId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getStatsEquipeJSON(equipeId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/test/tequipe2JSON.php?equipeId='+equipeId,false);
	requete_ajax.send(null);
	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	function getStatsLigueJSON(ligueId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/test/tjoueursDansLigue2JSON.php?ligueId='+ligueId,false);
	requete_ajax.send(null);
	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}

	function getStatsGardiens(saisonId,ligueId,equipeId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','/test/tgardiens2JSON.php?ligueId='+ligueId+'&saisonId='+saisonId+'&equipeId='+equipeId,false);
	requete_ajax.send(null);
	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}

function getStatsJoueurJSON(joueurId)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','stats/statsJoueur2JSON.php?joueurId='+joueurId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}
	


	function getPermissions(ligueId,userId)
	{
	var requete_ajax = new XMLHttpRequest();
//	requete_ajax.open('GET','stats/listeLigues2JSON.php?'+LigueID,false);
	requete_ajax.open('GET','/stats/getPermissions.php?ligueId='+ligueId+'&userId='+userId,false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}


	function getClassementJSON(LigueID)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','stats/classement2JSON.php?ligueId='+LigueID,false);
//	requete_ajax.open('GET','stats/listeMatchs2JSON.php',false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}


	function getMatchsdeLigueID(LigueID)
	{
	var requete_ajax = new XMLHttpRequest();
	requete_ajax.open('GET','stats/listeMatchs2JSON.php?ligueId='+LigueID,false);
//	requete_ajax.open('GET','stats/listeMatchs2JSON.php',false);
	requete_ajax.send(null);
//	alert(requete_ajax.responseText);
	return requete_ajax.responseText;
	}


	function getLigueDeUserId(userId)
	{
		var requete_ajax = new XMLHttpRequest();
		var url ="stats/user2Ligue.php";
		requete_ajax.open('GET', url+'?userId='+userId, false);
		requete_ajax.send(null);
//	alert("getLigueDeUserId: "+requete_ajax.responseText);
		return requete_ajax.responseText;
	}

	function getJSONdePerso(id)  // id est le username. 
	{
	var requete_ajax = new XMLHttpRequest();
				var url ="/stats/perso2JSON.php";
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
}/**/


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
/**/
  }
}

// this deletes the cookie when called
function deleteCookie( name, path, domain ) {
if ( Get_Cookie( name ) ) document.cookie = name + "=" +
( ( path ) ? ";path=" + path : "") +
( ( domain ) ? ";domain=" + domain : "" ) +
";expires=Thu, 01-Jan-1970 00:00:01 GMT";
}



	
function getValue(varname)
{
  var url = window.location.href;
  var qparts = url.split("?");
  if (qparts.length == 0)
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
  value = unescape(value);
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
