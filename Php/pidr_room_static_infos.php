<?php
header('Content-type:text/html; charset=utf-8');
require_once('_const_fonc.php');

$query = "SELECT * "
  ." FROM  individu "
  ." WHERE num_bureau = " .GetSQLValueString($_POST["room_number"] , "text");

$result=mysql_query($query) or die(mysql_error()) ;

while ($row = mysql_fetch_assoc($result)) {
  /* Informations basiques */
  echo $row['nom']." ".$row['prenom'][0]."."."<br/>";
}

?>