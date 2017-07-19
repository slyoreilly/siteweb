 
<?php




// Insert query
#mysql_select_db("syncsta1_900", $con);

#if(!mysql_query("INSERT INTO TableSommaire (marqueur, passeur1, passeur2, chrono, equipe)
#VALUES ('".$marqueur."', '".$passeur1."', '".$passeur2."', '".$chrono."', '".$equipe."')"));

#mysql_close($con);


?>
 
<html> 
<title>liste d�roulante dynamique</title> 
<head> 
<script language="Javascript" type="text/javascript" > 
function choix(formulaire) 
{ 
var j; 
var i = formulaire.boite1.selectedIndex; 
if (i == 0) 
for(j = 1; j <3; j++) 
formulaire.boite2.options[j].text=""; 


else{ 
switch (i){ 
case 1 : var text = new Array( "Noir","Blanc",""); 
break; 
case 2 : var text = new Array("Toulouse","Agen","Paris"); 
break; 

case 3 : var text = new Array("Dijon","Pau","Gravelines"); 
break; 
} 

for(j = 0; j<3; j++) 
formulaire.boite2.options[j+1].text=text[j];	
} 
formulaire.boite2.selectedIndex=0; 
} 
</script> 
</head> 
<body> 
<form name="formulaire"> 
<select name="boite1" onChange="choix(this.form)"> 
<option selected>...........Choisissez une Ligue...........</option> 

<?php
$db_host="localhost";
$db_user="syncsta1_u01";
$db_pwd="test";

$database = 'syncsta1_900';
//$table = 'Ligue';

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    {
    	echo "<h1>Database: {$database}</h1>";
    	echo "<h1>Table: {$table}</h1>";
    	die("Can't select database");

}
// sending query
$result = mysql_query("SELECT * FROM {$table}");
if (!$result) {
    die("Query to show fields from table failed");
}

while($ligne_liste=mysql_fetch_array($results))
                        {
echo "<option Value=\"".$ligne_liste['Nom_Ligue']."\">".$ligne_liste['Nom_Ligue']."</option>";
			}

			echo "</SELECT>";


?>

<option></option> 

</select> 

<select name="boite2"> 
<option selected>...........Choisissez une �quipe...........</option> 
<option></option>  
<option></option> 
<option></option> 
</form> 
</select> 
</body> 
</html> 