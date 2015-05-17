<HTML> 
<HEAD> 
	<title>Page personnelle</title>
		<link rel="stylesheet" href="style/general.css" type="text/css">
		<script src="/scripts/fonctions.js" type="text/javascript" charset="utf-8"></script>
		<script src="/scripts/affichage.js" type="text/javascript" charset="utf-8"></script>
		<script src="/scripts/afficheTableau.js" type="text/javascript" charset="utf-8"></script>
		<script src="/scripts/dhtmlgoodies_calendar.js" type="text/javascript"charset="utf-8"></script>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


<style>
</style> 
</HEAD>

<body>

		<script type="text/javascript" charset="utf-8">

			function afficheSelectionZA(choix) {
				return function() {
					videNoeud('mbody');
					if (window.m != '1') {
						genereEntete();
						genereMenu(5);
						genMenuZoneAdmin(choix);
						//			alert('22')
						m = 0;
					}

					switch(choix) {
						case 1:
							window.location.href = '/gestionligue.html?ligueId=' + ligueId;
							break;
						case 2:
							window.location.href = '/gestionjoueursligue.html?ligueId=' + ligueId;
							break;
						case 3:
							window.location.href = '/zeroconfig.html?ligueId=' + ligueId;
							//	afficheMeneurs(window.statsJSON, window.gJSON);
							break;
						case 4:
							if (getCookie("ligueId"))
								document.cookie = "ligueId" + "=" + (("/" ) ? ";path=" + "/" : "") + (("" ) ? ";domain=" + "" : "" ) + ";expires=Thu, 01-Jan-1970 00:00:01 GMT";
							window.location.href = '/statistiques.html';

							break;
						case 5:
							/*					alert('HEp!')
							 //setCookie('ligueId',null,7);
							 //					deleteCookie("ligueId","/","");
							 */
							break;
						default:
					}
				}
			}

	if (window.m != "1") {
				genereEntete();
				genereMenu(5);
				genMenuZoneAdmin(1);
				m = 0;

			}

</script>
<form name="myForm" action="/admin/modifierligue.php" enctype="multipart/form-data" method="post">
<table border="0">
<tr><td bgcolor="black">
<font color="white" size="4"><b>Créer une ligue</b></font>
</td></tr>
</table>

<p>

Nom de la ligue: <input id="nom"  type="text" name="nom" size="12" />
<br />
Horaire: <input id="horaire" type="text" name="horaire" size="20" />
<br />
<input type="hidden" name="ligueid" value="<?=$_POST['ligueId']; ?>"/>
<input type="hidden" name="admin" value="<?=$_POST['id']; ?>"/>
<input type="hidden" name="code" value="<?=$_POST['code']; ?>"/>
Lieu: <input id="lieu" type="text" name="lieu" size="20" value="<?=$_POST['lieu']; ?>"/>


<script type="text/javascript">



try{
if(<?=$_POST['code']; ?>==1)
{    var nom = document.getElementById('nom') ;
    nom.setAttribute("value", "") ;
     var horaire = document.getElementById('horaire') ;
    horaire.setAttribute("value", "") ;
     var lieu = document.getElementById('lieu') ;
    lieu.setAttribute("value", "") ;
   
}
if(<?=$_POST['code']; ?>==10)
{
	 var nom = document.getElementById('nom') ;
    nom.setAttribute("value", "<?=$_POST['nom']; ?>") ;
     var horaire = document.getElementById('horaire') ;
    horaire.setAttribute("value", "<?=$_POST['horaire']; ?>") ;
     var lieu = document.getElementById('lieu') ;
    lieu.setAttribute("value", "<?=$_POST['lieu']; ?>") ;
   
	
}
}catch(err){}


		

</script>



<br />
<table >
<tr>
<td width="246">
<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
<input name="userfile" type="file" id="userfile">
</td>
<td width="80"><input name="upload" type="submit" id="upload" value="T�l�verser"></td>
</tr>
</table>
<br />
<input type="submit" value="Enregistrer" />
<input type="reset" value="Recommencer" />

</form>
</body> 
</html>
