 <html> 
<title>liste déroulante dynamique</title> 
<head> 
<form method="post" id="myform" action="stats/classement.php">	
</head> 
<body> 

<script type="text/javascript">
function submitform()
{
    document.forms["myform"].submit();
}
function ajour(){
	var userInput = document.getElementById('userInput').value;
	document.getElementById('ligue').innerHTML = userInput;
}
</script>
<p>Votre ligue :<b id='ligue'>- Aucune -</b> </p> 
<input type='text' id='userInput' name='ligue' value='<Nom de votre ligue>' /><br />
<input type="radio" name="selection" value="matchs" /> Voir matchs
<input type="radio" name="selection" value="equipes" /> Voir classement<br />
<input type='button' onclick='submitform()' value='Valider'/><br />
</form>
<hr>

</body> 
</html> 