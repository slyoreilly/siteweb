<HTML> 
<HEAD> 
	<title>Modification d'équipe</title>
<link rel="stylesheet" href="/style/general.css" type="text/css">
<script src="/scripts/fonctions.js" type="text/javascript"></script>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


<style>
</style> 
</HEAD>

<body>

<form name="myForm" action="/admin/modifierequipe.php" enctype="multipart/form-data" method="post">
<table border="0">
<tr><td bgcolor="black">
<font color="white" size="4"><b>Créer une équipe</b></font>
</td></tr>
</table>

<p>

Nom de la l'équipe: <input id="nom"  type="text" name="nom" size="12" />
<br />
Couleur du chandail: 
<select name="logo" id="logo" size="1">
<option value="blanc" selected="selected">Blanc</option>
<option value="bleu"> Bleu</option>
<option value="vert"> Vert</option>
<option value="rouge"> Rouge</option>
</select>
<br />
<input type="hidden" name="ligueId" value="<?=$_POST['ligueId']; ?>"/>
<input type="hidden" name="admin" value="<?=$_POST['id']; ?>"/>
<input type="hidden" name="code" value="<?=$_POST['code']; ?>"/>
<input type="hidden" id="equipeId" name="equipeId" value=""/>
<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
Fichier d'image: 

<input name="userfile" type="file" id="userfile">


<script type="text/javascript">
try{
//alert(<?=$_POST['code']; ?>);
if(<?=$_POST['code']; ?>==1)
{    var nom = document.getElementById('nom') ;
    nom.setAttribute("value", "") ;
   
}
if(<?=$_POST['code']; ?>==10)
{
	 var nom = document.getElementById('nom') ;
    nom.setAttribute("value", "<?=$_POST['nomEquipe']; ?>") ;
     var logo = document.getElementById('logo') ;
    logo.setAttribute("value", "<?=$_POST['logo']; ?>") ;
//   alert("<?=$_POST['equipeId']; ?>"+"  "+ "<?=$_POST['nomEquipe']; ?>");
	     var equipeId2 = document.getElementById('equipeId') ;
    equipeId2.setAttribute("value", "<?=$_POST['equipeId']; ?>") ;
	
}
}catch(err){}


</script>


<br />
<input name="upload" type="submit" id="upload" value="Enregistrer" />
<input type="reset" value="Recommencer" />

</form>
</body> 
</html>
