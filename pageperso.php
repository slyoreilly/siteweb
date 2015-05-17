<HTML> 
<HEAD> 
	<title>Page personnelle</title>
<link rel="stylesheet" href="style/general.css" type="text/css">
<script src="scripts/affichage.js" type="text/javascript" charset="utf-8"></script>
        <script src="scripts/fonctions.js" type="text/javascript" charset="utf-8"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
</style> 
</HEAD>
<script type="text/javascript">
	
	function creerLigue(uneForme)
	{

window.location="/admin/creerligue.html?userId="+window.usager.id+"&code="+moncode;
return true	;
	}

	function surSelection(ligueId)
					{
						return function(){setCookie('ligueId',ligueId,120);}
//						setCookie('ligueId',ligueId,120);
					//window.location.href='statistiques.html?ligueId='+ligueId;
					}
</script>

<body id="body" >
    	 	<script type="text/javascript">
				genereEntete();    
				genereMenu(4);    
		</script>


<DIV ID="titre"><H1> Mon Profil </H1></DIV>
<div>
<DIV ID="date"></DIV>
	</div>
<div>
<DIV ID="scoreDom">  </DIV>
<DIV ID="scoreVis"></DIV>
	</div>
	
	
	
<DIV ID="corps"> </DIV>

  <DIV ID="divCentrale">

<DIV>	<input type="button" value="Creer une ligue" onClick="creerLigue(this.form)"></DIV>


<DIV ID="divTab">
 <table id="tableau" class="c_tableau">
 <tr id="date2"><td class="titreTableau" colspan="4">Liste des ligues inscrites</td></tr>
 <tr id="rangeeTitreLigues">
<td class="rangeetitre">Match ID</td>
<td class="rangeetitre">Date</td>
<td class="rangeetitre">Lieu</td>
<td class="rangeetitre">Horaire</td>
</tr>
</table>
</DIV>

<DIV ID="ligneCentrale1">
<DIV ID="photoFiche"><img id="logoEquipe" class="grosLogo"></DIV>

<DIV ID="descriptionFiche"></DIV></DIV>
<DIV ID="commentaireFiche"></DIV>


 <DIV ID="divTab1">
 <table id="tableauJoueur" class="c_tableau">
 <tr><td class="titreTableau" colspan="7">Statistiques en carrière du joueur</td></tr>
 <tr id="rangeeTitreEquipe">
<td class="rangeetitre">Saison démarrant le:</td>
<td class="rangeetitre">Saison se terminant le:</td>
<td class="rangeetitre">Équipe</td>
<td class="rangeetitre">Parties jouées</td>
<td class="rangeetitre">Buts</td>
<td class="rangeetitre">Passes</td>
<td class="rangeetitre">Points</td>
</tr>
 </table>
  </DIV>

  
  
  </DIV>
 	<script type="text/javascript">
//alert("uneString");

var poste = getCookie("userId");
var uneString = getJSONdePerso(poste);
//alert(uneString);
var rJSON = eval('(' +uneString+')');

document.getElementById('date').innerHTML=rJSON.prenom+' '+rJSON.nom+"</br>"+rJSON.taille+" cm"+"</br>"+rJSON.poid+" kg";

var joueurId;
	for (M=0;M<rJSON.joueurs.length;M=M+1)
	{
	joueurId=rJSON.joueurs[M];
	}

	
	var strLigues = getLigueDeUserId(poste);
