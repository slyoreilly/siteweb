<html><head>
	<form method="post" action="delete_joueur.php">
<title>MySQL Table Viewer</title></head><body>
<?php 
$db_host = 'localhost';
$db_user = 'syncsta1_u01';
$db_pwd = 'test';

$database = 'syncsta1_900';
$table = 'TableJoueur';

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

$fields_num = mysql_num_fields($result);

echo "<h1>Table: {$table}</h1>";
echo "<table border='1'><tr>";
// printing table headers
for($i=0; $i<$fields_num; $i++)
{
    $field = mysql_fetch_field($result);
    echo "<td>{$field->name}</td>";
}
echo "</tr>\n";
// printing table rows
$position =1;
while($row = mysql_fetch_row($result))
{
	
    echo "<tr>";

    // $row is array... foreach( .. ) puts every element
    // of $row to $cell variable
    foreach($row as $cell)
        echo "<td>$cell</td>";
	echo "<td><input type=\"checkbox\", name=\"$position";
	echo "\"></td>";
    echo "</tr>\n";
	$position++;
}
mysql_free_result($result);
?>
<input type="submit" value="Retirer">
</body>
</form>
</html>