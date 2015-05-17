/**
 * @author Kimsyl
 */

// this deletes the cookie when called
function deleteCookie( name, path, domain ) {
	
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


function genereEntete()
	{
	usager = getCookie("userId");
	
	
	topDiv = document.createElement('DIV');
	m2body = document.getElementsByTagName("body")[0];
//	body.innerHTML="un autre test";
	m2body.appendChild(topDiv);
	topDiv.style.width='90%';
//	topDiv.setAttribute('float','left')

	
	titre = document.createElement('H1');
	topDiv.appendChild(titre);
	titre.id='titre';

	titre.style.textAlign='left';
//	titre.setAttribute('align','middle');
	titre.innerHTML='SyncStats';


	sousTitre = document.createElement('H2');
	sousTitre.id='sousTitre';
	topDiv.appendChild(sousTitre);

//	sousTitre.setAttribute('text-align','left');
	sousTitre.style.textAlign='left';
//	titre.setAttribute('align','middle');
	sousTitre.innerHTML='Allez, jouez. On vous suit.';


if(usager === undefined)
{
	toLog = document.createElement('H2');
	toLog.style.width='90%';
	toLog.id='toLog';
//	toLog.setAttribute('width','90%')

	toLog.style.textAlign='right';
//	toLog.setAttribute('style','text-align:right');
//	titre.setAttribute('align','middle');

	lien = document.createElement('A');
	lien.innerHTML = "inscription";
	lien.setAttribute('href','/admin/inscription.html');
	toLog.appendChild(lien);

	var newText = document.createTextNode(' | ')

	toLog.appendChild(newText);

	lien2 = document.createElement('A');
	lien2.innerHTML = "connexion";
	lien2.setAttribute('href','/login.html');
	toLog.appendChild(lien2);
	topDiv.appendChild(toLog);
	
}
else{
	affUser = document.createElement('p');
	affUser.setAttribute('id','affUser');
	topDiv.appendChild(affUser);


	affUser.style.textAlign='right';
//	titre.setAttribute('align','middle');
	affUser.innerHTML='Bienvenue, '+usager +'!';
	affUser.style.width='90%';

	lien = document.createElement('A');
	lien.innerHTML = "    ...Déconnexion";
	lien.setAttribute('href','/index.html');
	lien.onclick = function(){
//		alert("dans deleteCookie");
//if ( Get_Cookie( name ) ) document.cookie = name + "=" +
//( ( path ) ? ";path=" + path : "") +
//( ( domain ) ? ";domain=" + domain : "" ) +
//";expires=Thu, 01-Jan-1970 00:00:01 GMT";

if ( getCookie( "userId" ) ) document.cookie = "userId" + "=" +
( ( "/" ) ? ";path=" + "/" : "") +
( ( "" ) ? ";domain=" + "" : "" ) +
";expires=Thu, 01-Jan-1970 00:00:01 GMT";

	};
	
	
//	setAttribute("onClick","deleteCookie('userId', '/', '')");
	affUser.appendChild(lien);


}


//	mainLogo = document.createElement('IMG');
//	topDiv.appendChild(mainLogo);

//	mainLogo.setAttribute('src','images/logo_v1.png');
//	mainLogo.setAttribute('text-align','center');
//	mainLogo.setAttribute('align','middle');
}

function genereMenu(position)
{
	divMenu = document.createElement('DIV');
	m2body = document.getElementsByTagName("body")[0];
	m2body.appendChild(divMenu);
	divMenu.style.width='90%';
	divMenu.style.cssFloat='left';
	divMenu.id='divMenu';


	menu2_b = document.createElement('UL');
	divMenu.appendChild(menu2_b);
	menu2_b.id='menu2_b';


	li1 = document.createElement('LI');
	menu2_b.appendChild(li1);
	a1 = document.createElement('A');
	li1.appendChild(a1);
	a1.setAttribute('href','/index.html');
	a1.innerHTML='Page d\'accueil';

	li4 = document.createElement('LI');
	menu2_b.appendChild(li4);
	a4 = document.createElement('A');
	li4.appendChild(a4);
	a4.setAttribute('href','/login.html');
	a4.innerHTML='Mon profil';


	li2 = document.createElement('LI');
	menu2_b.appendChild(li2);
	a2 = document.createElement('A');
	li2.appendChild(a2);
	a2.setAttribute('href','/listeligues.html');
	a2.innerHTML='Statistiques';
		
	li3 = document.createElement('LI');
	menu2_b.appendChild(li3);
	a3 = document.createElement('A');
	li3.appendChild(a3);
	a3.setAttribute('href','/faq.html');
	a3.innerHTML='FAQ';

switch(position)
{
case 1:
	a1.className='actif_b';
  break;
case 2:
	a2.className='actif_b';
  break;
case 3:
	a3.className='actif_b';
  break;
case 4:
	a4.className='actif_b';
  break;

default:
}
	divDate = document.createElement('DIV');
	menu2_b.appendChild(divDate);
	divDate.innerHTML= formatDate(new Date());
	divDate.className='date';


	document.write('</br>')
	document.write('</br>')
}