//		if(verifiePermission()<3)
//		alert(strLigues);

	var liguesJSON = eval('(' +strLigues+')');
	
	for (K=0;K<liguesJSON.abonnements.length;K=K+1)
	{

		strUneLigue = getJSONdeLigueID(liguesJSON.abonnements[K].ligueId,'null');
		uneLigueJSON = eval('(' +strUneLigue+')');
//		if(verifiePermission()<3)
//			alert(strUneLigue);
	

//for (J=0;J<=uneLigueJSON.Ligues.length;J=J+1)
//{
	rangee = document.createElement('TR');
	amettre = document.getElementById('rangeeTitreLigues');
	amettre.parentNode.appendChild(rangee);
	
	celluleEq = document.createElement('TD');
	lien = document.createElement('A');
	lien.innerHTML = uneLigueJSON.Ligues[0].nomLigue;
	var ligueId=uneLigueJSON.Ligues[0].ligueId;
//	lien.setAttribute('href','listematchs.html?ligueId='+uneLigueJSON.Ligues[0].ligueId+'&userId='+poste);
	lien.href='/statistiques.html?ligueId='+ligueId;
	lien.onclick= surSelection(ligueId);
	
	celluleEq.appendChild(lien);
	rangee.appendChild(celluleEq);

	celluleNom = document.createElement('TD');
	texteNom = document.createTextNode(uneLigueJSON.Ligues[0].nomLigue);
	celluleNom.appendChild(texteNom);
	rangee.appendChild(celluleNom);
	cellulePas1 = document.createElement('TD');
	textePas1 = document.createTextNode(uneLigueJSON.Ligues[0].lieu);
	cellulePas1.appendChild(textePas1);
	rangee.appendChild(cellulePas1);
	cellulePas2 = document.createElement('TD');
	textePas2 = document.createTextNode(uneLigueJSON.Ligues[0].horaire);
	cellulePas2.appendChild(textePas2);
	rangee.appendChild(cellulePas2);
//}
	}
	
	
	
	//		SECTION DE STATS PERSONNELLES     -->


//try{


//var joueurId = getValue('joueurId');
//alert(stringStatsHome);
//alert("strProfilJoueur");
var strProfilJoueur = getProfilDeJoueurId(joueurId);
//alert(strProfilJoueur);
var profilJoueur = eval('(' +strProfilJoueur+')');
 userId = getCookie("userId");
//alert('uId :'+userId);
var strUser = getJSONdePerso(userId)
//alert(strUser);
var usager = eval('(' +strUser+')');

forme = document.createElement('form');
		forme.setAttribute('method','get');
		forme.setAttribute('action','/admin/entreprofil.html');
		bouton = document.createElement('input');
		bouton.setAttribute('type','submit');
		bouton.setAttribute('value',"Modifier");
		entree = document.createElement('input');
		entree.setAttribute('type','hidden');
		entree.setAttribute('value',joueurId);
		entree.setAttribute('name',"joueurId");
		amettre = document.getElementById('descriptionFiche');
		amettre.appendChild(forme);
		forme.appendChild(bouton);
		forme.appendChild(entree);
		

</script>
    	 	<script type="text/javascript">
function loadXMLDoc()
{
var requete_ajax;
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  requete_ajax=new XMLHttpRequest();
//	alert("yo1");
  }
