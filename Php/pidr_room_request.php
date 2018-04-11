<?php

require_once('_const_fonc.php');

$query = "SELECT * "
  ." FROM  individu "
  ." WHERE num_bureau = " .GetSQLValueString($_POST["room_number"] , "text");

$result=mysql_query($query) or die(mysql_error()) ;

while ($row = mysql_fetch_assoc($result)) {
    echo "Nom : ".$row['nom']."<br/>";
    echo "Prenom : ".$row['prenom']."<br/>";
    echo "Tel : ".$row['tel']."<br/>";
    echo "Email : ".$row['email']."<br/>";
    echo "================================<br/>";
}

?>