else
  {// code for IE6, IE5
  requete_ajax=new ActiveXObject("Microsoft.XMLHTTP");
  }
  requete_ajax.onreadystatechange=function(joueurId)
  	{
  	if (requete_ajax.readyState==4 && requete_ajax.status==200)
    	{
		stringStatsHome = requete_ajax.responseText;
		//alert(stringStatsHome);
		var statsJSONHome = eval('(' +stringStatsHome+')');

		liste = document.createElement('p');
		amettre = document.getElementById('descriptionFiche');
		
		texteFiche = document.createTextNode('Nom: '+profilJoueur.nom);
	
		amettre.appendChild(texteFiche);
		amettre.appendChild(document.createElement('BR'));
		texteFiche = document.createTextNode('Prenom: '+profilJoueur.prenom);
		amettre.appendChild(texteFiche);
		amettre.appendChild(document.createElement('BR'));
		texteFiche = document.createTextNode('Taille: '+profilJoueur.taille+' cm');
		amettre.appendChild(texteFiche);
		amettre.appendChild(document.createElement('BR'));
		texteFiche = document.createTextNode('Poids: '+profilJoueur.poids+' kg');
		amettre.appendChild(texteFiche);
		amettre.appendChild(document.createElement('BR'));
		texteFiche = document.createTextNode('Ville d\'origine: '+profilJoueur.villeOrigine);
		amettre.appendChild(texteFiche);
		amettre.appendChild(document.createElement('BR'));
		texteFiche = document.createTextNode('Année de naissance: '+profilJoueur.anneeNaissance);
		amettre.appendChild(texteFiche);
		amettre.appendChild(document.createElement('BR'));

		for (J=0;J<statsJSONHome.saisons.length;J=J+1)
			{
			for (K=0;K<statsJSONHome.saisons[J].equipe.length;K=K+1)
				{

				rangee = document.createElement('TR');
				amettre = document.getElementById('rangeeTitreEquipe');
				amettre.parentNode.appendChild(rangee);
	
				celluleEq = document.createElement('TD');
				texteEq = document.createTextNode(statsJSONHome.saisons[J].premierMatch);
				celluleEq.appendChild(texteEq);
				if((K+J)%2==0)
					celluleEq.setAttribute('class','lignePaire');
				else
					celluleEq.setAttribute('class','ligneImpaire');

				rangee.appendChild(celluleEq);
	
	
				celluleNom = document.createElement('TD');
				texteNom = document.createTextNode(statsJSONHome.saisons[J].dernierMatch);
				celluleNom.appendChild(texteNom);
				if((K+J)%2==0)
					celluleNom.setAttribute('class','lignePaire');
				else
					celluleNom.setAttribute('class','ligneImpaire');

				rangee.appendChild(celluleNom);

				celluleEquipe = document.createElement('TD');
				texteEquipe = document.createTextNode(statsJSONHome.saisons[J].equipe[K].equipeNom);
				celluleEquipe.appendChild(texteEquipe);
				if((K+J)%2==0)
					celluleEquipe.setAttribute('class','lignePaire');
				else
					celluleEquipe.setAttribute('class','ligneImpaire');
				rangee.appendChild(celluleEquipe);


				cellulePJ = document.createElement('TD');
				textePJ = document.createTextNode(statsJSONHome.saisons[J].equipe[K].fiche.pj);
				cellulePJ.appendChild(textePJ);
				if((K+J)%2==0)
					cellulePJ.setAttribute('class','lignePaire');
				else
					cellulePJ.setAttribute('class','ligneImpaire');
				rangee.appendChild(cellulePJ);

				celluleButs = document.createElement('TD');
				texteButs = document.createTextNode(statsJSONHome.saisons[J].equipe[K].fiche.buts);
				celluleButs.appendChild(texteButs);
				if((K+J)%2==0)
					celluleButs.setAttribute('class','lignePaire');
				else
					celluleButs.setAttribute('class','ligneImpaire');
				rangee.appendChild(celluleButs);

				cellulePasses = document.createElement('TD');
				textePasses = document.createTextNode(statsJSONHome.saisons[J].equipe[K].fiche.passes);
				cellulePasses.appendChild(textePasses);
				if((K+J)%2==0)
					cellulePasses.setAttribute('class','lignePaire');
				else
					cellulePasses.setAttribute('class','ligneImpaire');
				rangee.appendChild(cellulePasses);

				cellulePoints = document.createElement('TD');
				textePoints = document.createTextNode(parseInt(statsJSONHome.saisons[J].equipe[K].fiche.buts)+parseInt(statsJSONHome.saisons[J].equipe[K].fiche.passes));
				cellulePoints.appendChild(textePoints);
				if((K+J)%2==0)
					cellulePoints.setAttribute('class','lignePaire');
				else
					cellulePoints.setAttribute('class','ligneImpaire');
				rangee.appendChild(cellulePoints);

				}

			}
	    }
  	}
	requete_ajax.open('GET','/stats/statsJoueur2JSON.php?joueurId='+joueurId,true);
	requete_ajax.send();
}

loadXMLDoc();
 	</script>



	


</body> 
</html>
